<template>
  <div class="backup-management">
    <div class="page-header">
      <h1>Backup & Restore</h1>
    </div>

    <div class="backup-section">
      <h2>Buat Backup</h2>
      <p>Buat backup database dan file sistem</p>
      <div class="button-group">
        <button @click="createBackup" :disabled="backupLoading" class="btn-primary btn-lg">
          {{ backupLoading ? 'Sedang membuat backup...' : '💾 Buat Backup Sekarang' }}
        </button>
        <button @click="exportToCSV" :disabled="exportLoading" class="btn-secondary btn-lg">
          {{ exportLoading ? 'Sedang export...' : '📊 Export ke CSV (ZIP)' }}
        </button>
      </div>
      <p v-if="backupMessage" :class="`message ${backupSuccess ? 'success' : 'error'}`">{{ backupMessage }}</p>
      <p v-if="exportMessage" :class="`message ${exportSuccess ? 'success' : 'error'}`">{{ exportMessage }}</p>
    </div>

    <div class="restore-section">
      <h2>Restore Backup</h2>
      <p>Pilih file backup untuk di-restore atau download</p>
      
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
              <button @click="downloadBackup(file)" :disabled="downloadLoading" class="btn-small btn-download">
                ⬇️ Download
              </button>
              <button @click="restoreBackup(file)" :disabled="restoreLoading" class="btn-small btn-restore">
                {{ restoreLoading ? 'Restoring...' : '🔄 Restore' }}
              </button>
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
import { superAdminService } from '../services/superAdminService'

const backupLoading = ref(false)
const exportLoading = ref(false)
const restoreLoading = ref(false)
const downloadLoading = ref(false)
const loading = ref(false)
const backupMessage = ref('')
const exportMessage = ref('')
const restoreMessage = ref('')
const backupSuccess = ref(false)
const exportSuccess = ref(false)
const restoreSuccess = ref(false)
const backupFiles = ref([])

onMounted(() => {
  fetchBackupFiles()
})

const fetchBackupFiles = async () => {
  try {
    loading.value = true
    const res = await superAdminService.getBackups()
    backupFiles.value = res.data?.data ?? []
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
    const res = await superAdminService.createBackup()
    backupSuccess.value = true
    backupMessage.value = '✅ Backup berhasil dibuat: ' + (res.data?.data?.name ?? '')
    fetchBackupFiles()
  } catch (err) {
    backupSuccess.value = false
    backupMessage.value = '❌ Gagal membuat backup: ' + (err.response?.data?.message ?? err.message)
  } finally {
    backupLoading.value = false
  }
}

const exportToCSV = async () => {
  try {
    exportLoading.value = true
    exportMessage.value = '⏳ Sedang export data...'
    const token = localStorage.getItem('token')
    const response = await fetch(`/api/super-admin/export/csv?token=${token}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    
    if (!response.ok) {
      throw new Error('Export gagal')
    }
    
    // Download file
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `export_${new Date().toISOString().slice(0, 10)}.zip`
    document.body.appendChild(link)
    link.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
    
    exportSuccess.value = true
    exportMessage.value = '✅ Export CSV berhasil diunduh'
  } catch (err) {
    exportSuccess.value = false
    exportMessage.value = '❌ Export gagal: ' + err.message
  } finally {
    exportLoading.value = false
  }
}

const downloadBackup = async (file) => {
  try {
    downloadLoading.value = true
    const token = localStorage.getItem('token')
    const response = await fetch(`/api/super-admin/backups/${file.name}/download?token=${token}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    
    if (!response.ok) {
      throw new Error('Download gagal')
    }
    
    // Download file
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = file.name
    document.body.appendChild(link)
    link.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(link)
  } catch (err) {
    alert('❌ Download gagal: ' + err.message)
  } finally {
    downloadLoading.value = false
  }
}

const restoreBackup = async (file) => {
  if (!confirm(`Restore backup dari ${formatDate(file.created_at)}? Semua data sekarang akan diganti!`)) return
  try {
    restoreLoading.value = true
    restoreMessage.value = ''
    // Restore membutuhkan akses server langsung — tampilkan instruksi manual
    restoreSuccess.value = false
    restoreMessage.value = 'ℹ️ Untuk restore, jalankan secara manual di server:\npg_dump/mysql < ' + file.name
  } catch (err) {
    restoreSuccess.value = false
    restoreMessage.value = '❌ Gagal restore backup'
  } finally {
    restoreLoading.value = false
  }
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

.button-group {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.btn-primary { background: #1e40af; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary { background: #10b981; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.btn-secondary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-lg { padding: 1rem 2rem; font-size: 1rem; }

.backups-table { width: 100%; border-collapse: collapse; }
.backups-table th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; }
.backups-table td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
.btn-small { padding: 0.5rem 1rem; font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; margin-right: 0.5rem; margin-bottom: 0.25rem; cursor: pointer; background: white; }
.btn-small:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-download { background: #3b82f6; color: white; border-color: #3b82f6; }
.btn-restore { background: #f59e0b; color: white; border-color: #f59e0b; }

.message { padding: 1rem; border-radius: 0.25rem; margin-top: 1rem; white-space: pre-wrap; }
.message.success { background: #dcfce7; color: #166534; }
.message.error { background: #fee2e2; color: #991b1b; }

.loading { text-align: center; padding: 2rem; color: #94a3b8; }
.empty { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
