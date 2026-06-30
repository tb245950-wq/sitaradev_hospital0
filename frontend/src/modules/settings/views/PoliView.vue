<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <button @click="router.push('/settings')" class="btn-back">← Kembali ke Pengaturan</button>
        <h1 class="page-title">Manajemen Poli / Klinik</h1>
        <p class="page-subtitle">Kelola daftar poli yang tersedia untuk pendaftaran pasien</p>
      </div>
      <button @click="openForm()" class="btn-primary">+ Tambah Poli</button>
    </div>

    <!-- Tabel Poli -->
    <div class="card">
      <div v-if="loading" class="loading">Memuat data...</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Kode</th>
            <th>Nama Poli</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="polis.length === 0">
            <td colspan="5" class="empty-cell">Belum ada data poli</td>
          </tr>
          <tr v-for="p in polis" :key="p.id">
            <td><code class="kode-badge">{{ p.kode }}</code></td>
            <td class="fw-600">{{ p.nama }}</td>
            <td class="text-muted">{{ p.deskripsi || '-' }}</td>
            <td>
              <span :class="['status-badge', p.status === 'aktif' ? 'aktif' : 'nonaktif']">
                {{ p.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td>
              <div class="action-btns">
                <button @click="openForm(p)" class="btn-edit">Edit</button>
                <button @click="handleDelete(p)" class="btn-delete">Hapus</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeForm">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ isEdit ? 'Edit Poli' : 'Tambah Poli' }}</h2>
          <button @click="closeForm" class="modal-close">&times;</button>
        </div>
        <form @submit.prevent="handleSubmit" class="modal-body">
          <div class="form-group">
            <label>Kode Poli <span class="required">*</span></label>
            <input v-model="form.kode" type="text" class="form-input" placeholder="cth: poli_umum" :disabled="isEdit" required />
            <small class="help-text">Hanya huruf, angka, dan underscore. Tidak bisa diubah setelah dibuat.</small>
          </div>
          <div class="form-group">
            <label>Nama Poli <span class="required">*</span></label>
            <input v-model="form.nama" type="text" class="form-input" placeholder="cth: Poli Umum" required />
          </div>
          <div class="form-group">
            <label>Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="2" class="form-input" placeholder="Keterangan singkat poli ini..."></textarea>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select v-model="form.status" class="form-input">
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>
          <div v-if="formError" class="alert-error">{{ formError }}</div>
          <div class="modal-footer">
            <button type="button" @click="closeForm" class="btn-secondary">Batal</button>
            <button type="submit" :disabled="submitting" class="btn-primary">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { poliService } from '../services/poliService'
import { useNotificationStore } from '../../../shared/stores/notificationStore'

const router   = useRouter()
const notify   = useNotificationStore()

const polis     = ref([])
const loading   = ref(false)
const showModal = ref(false)
const isEdit    = ref(false)
const submitting= ref(false)
const formError = ref('')
const editId    = ref(null)

const form = ref({ kode: '', nama: '', deskripsi: '', status: 'aktif' })

const fetchPolis = async () => {
  loading.value = true
  try {
    const res = await poliService.getAll()
    polis.value = res.data || []
  } catch {
    notify.error('Gagal memuat data poli')
  } finally {
    loading.value = false
  }
}

const openForm = (poli = null) => {
  formError.value = ''
  if (poli) {
    isEdit.value = true
    editId.value = poli.id
    form.value = { kode: poli.kode, nama: poli.nama, deskripsi: poli.deskripsi || '', status: poli.status }
  } else {
    isEdit.value = false
    editId.value = null
    form.value = { kode: '', nama: '', deskripsi: '', status: 'aktif' }
  }
  showModal.value = true
}

const closeForm = () => { showModal.value = false }

const handleSubmit = async () => {
  submitting.value = true
  formError.value = ''
  try {
    if (isEdit.value) {
      await poliService.update(editId.value, { nama: form.value.nama, deskripsi: form.value.deskripsi, status: form.value.status })
      notify.success('Poli berhasil diperbarui', 'Berhasil')
    } else {
      await poliService.create(form.value)
      notify.success('Poli berhasil ditambahkan', 'Berhasil')
    }
    closeForm()
    fetchPolis()
  } catch (err) {
    formError.value = err.response?.data?.message || 'Terjadi kesalahan'
  } finally {
    submitting.value = false
  }
}

const handleDelete = async (poli) => {
  if (!confirm(`Hapus poli "${poli.nama}"? Tindakan ini tidak dapat dibatalkan.`)) return
  try {
    await poliService.remove(poli.id)
    notify.success('Poli berhasil dihapus')
    fetchPolis()
  } catch (err) {
    notify.error(err.response?.data?.message || 'Gagal menghapus poli')
  }
}

onMounted(fetchPolis)
</script>

<style scoped>
.page-container { padding: 2rem; max-width: 1000px; margin: 0 auto; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 2rem; }
.btn-back { background: none; border: none; color: #3b82f6; font-weight: 600; cursor: pointer; padding: 0; margin-bottom: 0.5rem; font-size: 0.875rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0.25rem 0 0; }
.page-subtitle { color: #64748b; font-size: 0.875rem; margin: 0; }

.card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.loading { padding: 2rem; text-align: center; color: #64748b; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.875rem 1rem; background: #f8fafc; text-align: left; font-size: 0.8125rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
.data-table td { padding: 0.875rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.9375rem; vertical-align: middle; }
.empty-cell { text-align: center; color: #94a3b8; padding: 3rem; }

.kode-badge { background: #f1f5f9; color: #475569; padding: 0.2rem 0.5rem; border-radius: 0.25rem; font-size: 0.8125rem; }
.fw-600 { font-weight: 600; color: #1e293b; }
.text-muted { color: #64748b; font-size: 0.875rem; }

.status-badge { padding: 0.2rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; }
.status-badge.aktif { background: #dcfce7; color: #166534; }
.status-badge.nonaktif { background: #f1f5f9; color: #64748b; }

.action-btns { display: flex; gap: 0.5rem; }
.btn-edit { padding: 0.375rem 0.75rem; background: #eff6ff; color: #1d4ed8; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; }
.btn-edit:hover { background: #dbeafe; }
.btn-delete { padding: 0.375rem 0.75rem; background: #fef2f2; color: #dc2626; border: none; border-radius: 0.375rem; cursor: pointer; font-size: 0.8125rem; font-weight: 600; }
.btn-delete:hover { background: #fee2e2; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; border-radius: 1rem; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; }
.modal-header h2 { font-size: 1.125rem; font-weight: 700; color: #1e293b; margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; line-height: 1; }
.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.125rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.5rem; }

.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-group label { font-size: 0.875rem; font-weight: 600; color: #374151; }
.required { color: #ef4444; }
.form-input { padding: 0.625rem 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.9375rem; outline: none; }
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
.help-text { font-size: 0.75rem; color: #94a3b8; }

.alert-error { background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.75rem 1rem; font-size: 0.875rem; color: #dc2626; }

.btn-primary { padding: 0.625rem 1.25rem; background: #1e40af; color: white; border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { padding: 0.625rem 1.25rem; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 0.5rem; cursor: pointer; font-weight: 600; font-size: 0.875rem; }
</style>
