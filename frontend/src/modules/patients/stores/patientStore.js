import { defineStore } from 'pinia'
import { ref } from 'vue'
import { patientService } from '../services/patientService'

export const usePatientStore = defineStore('patients', () => {
  const patients = ref([])
  const currentPatient = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0
  })

  // Filters
  const filters = ref({
    search: ''
  })

  async function fetchPatients(page = 1) {
    loading.value = true
    error.value = null
    try {
      const params = {
        ...filters.value,
        page
      }
      const response = await patientService.getPatients(params)
      patients.value = response.data.data
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil data pasien'
    } finally {
      loading.value = false
    }
  }

  async function fetchPatientById(id) {
    loading.value = true
    error.value = null
    try {
      const response = await patientService.getPatient(id)
      currentPatient.value = response.data.data
      return { success: true, data: response.data.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal mengambil detail pasien'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function createPatient(data) {
    loading.value = true
    error.value = null
    try {
      const response = await patientService.createPatient(data)
      return { success: true, data: response.data.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal menambah pasien'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function updatePatient(id, data) {
    loading.value = true
    error.value = null
    try {
      const response = await patientService.updatePatient(id, data)
      return { success: true, data: response.data.data }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal update pasien'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function deletePatient(id) {
    loading.value = true
    error.value = null
    try {
      await patientService.deletePatient(id)
      await fetchPatients(pagination.value.current_page)
      return { success: true }
    } catch (err) {
      error.value = err.response?.data?.message || 'Gagal hapus pasien'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  function setFilters(newFilters) {
    filters.value = { ...filters.value, ...newFilters }
  }

  return {
    patients,
    currentPatient,
    loading,
    error,
    pagination,
    filters,
    fetchPatients,
    fetchPatientById,
    createPatient,
    updatePatient,
    deletePatient,
    setFilters
  }
})
