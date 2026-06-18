<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_pasien' => $this->id_pasien,
            'nrm' => $this->nrm,
            'nama_lengkap' => $this->nama_lengkap,
            'nama_panggilan' => $this->nama_panggilan,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'no_telepon_wali' => $this->no_telepon_wali,
            'nama_wali' => $this->nama_wali,
            'hubungan_wali' => $this->hubungan_wali,
            'masked_nik' => $this->masked_nik,
            'nik' => $this->nik, // Full encrypted NIK
        ];
    }
}
