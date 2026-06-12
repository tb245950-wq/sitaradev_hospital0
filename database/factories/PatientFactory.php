<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'nrm' => 'NRM-' . fake()->unique()->numerify('#####'),
            'nik' => fake()->unique()->numerify('################'),
            'nama_lengkap' => fake()->name(),
            'nama_panggilan' => fake()->firstName(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-5 years'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'alamat' => fake()->address(),
            'no_telepon_wali' => fake()->phoneNumber(),
            'nama_wali' => fake()->name(),
            'hubungan_wali' => fake()->randomElement(['Ayah', 'Ibu', 'Wali']),
            'riwayat_medis' => fake()->sentence(),
        ];
    }
}
