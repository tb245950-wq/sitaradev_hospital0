<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTherapyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'dokter']);
    }

    public function rules(): array
    {
        return [
            'id_pasien' => 'required|exists:patients,id_pasien',
            'id_assessment' => 'sometimes|exists:medical_assessments,id_assessment',
            'nama_terapi' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'dosis' => 'sometimes|string|max:255',
            'durasi_hari' => 'required|integer|min:1',
            'frekuensi_per_minggu' => 'required|integer|min:1|max:7',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
        ];
    }
}
