<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TherapyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_terapi,
            'nama_terapi' => $this->nama_terapi,
            'deskripsi' => $this->deskripsi,
            'rencana' => [
                'dosis' => $this->dosis,
                'durasi_hari' => $this->durasi_hari,
                'frekuensi_per_minggu' => $this->frekuensi_per_minggu,
            ],
            'jadwal' => [
                'mulai' => $this->tanggal_mulai->format('Y-m-d'),
                'selesai' => $this->tanggal_selesai ? $this->tanggal_selesai->format('Y-m-d') : null,
            ],
            'status' => $this->status,
            'pasien' => [
                'id'         => $this->patient->id_pasien,
                'nama'       => $this->patient->nama_lengkap,
                'nrm'        => $this->patient->nrm,
                'tanggal_lahir' => $this->patient->tanggal_lahir?->format('Y-m-d'),
                'nama_wali'  => $this->patient->nama_wali,
                // NIK penuh hanya dikirim saat terapi sedang berjalan (sesi aktif)
                'nik'        => ($this->status === 'berjalan') ? $this->patient->nik : null,
                'masked_nik' => $this->patient->masked_nik,
            ],
            'terapis' => [
                'id' => $this->terapis->id,
                'nama' => $this->terapis->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
