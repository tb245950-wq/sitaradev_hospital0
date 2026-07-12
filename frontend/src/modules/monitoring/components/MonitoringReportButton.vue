<!-- 
  MonitoringReportButton.vue
  
  Komponen button untuk download laporan monitoring PDF
  Bisa diintegrasikan ke MonitoringView.vue atau detail modal
-->

<template>
  <div class="report-button-group">
    <button 
      v-if="canGenerateReport"
      @click="handleDownloadReport"
      :disabled="isLoading"
      :class="['btn-report', { 'is-loading': isLoading }]"
      :title="tooltipText"
    >
      <span v-if="!isLoading" class="icon">📄</span>
      <span v-else class="spinner">⌛</span>
      {{ buttonText }}
    </button>
    
    <div v-if="errorMessage" class="error-message">
      ⚠️ {{ errorMessage }}
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import monitoringReportService from '../services/monitoringReportService'
import { useAlert } from '@/shared/composables/useAlert'

export default {
  name: 'MonitoringReportButton',
  props: {
    idPasien: {
      type: [Number, String],
      required: true
    },
    idTerapi: {
      type: [Number, String],
      default: null
    },
    // Untuk disable button jika diperlukan
    disabled: {
      type: Boolean,
      default: false
    },
    // Callback ketika sukses
    onSuccess: {
      type: Function,
      default: null
    },
    // Callback ketika error
    onError: {
      type: Function,
      default: null
    }
  },
  setup(props, { emit }) {
    const isLoading = ref(false)
    const errorMessage = ref('')
    const { showAlert } = useAlert()

    const canGenerateReport = computed(() => {
      return props.idPasien && !props.disabled
    })

    const buttonText = computed(() => {
      return isLoading.value ? 'Mengunduh...' : 'Download Laporan'
    })

    const tooltipText = computed(() => {
      if (props.disabled) return 'Laporan tidak dapat diunduh'
      return 'Unduh laporan pemantauan tumbuh kembang anak'
    })

    const handleDownloadReport = async () => {
      if (isLoading.value) return

      errorMessage.value = ''
      isLoading.value = true

      try {
        const result = await monitoringReportService.downloadWithNotification(
          props.idPasien,
          props.idTerapi || null,
          (loading) => {
            isLoading.value = loading
          }
        )

        if (result.success) {
          showAlert({
            type: 'success',
            message: result.message,
            duration: 3000
          })
          
          props.onSuccess?.({
            idPasien: props.idPasien,
            idTerapi: props.idTerapi
          })
          
          emit('success', { idPasien: props.idPasien, idTerapi: props.idTerapi })
        } else {
          errorMessage.value = result.message
          
          showAlert({
            type: 'error',
            message: result.message,
            duration: 5000
          })
          
          props.onError?.(result)
          emit('error', result)
        }
      } catch (error) {
        const message = error.message || 'Gagal mengunduh laporan'
        errorMessage.value = message
        
        showAlert({
          type: 'error',
          message: `❌ ${message}`,
          duration: 5000
        })
        
        props.onError?.(error)
        emit('error', error)
      } finally {
        isLoading.value = false
      }
    }

    return {
      isLoading,
      canGenerateReport,
      buttonText,
      tooltipText,
      errorMessage,
      handleDownloadReport
    }
  }
}
</script>

<style scoped>
.report-button-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  align-items: flex-start;
}

.btn-report {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background-color: #3498DB;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-report:hover:not(:disabled) {
  background-color: #2980B9;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-report:active:not(:disabled) {
  transform: translateY(0);
}

.btn-report:disabled {
  background-color: #95A5A6;
  cursor: not-allowed;
  opacity: 0.7;
}

.btn-report.is-loading {
  opacity: 0.8;
}

.btn-report .icon {
  font-size: 16px;
}

.btn-report .spinner {
  display: inline-block;
  animation: spin 1s linear infinite;
  font-size: 14px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  font-size: 12px;
  color: #E74C3C;
  padding: 6px 10px;
  background-color: #FADBD8;
  border-radius: 3px;
  border-left: 3px solid #E74C3C;
}
</style>
