/**
 * Service untuk generate Laporan Monitoring PDF
 * 
 * Digunakan di MonitoringView.vue dan komponen monitoring lainnya
 */

import { api } from '@/core/services/api'

export const monitoringReportService = {
  /**
   * Download Laporan Monitoring PDF
   * 
   * @param {number} idPasien - ID Pasien
   * @param {number|null} idTerapi - ID Terapi (optional, ambil aktif jika null)
   * @returns {Promise<void>}
   */
  async downloadMonitoringReport(idPasien, idTerapi = null) {
    try {
      const url = idTerapi 
        ? `/api/monitorings/${idPasien}/${idTerapi}/report-pdf`
        : `/api/monitorings/${idPasien}/report-pdf`
      
      // Get PDF as blob
      const response = await api.get(url, {
        responseType: 'blob',
        headers: {
          'Accept': 'application/pdf'
        }
      })
      
      // Create blob and download
      const blob = new Blob([response.data], { type: 'application/pdf' })
      const link = document.createElement('a')
      link.href = window.URL.createObjectURL(blob)
      
      // Generate filename dengan timestamp
      const now = new Date()
      const timestamp = now.toISOString().split('T')[0]
      link.download = `Laporan_Monitoring_${timestamp}.pdf`
      
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      // Cleanup
      window.URL.revokeObjectURL(link.href)
      
      return {
        success: true,
        message: 'Laporan berhasil diunduh'
      }
    } catch (error) {
      console.error('Error downloading monitoring report:', error)
      
      const errorMsg = error.response?.data?.message 
        || 'Gagal mengunduh laporan'
      
      throw {
        success: false,
        message: errorMsg
      }
    }
  },

  /**
   * Download laporan dengan loading state
   * Cocok untuk digunakan dengan alert/notification
   * 
   * @param {number} idPasien - ID Pasien
   * @param {number|null} idTerapi - ID Terapi
   * @param {function} onLoading - Callback ketika loading
   * @returns {Promise<object>}
   */
  async downloadWithNotification(idPasien, idTerapi = null, onLoading = null) {
    try {
      onLoading?.(true)
      
      await this.downloadMonitoringReport(idPasien, idTerapi)
      
      onLoading?.(false)
      
      return {
        success: true,
        message: '✅ Laporan berhasil diunduh'
      }
    } catch (error) {
      onLoading?.(false)
      
      return {
        success: false,
        message: `❌ ${error.message || 'Gagal mengunduh laporan'}`
      }
    }
  }
}

export default monitoringReportService
