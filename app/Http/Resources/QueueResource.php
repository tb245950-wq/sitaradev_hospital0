<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_antrian,
            'nomor' => $this->nomor_antrian,
            'jenis' => $this->jenis_layanan,
            'status' => $this->status,
            'prioritas' => $this->prioritas,
            'waktu' => [
                'daftar' => $this->waktu_daftar,
                'panggil' => $this->waktu_panggil,
                'selesai' => $this->waktu_selesai,
            ],
            'pasien' => [
                'id' => $this->patient->id_pasien,
                'nama' => $this->patient->nama_lengkap,
                'nrm' => $this->patient->nrm,
            ],
            'petugas' => [
                'id' => $this->user->id,
                'nama' => $this->user->name,
            ],
            'catatan' => $this->catatan,
        ];
    }
}
