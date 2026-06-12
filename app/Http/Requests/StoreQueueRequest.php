<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by middleware
    }

    public function rules(): array
    {
        return [
            'id_pasien' => 'required|exists:patients,id_pasien',
            'jenis_layanan' => 'required|in:assessment,terapi',
            'prioritas' => 'sometimes|integer|min:0|max:10',
            'catatan' => 'nullable|string',
        ];
    }
}
