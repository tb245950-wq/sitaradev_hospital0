<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_assessment,
            'tanggal' => $this->tanggal_assessment->format('Y-m-d'),
            'keluhan' => $this->keluhan_utama,
            'diagnosis' => $this->diagnosis,
            'hasil_fisik' => $this->hasil_pemeriksaan,
            'pasien' => [
                'id' => $this->patient->id_pasien,
                'nama' => $this->patient->nama_lengkap,
                'nrm' => $this->patient->nrm,
            ],
            'dokter' => [
                'id' => $this->user->id,
                'nama' => $this->user->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
