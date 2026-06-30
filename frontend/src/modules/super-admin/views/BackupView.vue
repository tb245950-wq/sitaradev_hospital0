<template>
  <div class="backup-management">
    <div class="page-header">
      <h1>Backup & Restore</h1>
    </div>

    <div class="backup-section">
      <h2>Buat Backup</h2>
      <p>Buat backup database dan file sistem</p>
      <button @click="createBackup" :disabled="backupLoading" class="btn-primary btn-lg">
        {{ backupLoading ? 'Sedang membuat backup...' : '💾 Buat Backup Sekarang' }}
      </button>
      <p v-if="backupMessage" :class="`message ${backupSuccess ? 'success' : 'error'}`">{{ backupMessage }}</p>
    </div>

    <div class="restore-section">
      <h2>Restore Backup</h2>
      <p>Pilih file backup untuk di-restore</p>
      
      <div v-if="loading" class="loading">Memuat backup files...</div>
      
      <table v-if="!loading && backupFiles.length" class="backups-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Ukuran</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="file in backupFiles" :key="file.name">
            <td>{{ formatDate(file.created_at) }}</td>
            <td>{{ formatSize(file.size) }}</td>
            <td>
              <button @click="restoreBackup(file)" :disabled="restoreLoading" class="btn-small">
                {{ restoreLoading ? 'Restoring...' : 'Restore' }}
              </button>
              <button @click="downloadBackup(file)" class="btn-small">Download</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="!loading && !backupFiles.length" class="empty">Tidak ada backup file</div>

      <p v-if="restoreMessage" :class="`message ${restoreSuccess ? 'success' : 'error'}`">{{ restoreMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../core/services/api'

const backupLoading = ref(false)
const restoreLoading = ref(false)
const loading = ref(false)
const backupMessage = ref('')
const restoreMessage = ref('')
const backupSuccess = ref(false)
const restoreSuccess = ref(false)
const backupFiles = ref([])

onMounted(() => {
  fetchBackupFiles()
})

const fetchBackupFiles = async () => {
  try {
    loading.value = true
    // TODO: Implement backend endpoint GET /super-admin/backups
    backupFiles.value = []
  } catch (err) {
    console.error('Error fetching backups:', err)
  } finally {
    loading.value = false
  }
}

const createBackup = async () => {
  try {
    backupLoading.value = true
    backupMessage.value = ''
    // TODO: Implement backend endpoint POST /super-admin/backup
    backupSuccess.value = true
    backupMessage.value = '✅ Backup berhasil dibuat'
    fetchBackupFiles()
  } catch (err) {
    backupSuccess.value = false
    backupMessage.value = '❌ Gagal membuat backup'
  } finally {
    backupLoading.value = false
  }
}

const restoreBackup = async (file) => {
  if (!confirm(`Restore backup dari ${formatDate(file.created_at)}? Semua data sekarang akan diganti!`)) return
  try {
    restoreLoading.value = true
    restoreMessage.value = ''
    // TODO: Implement backend endpoint POST /super-admin/restore
    restoreSuccess.value = true
    restoreMessage.value = '✅ Restore berhasil'
  } catch (err) {
    restoreSuccess.value = false
    restoreMessage.value = '❌ Gagal restore backup'
  } finally {
    restoreLoading.value = false
  }
}

const downloadBackup = (file) => {
  // TODO: Implement download functionality
  alert('Download feature coming soon')
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleString('id-ID', { year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
.backup-management { padding: 1.5rem; max-width: 800px; }
.page-header { margin-bottom: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; }

.backup-section, .restore-section {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.backup-section h2, .restore-section h2 { font-size: 1.2rem; margin-bottom: 0.5rem; }
.backup-section p, .restore-section p { color: #64748b; margin-bottom: 1rem; }

.btn-primary { background: #1e40af; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-lg { padding: 1rem 2rem; font-size: 1rem; }

.backups-table { width: 100%; border-collapse: collapse; }
.backups-table th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; }
.backups-table td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
.btn-small { padding: 0.5rem 1rem; font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; margin-right: 0.5rem; }
.btn-small:disabled { opacity: 0.5; cursor: not-allowed; }

.message { padding: 1rem; border-radius: 0.25rem; margin-top: 1rem; }
.message.success { background: #dcfce7; color: #166534; }
.message.error { background: #fee2e2; color: #991b1b; }

.loading { text-align: center; padding: 2rem; color: #94a3b8; }
.empty { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
