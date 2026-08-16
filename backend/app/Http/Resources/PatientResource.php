<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_pasien,
            'nrm' => $this->nrm,
            'nik' => $this->nik,
            'masked_nik' => $this->masked_nik,
            'nama' => $this->nama_lengkap,
            'nama_panggilan' => $this->nama_panggilan,
            'info_lahir' => [
                'tanggal' => $this->tanggal_lahir->format('Y-m-d'),
                'usia' => $this->tanggal_lahir->age,
            ],
            'jenis_kelamin' => $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            'alamat' => $this->alamat,
            'wali' => [
                'nama' => $this->nama_wali,
                'hubungan' => $this->hubungan_wali,
                'kontak' => $this->no_telepon_wali,
            ],
            'riwayat_medis' => $this->riwayat_medis,
            'statistik' => [
                'total_assessment' => $this->whenLoaded('assessments', function() { return $this->assessments->count(); }),
                'total_terapi' => $this->whenLoaded('therapies', function() { return $this->therapies->count(); }),
            ],
            'created_at' => $this->created_at,
        ];
    }
}
