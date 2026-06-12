<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonitoringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['dokter', 'terapis']);
    }

    public function rules(): array
    {
        return [
            'id_terapi' => 'required|exists:therapies,id_terapi',
            'tanggal_sesi' => 'sometimes|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'kehadiran' => 'required|in:hadir,tidak_hadir,izin,sakit', // Note: using underscore for consistency with DB enum
            'catatan_perkembangan' => 'required|string',
            'kondisi_pasien' => 'required|string',
            'rekomendasi' => 'nullable|string',
            'progress_score' => 'required|integer|min:0|max:100',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('kehadiran')) {
            $this->merge([
                'kehadiran' => str_replace(' ', '_', strtolower($this->kehadiran)),
            ]);
        }
    }
}
