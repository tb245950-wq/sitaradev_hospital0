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
            // Terapi
            'therapy' => [
                'id' => $this->therapy->id_terapi,
                'nama_terapi' => $this->therapy->nama_terapi,
                'status' => $this->therapy->status,
            ],
            // Pasien — flat structure untuk memudahkan akses di frontend
            'pasien' => [
                'id' => $this->patient->id_pasien,
                'nama' => $this->patient->nama_lengkap,
                'nrm' => $this->patient->nrm,
            ],
            // Terapis — flat structure
            'terapis' => [
                'id' => $this->terapis->id,
                'nama' => $this->terapis->name,
            ],
            // Sesi detail
            'tanggal_sesi' => $this->tanggal_sesi?->format('Y-m-d'),
            'waktu_mulai' => $this->waktu_mulai,
            'waktu_selesai' => $this->waktu_selesai,
            // Status kehadiran dan progress
            'kehadiran' => $this->kehadiran,
            'progress_score' => $this->progress_score,
            // Catatan detail
            'catatan_perkembangan' => $this->catatan_perkembangan,
            'kondisi_pasien' => $this->kondisi_pasien,
            'rekomendasi' => $this->rekomendasi,
            // Metadata
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
