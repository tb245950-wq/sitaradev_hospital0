<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EncryptedField implements CastsAttributes
{
    /**
     * Cast the given value (Decrypt saat diakses).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // Jika gagal decrypt (mungkin data lama belum terenkripsi), kembalikan nilai asli
            return $value;
        }
    }

    /**
     * Prepare the given value for storage (Encrypt saat disimpan).
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (empty($value)) {
            return $value;
        }

        return Crypt::encryptString($value);
    }
}
