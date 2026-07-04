<template>
  <div class="poli-management">
    <div class="page-header">
      <h1>Manajemen Poli / Layanan</h1>
      <button @click="showCreateModal = true" class="btn-primary">+ Tambah Poli</button>
    </div>

    <div v-if="loading" class="loading">Memuat...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <table v-if="!loading && polis.length" class="polis-table">
      <thead>
        <tr>
          <th>Nama Poli</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Dibuat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="poli in polis" :key="poli.id">
          <td><strong>{{ poli.nama }}</strong></td>
          <td>{{ poli.deskripsi || '-' }}</td>
          <td><span :class="`status-${poli.status}`">{{ poli.status }}</span></td>
          <td>{{ formatDate(poli.created_at) }}</td>
          <td class="actions">
            <button @click="editPoli(poli)" class="btn-small">Edit</button>
            <button @click="deletePoliConfirm(poli)" class="btn-small danger">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>

    <div v-if="!loading && !polis.length" class="empty">Tidak ada poli</div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="modal-overlay" @click="showCreateModal = false">
      <div class="modal" @click.stop>
        <h2>{{ editingPoli ? 'Edit Poli' : 'Tambah Poli' }}</h2>
        
        <input v-model="form.kode" placeholder="Kode Poli (cth: umum)" class="input">
        <input v-model="form.nama" placeholder="Nama Poli" class="input">
        <textarea v-model="form.deskripsi" placeholder="Deskripsi" class="input" rows="3"></textarea>
        <select v-model="form.status" class="input">
          <option value="">Pilih Status</option>
          <option value="aktif">Aktif</option>
          <option value="nonaktif">Nonaktif</option>
        </select>

        <div class="modal-actions">
          <button @click="savePoli" class="btn-primary">Simpan</button>
          <button @click="showCreateModal = false" class="btn-secondary">Batal</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { superAdminService } from '../services/superAdminService'

const polis = ref([])
const loading = ref(false)
const error = ref(null)
const showCreateModal = ref(false)
const editingPoli = ref(null)
const form = ref({
  kode: '',
  nama: '',
  deskripsi: '',
  status: 'aktif'
})

onMounted(() => {
  fetchPolis()
})

const fetchPolis = async () => {
  try {
    loading.value = true
    const res = await superAdminService.getPolis()
    polis.value = res.data?.data ?? res.data ?? []
  } catch (err) {
    error.value = 'Gagal memuat poli'
  } finally {
    loading.value = false
  }
}

const editPoli = (poli) => {
  editingPoli.value = poli
  form.value = { ...poli }
  showCreateModal.value = true
}

const savePoli = async () => {
  try {
    if (editingPoli.value) {
      // Update via PoliController (existing endpoint)
      await superAdminService.updatePoli(editingPoli.value.id, form.value)
    } else {
      // Create via PoliController (existing endpoint)
      await superAdminService.createPoli(form.value)
    }
    showCreateModal.value = false
    fetchPolis()
  } catch (err) {
    error.value = 'Gagal menyimpan poli'
  }
}

const deletePoliConfirm = async (poli) => {
  if (!confirm(`Hapus poli ${poli.nama}?`)) return
  try {
    await superAdminService.deletePoli(poli.id)
    fetchPolis()
  } catch {
    alert('Gagal hapus poli')
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const date = new Date(dateStr)
  return date.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: '2-digit' })
}
</script>

<style scoped>
.poli-management { padding: 1.5rem; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.loading { text-align: center; padding: 2rem; }
.error { background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
.polis-table { width: 100%; border-collapse: collapse; background: white; border-radius: 0.5rem; overflow: hidden; }
.polis-table th { background: #f8fafc; padding: 1rem; text-align: left; font-weight: 600; }
.polis-table td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
.status-active { color: #059669; }
.status-inactive { color: #dc2626; }
.actions { display: flex; gap: 0.5rem; }
.btn-small { padding: 0.5rem 1rem; font-size: 0.85rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; }
.btn-small.danger { background: #fee2e2; color: #dc2626; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; }
.modal { background: white; padding: 2rem; border-radius: 0.5rem; min-width: 400px; }
.input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; font-family: inherit; }
.modal-actions { display: flex; gap: 0.5rem; }
.btn-primary { background: #1e40af; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.btn-secondary { background: #e2e8f0; padding: 0.75rem 1.5rem; border: none; border-radius: 0.25rem; cursor: pointer; }
.empty { text-align: center; padding: 2rem; color: #94a3b8; }
</style>
