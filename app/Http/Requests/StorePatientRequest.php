<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Role check handled by middleware, but extra safety here
        return in_array($this->user()->role, ['admin', 'dokter']);
    }

    public function rules(): array
    {
        return [
            'nrm' => 'required|string|max:50|unique:patients,nrm',
            'nik' => 'required|numeric|digits_between:16,20|unique:patients,nik',
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_telepon_wali' => 'required|string|max:20',
            'nama_wali' => 'required|string|max:255',
            'hubungan_wali' => 'required|string|max:50',
            'riwayat_medis' => 'nullable|string',
        ];
    }
}
