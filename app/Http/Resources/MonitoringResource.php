<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitoringResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_monitoring,
            'terapi' => [
                'id' => $this->therapy->id_terapi,
                'nama' => $this->therapy->nama_terapi,
            ],
            'pasien' => [
                'id' => $this->patient->id_pasien,
                'nama' => $this->patient->nama_lengkap,
                'nrm' => $this->patient->nrm,
            ],
            'sesi' => [
                'tanggal' => $this->tanggal_sesi->format('Y-m-d'),
                'jam' => $this->waktu_mulai . ' - ' . $this->waktu_selesai,
            ],
            'status' => [
                'kehadiran' => $this->kehadiran,
                'skor_progress' => $this->progress_score,
            ],
            'catatan' => [
                'perkembangan' => $this->catatan_perkembangan,
                'kondisi' => $this->kondisi_pasien,
                'rekomendasi' => $this->rekomendasi,
            ],
            'petugas' => [
                'id' => $this->terapis->id,
                'nama' => $this->terapis->name,
            ],
        ];
    }
}
