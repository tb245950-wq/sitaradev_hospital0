<!-- 
  MonitoringReportTemplate.vue
  
  Template untuk menampilkan Laporan Pemantauan Tumbuh Kembang Anak
  Digunakan di ReportsView.vue untuk menampilkan laporan pasien detail
-->

<template>
  <div id="print-area" class="print-area monitoring-report">
    <!-- HEADER -->
    <div class="laporan-header">
      <h1>KLINIK TUMBUH KEMBANG ANAK SITARA</h1>
      <h2>LAPORAN PEMANTAUAN TUMBUH KEMBANG ANAK</h2>
      <hr class="header-divider" />
    </div>

    <!-- SECTION I: INFORMASI UMUM -->
    <div class="section">
      <h3 class="section-title">I. INFORMASI UMUM</h3>

      <div class="info-grid">
        <div class="info-row">
          <div class="info-label">Nama Anak</div>
          <div class="info-value"><strong>{{ patientData?.nama_lengkap }}</strong></div>
        </div>
        <div class="info-row">
          <div class="info-label">Tanggal Lahir / Usia</div>
          <div class="info-value">{{ formatDate(patientData?.tanggal_lahir) }} / {{ calculateAge(patientData?.tanggal_lahir) }}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Nama Orang Tua / Wali</div>
          <div class="info-value">{{ patientData?.nama_wali }} ({{ patientData?.hubungan_wali }})</div>
        </div>
        <div class="info-row">
          <div class="info-label">NRM (Nomor Rekam Medis)</div>
          <div class="info-value"><strong>{{ patientData?.nrm }}</strong></div>
        </div>
        <div class="info-row">
          <div class="info-label">Tanggal Pemeriksaan</div>
          <div class="info-value"><strong>{{ currentDate }}</strong></div>
        </div>
        <div class="info-row">
          <div class="info-label">Jenis Terapi</div>
          <div class="info-value">{{ therapyData?.nama_terapi ?? 'Belum ada terapi' }}</div>
        </div>
        <div class="info-row">
          <div class="info-label">Total Sesi Monitoring</div>
          <div class="info-value"><strong>{{ summaryData?.total_sesi ?? 0 }} Sesi</strong> | Rata-rata Skor: <span class="score-badge">{{ summaryData?.rata_skor ?? 0 }}%</span></div>
        </div>
      </div>
    </div>

    <!-- SECTION II: PENGUKURAN PERTUMBUHAN -->
    <div class="section">
      <h3 class="section-title">II. HASIL PENGUKURAN PERTUMBUHAN (ANTROPOMETRI)</h3>

      <table class="measurement-table">
        <tr>
          <td class="measurement-item">Berat Badan</td>
          <td class="measurement-value">Sesuai kurva pertumbuhan WHO</td>
        </tr>
        <tr>
          <td class="measurement-item">Tinggi Badan</td>
          <td class="measurement-value">Normal / Tinggi untuk usia</td>
        </tr>
        <tr>
          <td class="measurement-item">Lingkar Kepala</td>
          <td class="measurement-value">Dalam batas normal</td>
        </tr>
        <tr>
          <td class="measurement-item">Status Gizi</td>
          <td class="measurement-value"><span class="status-good">Baik</span></td>
        </tr>
      </table>
    </div>

    <!-- SECTION III: EVALUASI PERKEMBANGAN -->
    <div class="section">
      <h3 class="section-title">III. EVALUASI PERKEMBANGAN (MILESTONE CHECKLIST)</h3>

      <table class="dev-table">
        <thead>
          <tr>
            <th class="aspect-name">Aspek yang Dinilai</th>
            <th class="achievement">Kemampuan yang Tercapai</th>
            <th class="notes-cell">Catatan & Observasi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="aspect-name"><strong>Motorik Kasar</strong></td>
            <td class="achievement">Berdiri seimbang, berlari, melompat, menaiki tangga</td>
            <td class="notes-cell">Koordinasi tubuh baik</td>
          </tr>
          <tr>
            <td class="aspect-name"><strong>Motorik Halus</strong></td>
            <td class="achievement">Memegang krayon dengan jari, menyusun balok, meniru gambar</td>
            <td class="notes-cell">Cengkeraman tangan kuat</td>
          </tr>
          <tr>
            <td class="aspect-name"><strong>Bahasa & Komunikasi</strong></td>
            <td class="achievement">Menyebutkan nama, menggunakan kalimat sederhana, mengerti instruksi</td>
            <td class="notes-cell">Komunikasi dua arah mulai aktif</td>
          </tr>
          <tr>
            <td class="aspect-name"><strong>Sosial & Emosional</strong></td>
            <td class="achievement">Bermain bersama teman, berbagi mainan, melepas baju sendiri</td>
            <td class="notes-cell">Dapat mengekspresikan emosi dengan baik</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- SECTION IV: KESIMPULAN & REKOMENDASI -->
    <div class="section">
      <h3 class="section-title">IV. KESIMPULAN DAN REKOMENDASI DOKTER / TERAPIS</h3>

      <div class="conclusion-box">
        <strong>Kesimpulan:</strong><br /><br />
        {{ patientData?.nama_lengkap }} menunjukkan perkembangan motorik, bahasa, dan sosial yang sesuai dengan 
        tahapan usianya (age-appropriate). Rata-rata progress score monitoring: 
        <strong>{{ summaryData?.rata_skor ?? 0 }}%</strong>.<br /><br />
        Terapi {{ therapyData?.nama_terapi }} telah berlangsung selama 
        <strong>{{ summaryData?.total_sesi ?? 0 }} sesi</strong> dengan respons positif dari pasien.
      </div>

      <div class="recommendation-box">
        <strong>Rekomendasi Stimulasi Lanjutan di Rumah:</strong>
        <ul>
          <li>Latih anak untuk menceritakan kembali kegiatan atau dongeng pendek</li>
          <li>Berikan mainan menyusun puzzle bertingkat 5-10 keping</li>
          <li>Ajak anak bermain lempar tangkap bola untuk melatih fokus dan koordinasi mata-tangan</li>
          <li>Lakukan aktivitas membaca bersama setiap hari selama 15-20 menit</li>
          <li>Dorong interaksi sosial dengan teman sebaya melalui bermain kelompok</li>
        </ul>
      </div>

      <div class="next-schedule">
        <strong>Jadwal Kontrol Berikutnya:</strong> 3 Bulan lagi ({{ nextSchedule }}) untuk pemantauan rutin
      </div>
    </div>

    <!-- SECTION V: TREN PERKEMBANGAN (Optional) -->
    <div v-if="progressTrend?.length" class="section">
      <h3 class="section-title">V. TREN PERKEMBANGAN - 5 SESI TERAKHIR</h3>

      <table class="trend-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal Sesi</th>
            <th>Score (%)</th>
            <th>Kehadiran</th>
            <th>Kondisi Pasien</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(trend, idx) in progressTrend.slice(-5)" :key="idx">
            <td style="text-align: center;">{{ idx + 1 }}</td>
            <td>{{ trend.tanggal }}</td>
            <td style="text-align: center;"><strong>{{ trend.skor }}</strong></td>
            <td style="text-align: center;">{{ formatKehadiran(trend.kehadiran) }}</td>
            <td>{{ trend.kondisi }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- FOOTER -->
    <div class="footer">
      <div class="footer-section">
        <div style="margin-bottom: 50px;">Dokter / Terapis Pendamping</div>
        <div class="signature-line"></div>
        <div style="margin-top: 5px; font-size: 0.75rem;">NIP: _________________</div>
      </div>
      <div class="footer-section">
        <div style="margin-bottom: 50px;">Orang Tua / Wali Anak</div>
        <div class="signature-line"></div>
        <div style="margin-top: 5px; font-size: 0.75rem;">{{ patientData?.nama_wali }}</div>
      </div>
    </div>

    <div class="page-number">
      Laporan dibuat otomatis oleh Sistem Manajemen Rumah Sakit SITARA | {{ currentDateTime }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  patientData: {
    type: Object,
    default: () => ({})
  },
  therapyData: {
    type: Object,
    default: () => ({})
  },
  summaryData: {
    type: Object,
    default: () => ({})
  },
  progressTrend: {
    type: Array,
    default: () => []
  }
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
})

const currentDateTime = computed(() => {
  return new Date().toLocaleString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

const nextSchedule = computed(() => {
  const date = new Date()
  date.setMonth(date.getMonth() + 3)
  return date.toLocaleDateString('id-ID', {
    month: 'long',
    year: 'numeric'
  })
})

const calculateAge = (birthDate) => {
  if (!birthDate) return '-'
  const birth = new Date(birthDate)
  const now = new Date()
  const years = now.getFullYear() - birth.getFullYear()
  const months = now.getMonth() - birth.getMonth()
  
  if (months < 0) {
    return `${years - 1} Tahun ${12 + months} Bulan`
  }
  return `${years} Tahun ${months} Bulan`
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  })
}

const formatKehadiran = (kehadiran) => {
  const mapping = {
    'hadir': 'Hadir',
    'tidak_hadir': 'Tidak Hadir',
    'izin': 'Izin',
    'sakit': 'Sakit'
  }
  return mapping[kehadiran] || kehadiran
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.monitoring-report {
  font-family: 'Arial', sans-serif;
  line-height: 1.6;
  color: #333;
}

/* HEADER */
.laporan-header {
  text-align: center;
  border-bottom: 2px solid #333;
  padding-bottom: 15px;
  margin-bottom: 25px;
}

.laporan-header h1 {
  font-size: 14px;
  font-weight: bold;
  color: #000;
  margin-bottom: 5px;
  letter-spacing: 0.5px;
}

.laporan-header h2 {
  font-size: 13px;
  font-weight: bold;
  color: #333;
  margin-bottom: 10px;
}

.header-divider {
  border: none;
  border-top: 1px solid #ccc;
  margin-top: 10px;
}

/* SECTIONS */
.section {
  margin-bottom: 20px;
  margin-top: 15px;
}

.section-title {
  font-size: 11px;
  font-weight: bold;
  color: #000;
  border-bottom: 2px solid #333;
  padding-bottom: 8px;
  margin-bottom: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* INFO GRID */
.info-grid {
  border: 1px solid #000;
  border-collapse: collapse;
  background: #fff;
  margin-bottom: 15px;
}

.info-row {
  display: flex;
  border-bottom: 1px solid #ccc;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  width: 30%;
  padding: 8px 10px;
  font-weight: bold;
  background-color: #f5f5f5;
  border-right: 1px solid #ccc;
  font-size: 10px;
}

.info-value {
  width: 70%;
  padding: 8px 10px;
  font-size: 10px;
}

/* MEASUREMENT TABLE */
.measurement-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
  font-size: 10px;
  border: 1px solid #000;
}

.measurement-table tr:nth-child(odd) {
  background-color: #f9f9f9;
}

.measurement-table tr:nth-child(even) {
  background-color: #fff;
}

.measurement-table td {
  padding: 8px 10px;
  border-right: 1px solid #ccc;
}

.measurement-table td:last-child {
  border-right: none;
}

.measurement-item {
  width: 40%;
  font-weight: bold;
}

.measurement-value {
  width: 60%;
}

/* DEV TABLE */
.dev-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
  font-size: 10px;
  border: 1px solid #000;
}

.dev-table th {
  background-color: #f5f5f5;
  color: #000;
  padding: 8px 10px;
  text-align: left;
  font-weight: bold;
  border: 1px solid #ccc;
}

.dev-table td {
  padding: 8px 10px;
  border: 1px solid #ccc;
  vertical-align: top;
}

.dev-table tr:nth-child(even) {
  background-color: #f9f9f9;
}

.aspect-name {
  width: 20%;
}

.achievement {
  width: 40%;
}

.notes-cell {
  width: 40%;
}

/* CONCLUSION & RECOMMENDATION */
.conclusion-box {
  background-color: #fff;
  border: 1px solid #000;
  padding: 10px;
  margin-bottom: 15px;
  margin-top: 15px;
  font-size: 10px;
  line-height: 1.5;
}

.recommendation-box {
  background-color: #fff;
  border: 1px solid #000;
  padding: 10px;
  margin-bottom: 15px;
  font-size: 10px;
  line-height: 1.5;
}

.recommendation-box ul {
  margin-left: 20px;
  margin-top: 8px;
}

.recommendation-box li {
  margin-bottom: 5px;
}

.next-schedule {
  background-color: #fff;
  border: 1px solid #000;
  padding: 10px;
  border-radius: 0;
  font-size: 10px;
  text-align: left;
  color: #000;
}

.score-badge {
  background-color: transparent;
  color: #000;
  padding: 0;
  border-radius: 0;
  font-weight: bold;
  font-size: 1em;
}

.status-good {
  color: #000;
  font-weight: bold;
}

/* TREND TABLE */
.trend-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 15px;
  font-size: 9px;
  border: 1px solid #000;
}

.trend-table th {
  background-color: #f5f5f5;
  color: #000;
  padding: 6px 8px;
  text-align: center;
  font-weight: bold;
  border: 1px solid #ccc;
}

.trend-table td {
  padding: 6px 8px;
  border: 1px solid #ccc;
  text-align: center;
}

.trend-table tr:nth-child(even) {
  background-color: #f9f9f9;
}

/* FOOTER */
.footer {
  margin-top: 30px;
  border-top: 1px solid #333;
  padding-top: 15px;
  display: flex;
  width: 100%;
  gap: 20px;
}

.footer-section {
  width: 50%;
  text-align: center;
  font-size: 9px;
  padding: 0 10px;
}

.signature-line {
  margin-top: 30px;
  border-top: 1px solid #333;
  margin-bottom: 40px;
  padding-top: 5px;
}

.page-number {
  text-align: center;
  font-size: 8px;
  margin-top: 20px;
  color: #666;
}

/* PRINT STYLES */
@media print {
  body {
    margin: 0;
    padding: 0;
  }
  .monitoring-report {
    padding: 15mm;
  }
}
</style>
