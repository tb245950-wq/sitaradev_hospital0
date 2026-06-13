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
            'nomor' => ($this->jenis_layanan === 'assessment' ? 'A' : 'T') . str_pad($this->nomor_antrian, 3, '0', STR_PAD_LEFT),
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
