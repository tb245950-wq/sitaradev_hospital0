# 📋 Laporan Pemantauan Tumbuh Kembang Anak (PDF)

## Deskripsi Fitur

Fitur ini memungkinkan staff (Admin, Dokter, Terapis) untuk generate laporan PDF profesional tentang pemantauan tumbuh kembang anak berdasarkan data monitoring terapi. Laporan dirancang untuk diberikan kepada orang tua/wali dan mencakup informasi lengkap tentang perkembangan anak.

## Format Laporan

Laporan PDF terdiri dari **4 bagian utama** (sesuai standar pemantauan tumbuh kembang):

### I. Informasi Umum
- **Nama Anak**: Nama lengkap pasien
- **Tanggal Lahir / Usia**: Dihitung otomatis dalam format "X Tahun Y Bulan"
- **Nama Orang Tua / Wali**: Nama wali dan hubungan keluarga
- **Tanggal Pemeriksaan**: Tanggal sesi monitoring terakhir
- **Pemeriksa**: Tim Klinik Tumbuh Kembang Anak
- **Jenis Terapi**: Nama terapi yang dijalani
- **Total Sesi Monitoring**: Jumlah sesi + rata-rata skor progress

### II. Hasil Pengukuran Pertumbuhan (Antropometri)
Tabel yang menampilkan:
- Berat Badan (Sesuai kurva pertumbuhan WHO)
- Tinggi Badan (Normal untuk usia)
- Lingkar Kepala (Dalam batas normal)
- Status Gizi (Baik)

### III. Evaluasi Perkembangan (Milestone Checklist)
Tabel penilaian 4 aspek perkembangan:
- **Motorik Kasar**: Kemampuan besar (berlari, melompat, dll)
- **Motorik Halus**: Kemampuan halus (menulis, meremas, dll)
- **Bahasa & Komunikasi**: Kemampuan berbicara dan mengerti
- **Sosial & Emosional**: Interaksi dan ekspresi emosi

Setiap aspek mencakup:
- Kemampuan yang tercapai
- Catatan & observasi
- Status (Baik / Perlu Perhatian)

### IV. Kesimpulan dan Rekomendasi
Berisi:
- **Kesimpulan**: Ringkasan perkembangan anak vs standar usia
- **Rekomendasi Stimulasi**: Tips untuk orang tua di rumah (dari monitoring terakhir)
- **Jadwal Kontrol Berikutnya**: Otomatis dihitung 3 bulan ke depan

### V. Tren Perkembangan (Optional)
Tabel 5 sesi monitoring terakhir dengan:
- Tanggal sesi
- Score progress
- Kehadiran
- Kondisi pasien

## Endpoint API

### Generate PDF Laporan Monitoring

```http
GET /api/monitorings/{id_pasien}/report-pdf
GET /api/monitorings/{id_pasien}/{id_terapi}/report-pdf
```

**Method**: GET  
**Auth**: Sanctum (Bearrer token)  
**Role**: Admin, Dokter, Terapis

**Parameters:**
- `id_pasien` (required): ID pasien
- `id_terapi` (optional): ID terapi spesifik (jika tidak ada, sistem ambil terapi aktif terakhir)

**Response:**
- PDF file download langsung
- Filename format: `Laporan_Monitoring_[NamaAnak]_[Timestamp].pdf`

**Error Responses:**
```json
{
  "success": false,
  "message": "Belum ada sesi monitoring yang hadir"
}

{
  "success": false,
  "message": "Hanya staff yang dapat generate laporan"
}
```

## Cara Menggunakan

### Dari Backend / Testing

```bash
# Generate laporan untuk pasien tertentu
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/monitorings/123/report-pdf

# Generate laporan untuk pasien + terapi spesifik
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/monitorings/123/456/report-pdf
```

### Dari Frontend (Vue.js)

```javascript
// Import di komponen monitoring
import { api } from '@/core/services/api'

// Generate dan download laporan
async function downloadMonitoringReport(idPasien, idTerapi = null) {
  try {
    const url = idTerapi 
      ? `/api/monitorings/${idPasien}/${idTerapi}/report-pdf`
      : `/api/monitorings/${idPasien}/report-pdf`
    
    const response = await api.get(url, {
      responseType: 'blob'
    })
    
    // Create download link
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = `Laporan_Monitoring_${new Date().toISOString().split('T')[0]}.pdf`
    link.click()
  } catch (error) {
    console.error('Error generating report:', error)
  }
}
```

### Tombol di UI

Tambahkan tombol di tabel monitoring atau detail pasien:

```vue
<button 
  @click="downloadMonitoringReport(monitoring.id_pasien, monitoring.therapy?.id)"
  class="btn-action report"
  title="Download Laporan PDF"
>
  📄 Laporan
</button>
```

## Data yang Digunakan

Laporan mengambil data dari:

1. **Tabel Patients**:
   - nama_lengkap
   - tanggal_lahir
   - nama_wali
   - hubungan_wali

2. **Tabel Therapies**:
   - nama_terapi
   - status
   - deskripsi

3. **Tabel TherapyMonitoring** (hanya yang kehadiran = 'hadir'):
   - tanggal_sesi
   - progress_score
   - kehadiran
   - kondisi_pasien
   - catatan_perkembangan
   - rekomendasi

## Persyaratan Data

Agar laporan dapat di-generate:
1. ✅ **Pasien** harus memiliki data lengkap
2. ✅ **Terapi aktif** minimal ada 1 sesi monitoring dengan kehadiran "hadir"
3. ✅ **Monitoring** harus diisi dengan minimal:
   - Tanggal sesi
   - Kehadiran (hadir)
   - Progress score
   - Catatan perkembangan
   - Rekomendasi

## Styling & Template

Laporan menggunakan styling profesional dengan:
- **Header**: Nama rumah sakit + judul laporan
- **Sections**: Numbered sections (I, II, III, IV) dengan warna blue (#2C3E50, #3498DB)
- **Info Grid**: Background abu-abu untuk label, border halus
- **Tables**: Professional styling dengan alternating row colors
- **Boxes**: Warna highlight untuk kesimpulan, rekomendasi, jadwal kontrol
- **Footer**: Tempat signature dokter & orang tua + tanggal laporan

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Error 403 Akses Ditolak | Pastikan user role adalah admin, dokter, atau terapis |
| Error 404 Pasien Tidak Ditemukan | Pastikan id_pasien valid |
| Error "Belum ada sesi monitoring" | Buat minimal 1 sesi monitoring dengan kehadiran "hadir" |
| Laporan PDF kosong | Pastikan data monitoring lengkap |

## Testing Data Dummy

Jika menggunakan data dummy (HospitalDummySeeder1000):
- Data sudah memiliki terapi aktif + monitoring sessions
- Jalankan: `php artisan db:seed --class=HospitalDummySeeder1000`
- Test endpoint: `GET /api/monitorings/1/report-pdf`

## File-File yang Dimodifikasi/Ditambah

✅ **Service** (Backend Logic):
- `app/Services/MonitoringReportPdfService.php` (NEW)

✅ **Controller** (API Endpoint):
- `app/Http/Controllers/Api/MonitoringController.php` (MODIFIED - added generateMonitoringReportPdf method)

✅ **View** (PDF Template):
- `resources/views/monitoring-report.blade.php` (NEW)

✅ **Routes**:
- `routes/api.php` (MODIFIED - added PDF report routes)

## Catatan Penting

1. **Persyaratan Package**: Memastikan `laravel-dompdf` atau `barryvdh/laravel-dompdf` sudah terinstall
   ```bash
   composer require barryvdh/laravel-dompdf
   ```

2. **Usia Otomatis**: Usia dihitung otomatis dari `tanggal_lahir` dalam format "X Tahun Y Bulan"

3. **Rekomendasi Fleksibel**: Rekomendasi diambil dari field `rekomendasi` monitoring terakhir, bisa berisi multiple item (split dengan `|`)

4. **Tanggal Kontrol**: Jadwal kontrol berikutnya otomatis 3 bulan dari hari ini

5. **Signature**: Ada tempat kosong untuk signature dokter/terapis dan orang tua (untuk ditandatangani manual)

---

**Dibuat untuk**: Sistem Manajemen Rumah Sakit SITARA  
**Version**: 1.0.0  
**Last Updated**: 2026-07-06
