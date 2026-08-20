<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $patient = $this->patient;
        $user    = $this->user;

        // Use queue_number if available, otherwise generate from nomor_antrian + jenis_layanan
        $nomorFormatted = $this->queue_number ?? $this->generateNomor();

        return [
            'id'       => $this->id_antrian,
            'nomor'    => $nomorFormatted,
            'jenis'    => $this->jenis_layanan,
            'status'   => $this->status,
            'prioritas'=> $this->prioritas ?? 0,
            'waktu' => [
                'daftar'  => $this->waktu_daftar,
                'panggil' => $this->waktu_panggil,
                'selesai' => $this->waktu_selesai,
            ],
            'pasien' => $patient ? [
                'id'         => $patient->id_pasien,
                'nama'       => $patient->nama_lengkap,
                'nrm'        => $patient->nrm,
                // NIK penuh hanya dikirim saat pasien sedang dipanggil (sesi aktif)
                'nik'        => ($this->status === 'dipanggil') ? $patient->nik : null,
                'masked_nik' => $patient->masked_nik,
            ] : null,
            'petugas' => $user ? [
                'id'   => $user->id,
                'nama' => $user->name,
            ] : null,
            'catatan' => $this->catatan,
        ];
    }

    private function generateNomor(): string
    {
        $prefix = match($this->jenis_layanan) {
            'assessment' => 'A',
            'terapi' => 'T',
            'checkup' => 'C',
            'konsultasi' => 'K',
            default => 'Q',
        };
        return $prefix . str_pad($this->nomor_antrian, 3, '0', STR_PAD_LEFT);
    }
}
