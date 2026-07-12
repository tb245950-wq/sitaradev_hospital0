<template>
  <div class="page-container">
    <div class="page-header no-print">
      <button @click="goToDashboard" class="btn-back">
        <span>←</span>
        <span>Kembali ke Dashboard</span>
      </button>
      <h1 class="page-title">Laporan Medis</h1>
      <p class="page-subtitle">Analisis dan rekapitulasi data pasien</p>
    </div>

    <!-- Form Pilih Laporan -->
    <div class="reports-grid no-print">
      <div class="report-selection-card">
        <h3>Pilih Jenis Laporan</h3>
        <div class="report-types">
          <div
            v-for="type in reportTypes"
            :key="type.id"
            :class="['report-type-item', { active: selectedType === type.id }]"
            @click="selectedType = type.id"
          >
            <span class="report-icon" v-html="type.icon"></span>
            <div class="report-info">
              <strong>{{ type.label }}</strong>
              <p>{{ type.description }}</p>
            </div>
          </div>
        </div>

        <div class="report-filters">
          <!-- Filter tanggal untuk laporan harian -->
          <div v-if="selectedType === 'daily'" class="form-group">
            <label>Tanggal</label>
            <input type="date" v-model="filters.tanggal" class="form-input" />
          </div>

          <!-- Filter bulan/tahun untuk laporan bulanan -->
          <div v-if="selectedType === 'monthly'" class="form-group-row">
            <div class="form-group">
              <label>Bulan</label>
              <select v-model="filters.bulan" class="form-input">
                <option v-for="(nama, idx) in namaBulan" :key="idx" :value="idx + 1">{{ nama }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Tahun</label>
              <input type="number" v-model="filters.tahun" class="form-input" min="2020" :max="currentYear" />
            </div>
          </div>

          <!-- Pilih pasien untuk laporan per pasien -->
          <div v-if="selectedType === 'patient'" class="form-group">
            <label>Cari Pasien</label>
            <input
              type="text"
              v-model="patientSearch"
              @input="debouncedPatientSearch"
              placeholder="Ketik nama atau NRM pasien..."
              class="form-input"
            />
            <div v-if="patientResults.length > 0" class="patient-dropdown">
              <div
                v-for="p in patientResults"
                :key="p.id"
                class="patient-option"
                @click="selectPatient(p)"
              >
                <strong>{{ p.nama }}</strong>
                <span>NRM: {{ p.nrm }}</span>
              </div>
            </div>
            <div v-if="selectedPatient" class="selected-patient">
              ✓ {{ selectedPatient.nama }} ({{ selectedPatient.nrm }})
              <button @click="selectedPatient = null" class="btn-clear">×</button>
            </div>
          </div>

          <div v-if="error" class="alert-error">{{ error }}</div>

          <button
            @click="generateReport"
            :disabled="loading || (selectedType === 'patient' && !selectedPatient)"
            class="btn-primary-full"
          >
            <span v-if="loading">⏳ Memuat Data...</span>
            <span v-else>Generate Laporan</span>
          </button>
        </div>
      </div>

      <!-- Panel info sebelah kanan -->
      <div class="report-info-card">
        <h3>Panduan</h3>
        <div class="guide-list">
          <div class="guide-item">
            <span class="guide-num">1</span>
            <p>Pilih jenis laporan yang ingin di-generate</p>
          </div>
          <div class="guide-item">
            <span class="guide-num">2</span>
            <p>Atur filter periode atau pilih pasien</p>
          </div>
          <div class="guide-item">
            <span class="guide-num">3</span>
            <p>Klik <strong>Generate Laporan</strong> untuk melihat preview</p>
          </div>
          <div class="guide-item">
            <span class="guide-num">4</span>
            <p>Klik <strong>Cetak / Simpan PDF</strong> untuk mengunduh</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================ -->
    <!-- AREA PREVIEW & PRINT                                          -->
    <!-- ============================================================ -->
    <div v-if="reportData" class="preview-section">
      <div class="preview-toolbar no-print">
        <h3>Preview Laporan</h3>
        <button @click="printReport" class="btn-print">🖨️ Cetak / Simpan PDF</button>
      </div>

      <!-- ── LAPORAN HARIAN ── -->
      <div v-if="selectedType === 'daily'" id="print-area" class="print-area">
        <div class="laporan-header">
          <h2>LAPORAN HARIAN</h2>
          <h3>Klinik Sitara</h3>
          <p>Tanggal: {{ formatDateLabel(reportData.tanggal) }}</p>
          <p>Dilaporkan oleh: {{ reportData.dilaporkan_oleh }}</p>
        </div>
        <hr />

        <h4>Ringkasan</h4>
        <table class="laporan-table">
          <thead><tr><th>Indikator</th><th>Jumlah</th></tr></thead>
          <tbody>
            <tr><td>Pasien Baru</td><td>{{ reportData.ringkasan.pasien_baru }}</td></tr>
            <tr><td>Total Pasien Aktif</td><td>{{ reportData.ringkasan.total_pasien_aktif }}</td></tr>
            <tr><td>Total Antrian</td><td>{{ reportData.ringkasan.total_antrian }}</td></tr>
            <tr><td>Antrian Selesai</td><td>{{ reportData.ringkasan.antrian_selesai }}</td></tr>
            <tr><td>Tidak Hadir</td><td>{{ reportData.ringkasan.antrian_tidak_hadir }}</td></tr>
            <tr><td>Total Assessment</td><td>{{ reportData.ringkasan.total_assessment }}</td></tr>
            <tr><td>Terapi Aktif</td><td>{{ reportData.ringkasan.total_terapi_aktif }}</td></tr>
            <tr><td>Terapi Baru</td><td>{{ reportData.ringkasan.total_terapi_baru }}</td></tr>
            <tr><td>Sesi Monitoring</td><td>{{ reportData.ringkasan.total_monitoring }}</td></tr>
            <tr><td>Rata-rata Skor Monitoring</td><td>{{ reportData.ringkasan.rata_rata_skor_monitoring }}</td></tr>
          </tbody>
        </table>

        <h4>Antrian Per Jenis Layanan</h4>
        <table class="laporan-table">
          <thead><tr><th>Jenis Layanan</th><th>Jumlah</th></tr></thead>
          <tbody>
            <tr v-for="item in reportData.antrian_per_jenis" :key="item.jenis_layanan">
              <td>{{ item.jenis_layanan }}</td>
              <td>{{ item.total }}</td>
            </tr>
            <tr v-if="!reportData.antrian_per_jenis?.length">
              <td colspan="2" style="text-align:center;color:#94a3b8">Tidak ada data</td>
            </tr>
          </tbody>
        </table>

        <p class="laporan-footer">Dicetak: {{ printedAt }}</p>
      </div>

      <!-- ── LAPORAN BULANAN ── -->
      <div v-if="selectedType === 'monthly'" id="print-area" class="print-area">
        <div class="laporan-header">
          <h2>LAPORAN BULANAN</h2>
          <h3>Klinik Sitara</h3>
          <p>Periode: {{ namaBulan[filters.bulan - 1] }} {{ filters.tahun }}</p>
        </div>
        <hr />

        <h4>Ringkasan Bulanan</h4>
        <table class="laporan-table">
          <thead><tr><th>Indikator</th><th>Jumlah</th></tr></thead>
          <tbody>
            <tr v-for="(val, key) in reportData.ringkasan" :key="key">
              <td>{{ formatKey(key) }}</td>
              <td>{{ val }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="reportData.per_minggu?.length">
          <h4>Tren Per Minggu</h4>
          <table class="laporan-table">
            <thead><tr><th>Minggu</th><th>Antrian</th><th>Assessment</th><th>Monitoring</th></tr></thead>
            <tbody>
              <tr v-for="w in reportData.per_minggu" :key="w.minggu">
                <td>Minggu {{ w.minggu }}</td>
                <td>{{ w.antrian ?? '-' }}</td>
                <td>{{ w.assessment ?? '-' }}</td>
                <td>{{ w.monitoring ?? '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="laporan-footer">Dicetak: {{ printedAt }}</p>
      </div>

      <!-- ── LAPORAN PER PASIEN (PEMANTAUAN TUMBUH KEMBANG ANAK) ── -->
      <MonitoringReportTemplate
        v-if="selectedType === 'patient'"
        :patient-data="reportData?.pasien"
        :therapy-data="reportData?.riwayat_terapi?.[0]"
        :summary-data="{ 
          total_sesi: reportData?.statistik?.total_sesi_monitoring,
          rata_skor: reportData?.statistik?.rata_rata_skor
        }"
        :progress-trend="reportData?.tren_progress || []"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useNavigation } from '../../../shared/composables/useNavigation'
import api from '../../../core/services/api'
import MonitoringReportTemplate from '../components/MonitoringReportTemplate.vue'

const { goToDashboard } = useNavigation()

// ── State ─────────────────────────────────────────────────────────────
const selectedType = ref('daily')
const loading = ref(false)
const error = ref('')
const reportData = ref(null)
const printedAt = ref('')

const currentYear = new Date().getFullYear()
const todayStr = new Date().toISOString().split('T')[0]

const filters = ref({
  tanggal: todayStr,
  bulan: new Date().getMonth() + 1,
  tahun: currentYear,
})

// Pencarian pasien
const patientSearch = ref('')
const patientResults = ref([])
const selectedPatient = ref(null)
let patientSearchTimer = null

// ── Config ────────────────────────────────────────────────────────────
const namaBulan = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const reportTypes = [
  {
    id: 'daily',
    label: 'Laporan Harian',
    description: 'Rekap antrian dan layanan hari ini',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`
  },
  {
    id: 'monthly',
    label: 'Statistik Bulanan',
    description: 'Tren pertumbuhan dan kunjungan',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>`
  },
  {
    id: 'patient',
    label: 'Laporan Per Pasien',
    description: 'Riwayat lengkap medis satu pasien',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#0f766e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`
  },
]

// ── Helpers ───────────────────────────────────────────────────────────
const formatDateLabel = (d) => {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
}

const formatKey = (key) =>
  key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())

// ── Patient search ─────────────────────────────────────────────────────
const debouncedPatientSearch = () => {
  clearTimeout(patientSearchTimer)
  patientSearchTimer = setTimeout(async () => {
    if (patientSearch.value.length < 2) { patientResults.value = []; return }
    try {
      const res = await api.get('/patients', { params: { search: patientSearch.value, per_page: 10 } })
      patientResults.value = (res.data?.data ?? []).map(p => ({
        id: p.id_pasien ?? p.id,
        nama: p.nama_lengkap ?? p.nama,
        nrm: p.nrm,
      }))
    } catch { patientResults.value = [] }
  }, 400)
}

const selectPatient = (p) => {
  selectedPatient.value = p
  patientSearch.value = ''
  patientResults.value = []
}

// ── Generate laporan ───────────────────────────────────────────────────
const generateReport = async () => {
  error.value = ''
  reportData.value = null
  loading.value = true

  try {
    let res

    if (selectedType.value === 'daily') {
      res = await api.get('/reports/daily', { params: { tanggal: filters.value.tanggal } })
    } else if (selectedType.value === 'monthly') {
      res = await api.get('/reports/monthly', {
        params: { bulan: filters.value.bulan, tahun: filters.value.tahun }
      })
    } else if (selectedType.value === 'patient') {
      if (!selectedPatient.value) {
        error.value = 'Pilih pasien terlebih dahulu.'
        loading.value = false
        return
      }
      res = await api.get(`/reports/patient/${selectedPatient.value.id}`)
    }

    reportData.value = res.data?.data ?? res.data
    printedAt.value = new Date().toLocaleString('id-ID')
  } catch (err) {
    error.value = err.response?.data?.message ?? 'Gagal mengambil data laporan.'
  } finally {
    loading.value = false
  }
}

// ── Print ke PDF via browser ───────────────────────────────────────────
const printReport = () => {
  window.print()
}
</script>

<style scoped>
/* ── Layout ────────────────────────────────────────────────────── */
.page-container { padding: 2rem; max-width: 1200px; margin: 0 auto; }
.page-header { margin-bottom: 2rem; }
.page-title { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0; }
.page-subtitle { color: #64748b; margin: 0.25rem 0 0; }
.btn-back { display: inline-flex; align-items: center; gap: 0.5rem; background: none; border: none; color: #3b82f6; font-weight: 600; cursor: pointer; font-size: 0.875rem; margin-bottom: 0.75rem; padding: 0; }

.reports-grid { display: grid; grid-template-columns: 1fr 320px; gap: 2rem; margin-bottom: 2rem; }

/* ── Kartu pilih laporan ───────────────────────────────────────── */
.report-selection-card, .report-info-card {
  background: white; padding: 2rem; border-radius: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.report-selection-card h3, .report-info-card h3 {
  font-size: 1.125rem; font-weight: 700; color: #334155; margin: 0 0 1.5rem;
}

.report-types { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem; }
.report-type-item {
  display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem;
  border: 1.5px solid #e2e8f0; border-radius: 0.75rem; cursor: pointer; transition: all 0.15s;
}
.report-type-item:hover { background: #f8fafc; border-color: #cbd5e1; }
.report-type-item.active { background: #eff6ff; border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
.report-icon {
  width: 44px; height: 44px; background: #f8fafc; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  box-shadow: 0 1px 2px rgba(0,0,0,0.06);
}
.report-info strong { display: block; font-size: 0.9375rem; color: #1e293b; }
.report-info p { font-size: 0.8125rem; color: #64748b; margin: 0; }

/* ── Filter ────────────────────────────────────────────────────── */
.report-filters { padding-top: 1.5rem; border-top: 1px solid #f1f5f9; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem; }
.form-group-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }
.form-input {
  width: 100%; padding: 0.625rem 1rem; border: 1px solid #e2e8f0;
  border-radius: 0.5rem; font-size: 0.875rem; box-sizing: border-box; outline: none;
}
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

/* Dropdown patient search */
.patient-dropdown {
  border: 1px solid #e2e8f0; border-radius: 0.5rem; background: white;
  max-height: 180px; overflow-y: auto; margin-top: 0.25rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.patient-option {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.625rem 1rem; cursor: pointer; font-size: 0.875rem;
}
.patient-option:hover { background: #f0f9ff; }
.patient-option strong { color: #1e293b; }
.patient-option span { color: #94a3b8; font-size: 0.8rem; }
.selected-patient {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.5rem 0.875rem; background: #f0fdf4; border: 1px solid #bbf7d0;
  border-radius: 0.5rem; font-size: 0.875rem; color: #166534; margin-top: 0.5rem;
}
.btn-clear { background: none; border: none; cursor: pointer; color: #64748b; font-size: 1.1rem; line-height: 1; padding: 0 0.25rem; }

.btn-primary-full {
  width: 100%; padding: 0.875rem; background: #2563eb; color: white;
  border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer;
  font-size: 0.9375rem; transition: background 0.15s;
}
.btn-primary-full:hover:not(:disabled) { background: #1d4ed8; }
.btn-primary-full:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Panduan ────────────────────────────────────────────────────── */
.guide-list { display: flex; flex-direction: column; gap: 1rem; }
.guide-item { display: flex; align-items: flex-start; gap: 0.875rem; }
.guide-num {
  width: 26px; height: 26px; background: #eff6ff; color: #2563eb;
  border-radius: 50%; font-weight: 700; font-size: 0.8125rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.guide-item p { font-size: 0.875rem; color: #475569; margin: 0; line-height: 1.5; }

/* ── Alert ─────────────────────────────────────────────────────── */
.alert-error {
  background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem;
  padding: 0.625rem 0.875rem; color: #dc2626; font-size: 0.875rem; margin-bottom: 1rem;
}

/* ── Preview area ──────────────────────────────────────────────── */
.preview-section { background: white; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.preview-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9;
}
.preview-toolbar h3 { font-size: 1rem; font-weight: 700; color: #334155; margin: 0; }
.btn-print {
  padding: 0.625rem 1.25rem; background: #0f766e; color: white;
  border: none; border-radius: 0.5rem; font-weight: 600; cursor: pointer; font-size: 0.875rem;
}
.btn-print:hover { background: #0d5e57; }

/* ── Laporan (preview & print) ──────────────────────────────────── */
.print-area { padding: 2rem; }
.laporan-header { text-align: center; margin-bottom: 1.5rem; }
.laporan-header h2 { font-size: 1.375rem; font-weight: 800; color: #1e293b; margin: 0; }
.laporan-header h3 { font-size: 1rem; font-weight: 600; color: #475569; margin: 0.25rem 0; }
.laporan-header p { font-size: 0.875rem; color: #64748b; margin: 0.125rem 0; }
.print-area hr { border: none; border-top: 2px solid #1e293b; margin: 1.25rem 0; }
.print-area h4 { font-size: 0.9375rem; font-weight: 700; color: #1e293b; margin: 1.5rem 0 0.75rem; }

.laporan-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 0.875rem; }
.laporan-table th {
  background: #1e40af; color: white; padding: 0.625rem 1rem;
  text-align: left; font-weight: 600; font-size: 0.8125rem;
}
.laporan-table td { padding: 0.5rem 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
.laporan-table tr:nth-child(even) td { background: #f8fafc; }

.laporan-footer { margin-top: 2rem; font-size: 0.75rem; color: #94a3b8; text-align: right; }

@media (max-width: 1024px) {
  .reports-grid { grid-template-columns: 1fr; }
}

/* ── PRINT MODE ─────────────────────────────────────────────────── */
@media print {
  .no-print { display: none !important; }
  .page-container { padding: 0; }
  .preview-section { box-shadow: none; border-radius: 0; }
  .print-area { padding: 0; }
  .laporan-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
