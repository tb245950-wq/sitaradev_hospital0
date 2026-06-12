<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'dokter';
    }

    public function rules(): array
    {
        return [
            'id_pasien' => 'required|exists:patients,id_pasien',
            'id_antrian' => 'sometimes|exists:queues,id_antrian',
            'tanggal_assessment' => 'sometimes|date',
            'keluhan_utama' => 'required|string',
            'riwayat_penyakit' => 'nullable|string',
            'hasil_pemeriksaan' => 'required|array',
            'hasil_pemeriksaan.tensi' => 'required|string',
            'hasil_pemeriksaan.nadi' => 'required|string',
            'hasil_pemeriksaan.suhu' => 'required|string',
            'hasil_pemeriksaan.berat_badan' => 'sometimes|numeric',
            'hasil_pemeriksaan.tinggi_badan' => 'sometimes|numeric',
            'diagnosis' => 'required|string',
            'rencana_terapi' => 'nullable|string',
            'obat_diresepkan' => 'nullable|array',
            'catatan_tambahan' => 'nullable|string',
            'catatan_medis' => 'nullable|string',
        ];
    }
}
