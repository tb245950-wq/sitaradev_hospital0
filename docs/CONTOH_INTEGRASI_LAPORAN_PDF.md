/**
 * CONTOH INTEGRASI MonitoringReportButton ke MonitoringView.vue
 * 
 * File ini menunjukkan cara mengintegrasikan komponen laporan PDF
 * ke dalam view monitoring yang sudah ada
 */

// ============================================
// 1. IMPORT di MonitoringView.vue
// ============================================

import MonitoringReportButton from '../components/MonitoringReportButton.vue'

export default {
  components: {
    MonitoringReportButton // <-- Add ke components
  },
  // ...rest of component
}

// ============================================
// 2. TEMPLATE - Tambahkan Button di Table Actions
// ============================================

/**
 * Di section table tbody, ubah action buttons menjadi:
 * 
 * BEFORE:
 *   <button @click="openDetail(m)" class="btn-action detail">
 *     👁️
 *   </button>
 * 
 * AFTER:
 *   <button @click="openDetail(m)" class="btn-action detail">
 *     👁️
 *   </button>
 *   <MonitoringReportButton 
 *     :id-pasien="m.id_pasien" 
 *     :id-terapi="m.therapy?.id_terapi"
 *     @success="onReportSuccess"
 *     @error="onReportError"
 *   />
 */

// ============================================
// 3. METHODS - Add Handlers
// ============================================

methods: {
  // ... existing methods ...

  /**
   * Handle successful report generation
   */
  onReportSuccess(data) {
    console.log('✅ Laporan berhasil diunduh:', data)
    
    // Optional: Show success notification
    this.showNotification({
      type: 'success',
      message: `Laporan untuk ${data.idPasien} berhasil diunduh`,
      duration: 3000
    })
  },

  /**
   * Handle report generation error
   */
  onReportError(error) {
    console.error('❌ Error generating report:', error)
    
    // Optional: Show error notification
    this.showNotification({
      type: 'error',
      message: error.message || 'Gagal mengunduh laporan',
      duration: 5000
    })
  }
}

// ============================================
// 4. TEMPLATE FULL EXAMPLE
// ============================================

/**
 * <td style="text-align: right;">
 *   <div class="action-group">
 *     <button 
 *       @click="openDetail(m)" 
 *       class="btn-action detail"
 *       title="Lihat Detail"
 *     >
 *       👁️
 *     </button>
 *     
 *     <MonitoringReportButton 
 *       :id-pasien="m.id_pasien" 
 *       :id-terapi="m.therapy?.id_terapi"
 *       @success="onReportSuccess"
 *       @error="onReportError"
 *     />
 *     
 *     <button 
 *       v-if="canDelete(m)"
 *       @click="deleteMonitoring(m.id)" 
 *       class="btn-action delete"
 *       title="Hapus"
 *     >
 *       🗑️
 *     </button>
 *   </div>
 * </td>
 */

// ============================================
// 5. ALTERNATIF: BUTTON DI DETAIL MODAL
// ============================================

/**
 * Jika ingin button laporan di modal detail:
 * 
 * <div v-if="selectedMonitoring" class="modal-footer">
 *   <MonitoringReportButton
 *     :id-pasien="selectedMonitoring.id_pasien"
 *     :id-terapi="selectedMonitoring.therapy?.id_terapi"
 *     @success="onReportSuccess"
 *     @error="onReportError"
 *   />
 *   <button @click="closeDetail" class="btn-secondary">Tutup</button>
 * </div>
 */

// ============================================
// 6. ALTERNATIF: MANUAL TRIGGER
// ============================================

/**
 * Jika ingin trigger manual via method:
 */

import monitoringReportService from '../services/monitoringReportService'

methods: {
  async generateReportManually(idPasien, idTerapi) {
    try {
      this.isLoadingReport = true
      await monitoringReportService.downloadMonitoringReport(
        idPasien, 
        idTerapi
      )
      this.showNotification({
        type: 'success',
        message: 'Laporan berhasil diunduh'
      })
    } catch (error) {
      this.showNotification({
        type: 'error',
        message: error.message
      })
    } finally {
      this.isLoadingReport = false
    }
  }
}

// ============================================
// 7. STYLING CSS (tambah ke MonitoringView.vue)
// ============================================

/**
 * <style scoped>
 * .action-group {
 *   display: flex;
 *   gap: 8px;
 *   align-items: center;
 *   justify-content: flex-end;
 * }
 * 
 * .btn-action {
 *   padding: 6px 10px;
 *   background: white;
 *   border: 1px solid #ddd;
 *   border-radius: 4px;
 *   cursor: pointer;
 *   font-size: 14px;
 *   transition: all 0.2s;
 * }
 * 
 * .btn-action:hover {
 *   background-color: #f0f0f0;
 * }
 * 
 * .btn-action.detail {
 *   border-color: #3498db;
 *   color: #3498db;
 * }
 * 
 * .btn-action.delete {
 *   border-color: #e74c3c;
 *   color: #e74c3c;
 * }
 * </style>
 */

// ============================================
// 8. KONDISIONAL: SHOW BUTTON BASED ON PERMISSION
// ============================================

/**
 * <MonitoringReportButton 
 *   v-if="canViewReports"
 *   :id-pasien="m.id_pasien" 
 *   :id-terapi="m.therapy?.id_terapi"
 *   :disabled="!hasReportData(m)"
 *   @success="onReportSuccess"
 *   @error="onReportError"
 * />
 */

methods: {
  get canViewReports() {
    // Cek role user
    return ['admin', 'dokter', 'terapis'].includes(this.authStore.user?.role)
  },

  hasReportData(monitoring) {
    // Check apakah monitoring memiliki data lengkap untuk laporan
    return !!(
      monitoring.id_pasien &&
      monitoring.progress_score &&
      monitoring.catatan_perkembangan
    )
  }
}

// ============================================
// 9. TESTING DATA DUMMY
// ============================================

/**
 * Untuk testing, gunakan data dummy:
 * 
 * php artisan db:seed --class=HospitalDummySeeder1000
 * 
 * Kemudian buka monitoring dan coba download laporan untuk pasien ID 1-100
 * 
 * Monitoring harus memiliki:
 * - id_pasien ✓ (sudah ada)
 * - kehadiran = 'hadir' ✓ (sudah ada)
 * - progress_score ✓ (sudah ada)
 * - catatan_perkembangan ✓ (sudah ada)
 * - rekomendasi ✓ (sudah ada)
 */

// ============================================
// 10. DEBUGGING
// ============================================

/**
 * Jika ada masalah, buka console browser:
 * 
 * 1. Check network tab untuk melihat request:
 *    GET /api/monitorings/{id}/report-pdf
 * 
 * 2. Check response status:
 *    200 OK = Berhasil download
 *    403 = Akses ditolak (check role)
 *    404 = Pasien tidak ditemukan
 *    400 = Data tidak lengkap
 * 
 * 3. Check backend logs:
 *    tail -f storage/logs/laravel.log
 */
