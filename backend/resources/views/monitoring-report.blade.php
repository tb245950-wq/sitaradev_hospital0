<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemantauan Tumbuh Kembang Anak</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .pdf-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
        }
        
        /* HEADER */
        .header {
            text-align: center;
            border-bottom: 3px solid #2C3E50;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .hospital-name {
            font-size: 16px;
            font-weight: bold;
            color: #2C3E50;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #34495E;
            margin-top: 10px;
            text-transform: uppercase;
        }
        
        /* SECTIONS */
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: white;
            background-color: #2C3E50;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-left: 4px solid #3498DB;
        }
        
        .section-number {
            display: inline-block;
            background-color: #3498DB;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: bold;
            margin-right: 8px;
            font-size: 11px;
        }
        
        /* INFO UMUM */
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background: #F8F9FA;
            border: 1px solid #E0E0E0;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            padding: 8px 12px;
            font-weight: bold;
            border: 1px solid #E0E0E0;
            background-color: #ECF0F1;
            font-size: 11px;
        }
        
        .info-value {
            display: table-cell;
            width: 65%;
            padding: 8px 12px;
            border: 1px solid #E0E0E0;
            font-size: 11px;
        }
        
        /* MEASUREMENT TABLE */
        .measurement-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        
        .measurement-table td {
            padding: 10px 12px;
            border: 1px solid #BDC3C7;
        }
        
        .measurement-item {
            width: 50%;
            background-color: #ECF0F1;
            font-weight: bold;
        }
        
        .measurement-value {
            width: 50%;
        }
        
        /* DEVELOPMENT CHECKLIST */
        .dev-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .dev-table th {
            background-color: #34495E;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2C3E50;
        }
        
        .dev-table td {
            padding: 8px;
            border: 1px solid #BDC3C7;
            vertical-align: top;
        }
        
        .dev-table tr:nth-child(even) {
            background-color: #F8F9FA;
        }
        
        .aspect-name {
            font-weight: bold;
            width: 20%;
        }
        
        .achievement {
            width: 40%;
        }
        
        .notes-cell {
            width: 40%;
        }
        
        /* CONCLUSION */
        .conclusion-box {
            background-color: #FFF3CD;
            border: 1px solid #FFC107;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 11px;
            line-height: 1.5;
        }
        
        .recommendation-box {
            background-color: #D1ECF1;
            border: 1px solid #17A2B8;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 11px;
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
            background-color: #D4EDDA;
            border: 1px solid #28A745;
            padding: 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            color: #155724;
        }
        
        /* PROGRESS TREND */
        .trend-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .trend-table th {
            background-color: #7F8C8D;
            color: white;
            padding: 6px;
            text-align: center;
            font-weight: bold;
        }
        
        .trend-table td {
            padding: 6px;
            border: 1px solid #BDC3C7;
            text-align: center;
        }
        
        .trend-table tr:nth-child(even) {
            background-color: #ECF0F1;
        }
        
        /* FOOTER */
        .footer {
            margin-top: 30px;
            border-top: 2px solid #BDC3C7;
            padding-top: 15px;
            display: table;
            width: 100%;
        }
        
        .footer-section {
            display: table-cell;
            width: 50%;
            text-align: center;
            font-size: 10px;
            padding: 0 10px;
        }
        
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
        
        .page-number {
            text-align: center;
            font-size: 9px;
            margin-top: 20px;
            color: #7F8C8D;
        }
        
        /* UTILITIES */
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .status-good {
            color: #27AE60;
            font-weight: bold;
        }
        
        .status-warning {
            color: #F39C12;
            font-weight: bold;
        }
        
        .status-alert {
            color: #E74C3C;
            font-weight: bold;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .pdf-container {
                padding: 15mm;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-container">
        
        <!-- HEADER -->
        <div class="header">
            <div class="hospital-name">🏥 KLINIK TUMBUH KEMBANG ANAK - SITARA</div>
            <div class="report-title">Laporan Pemantauan Tumbuh Kembang Anak</div>
        </div>
        
        <!-- SECTION I: INFORMASI UMUM -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">I</span>
                Informasi Umum
            </div>
            
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Nama Anak</div>
                    <div class="info-value"><strong>{{ $summaryData['nama_anak'] }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Lahir / Usia</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($summaryData['tanggal_lahir'])->format('d M Y') }} / {{ $summaryData['usia'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Nama Orang Tua / Wali</div>
                    <div class="info-value">{{ $summaryData['nama_orang_tua'] }} ({{ $summaryData['hubungan_wali'] }})</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Pemeriksaan</div>
                    <div class="info-value"><strong>{{ $summaryData['tanggal_pemeriksaan'] }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Pemeriksa</div>
                    <div class="info-value">{{ $summaryData['pemeriksa'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jenis Terapi</div>
                    <div class="info-value">{{ $summaryData['jenis_terapi'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Sesi Monitoring</div>
                    <div class="info-value"><strong>{{ $summaryData['total_sesi'] }} Sesi</strong> | Rata-rata Skor: <span class="status-good">{{ $summaryData['rata_skor'] }}%</span></div>
                </div>
            </div>
        </div>
        
        <!-- SECTION II: HASIL PENGUKURAN PERTUMBUHAN -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">II</span>
                Hasil Pengukuran Pertumbuhan (Antropometri)
            </div>
            
            <table class="measurement-table">
                <tr>
                    <td class="measurement-item">Berat Badan</td>
                    <td class="measurement-value">Sesuai kurva pertumbuhan WHO</td>
                </tr>
                <tr>
                    <td class="measurement-item">Tinggi Badan</td>
                    <td class="measurement-value">Normal untuk usia</td>
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
            <div class="section-title">
                <span class="section-number">III</span>
                Evaluasi Perkembangan (Milestone Checklist)
            </div>
            
            <table class="dev-table">
                <thead>
                    <tr>
                        <th class="aspect-name">Aspek yang Dinilai</th>
                        <th class="achievement">Kemampuan yang Tercapai</th>
                        <th class="notes-cell">Catatan & Observasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($developmentData as $dev)
                    <tr>
                        <td class="aspect-name">
                            <strong>{{ $dev['aspek'] }}</strong>
                        </td>
                        <td class="achievement">
                            {{ $dev['pencapaian'] }}
                        </td>
                        <td class="notes-cell">
                            {{ $dev['catatan'] }}<br>
                            <strong class="@if($dev['status'] === 'Baik') status-good @else status-warning @endif">
                                Status: {{ $dev['status'] }}
                            </strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- SECTION IV: KESIMPULAN & REKOMENDASI -->
        <div class="section">
            <div class="section-title">
                <span class="section-number">IV</span>
                Kesimpulan dan Rekomendasi Dokter/Terapis
            </div>
            
            <div class="conclusion-box">
                <strong>Kesimpulan:</strong><br><br>
                {{ $patient->nama_lengkap }} menunjukkan perkembangan yang sesuai dengan tahapan usianya (age-appropriate). 
                Rata-rata progress score monitoring: <strong class="status-good">{{ $summaryData['rata_skor'] }}%</strong>.<br><br>
                Terapi {{ $summaryData['jenis_terapi'] }} telah berlangsung selama <strong>{{ $summaryData['total_sesi'] }} sesi</strong>. 
                Pasien menunjukkan respons positif terhadap program terapi yang diberikan.
            </div>
            
            <div class="recommendation-box">
                <strong>🎯 Rekomendasi Stimulasi Lanjutan di Rumah:</strong>
                <ul>
                    @foreach($recommendations as $rec)
                    <li>{{ $rec }}</li>
                    @endforeach
                </ul>
            </div>
            
            <div class="next-schedule">
                📅 Jadwal Kontrol Berikutnya: 3 Bulan lagi ({{ \Carbon\Carbon::now()->addMonths(3)->format('F Y') }}) 
                untuk pemantauan rutin
            </div>
        </div>
        
        <!-- PROGRESS TREND (Optional) -->
        @if(!empty($progressTrend))
        <div class="section">
            <div class="section-title" style="margin-top: 25px;">
                <span class="section-number">V</span>
                Tren Perkembangan - 5 Sesi Terakhir
            </div>
            
            <table class="trend-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Score</th>
                        <th>Kehadiran</th>
                        <th>Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($progressTrend, -5) as $trend)
                    <tr>
                        <td>{{ $trend['tanggal'] }}</td>
                        <td><strong>{{ $trend['skor'] }}%</strong></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $trend['kehadiran'])) }}</td>
                        <td>{{ $trend['kondisi'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        
        <!-- FOOTER & SIGNATURE -->
        <div class="footer">
            <div class="footer-section">
                <div style="margin-bottom: 50px;">Dokter / Terapis Pendamping</div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px; font-size: 9px;">NIP: _________________</div>
            </div>
            <div class="footer-section">
                <div style="margin-bottom: 50px;">Orang Tua / Wali Anak</div>
                <div class="signature-line"></div>
                <div style="margin-top: 5px;">{{ $summaryData['nama_orang_tua'] }}</div>
            </div>
        </div>
        
        <div class="page-number">
            Laporan dibuat otomatis oleh Sistem Manajemen Rumah Sakit SITARA | 
            {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        </div>
        
    </div>
</body>
</html>
