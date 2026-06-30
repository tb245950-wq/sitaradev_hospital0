<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id_assessment,
            'tanggal'       => $this->tanggal_assessment?->format('Y-m-d'),
            'keluhan'       => $this->keluhan_utama,
            'riwayat'       => $this->riwayat_penyakit,
            'diagnosis'     => $this->diagnosis,
            'rencana_terapi'=> $this->rencana_terapi,
            'hasil_fisik'   => $this->hasil_pemeriksaan,
            'catatan'       => $this->catatan_tambahan ?? $this->catatan_medis,
            'status'        => $this->status ?? 'draft',
            'pasien' => $this->patient ? [
                'id'   => $this->patient->id_pasien,
                'nama' => $this->patient->nama_lengkap,
                'nrm'  => $this->patient->nrm,
            ] : null,
            'dokter' => $this->user ? [
                'id'   => $this->user->id,
                'nama' => $this->user->name,
            ] : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
