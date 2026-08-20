<?php

namespace Database\Factories;

use App\Models\Therapy;
use App\Models\MedicalAssessment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TherapyFactory extends Factory
{
    protected $model = Therapy::class;

    public function definition(): array
    {
        return [
            'id_pasien' => Patient::factory(),
            'id_assessment' => MedicalAssessment::factory(),
            'id_terapis' => User::factory()->terapis(),
            'nama_terapi' => fake()->randomElement(['Terapi Wicara', 'Okupasi Terapi', 'Fisioterapi']),
            'deskripsi' => fake()->sentence(),
            'dosis' => '1x sehari',
            'durasi_hari' => 30,
            'frekuensi_per_minggu' => 2,
            'status' => 'berjalan',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(30),
        ];
    }
}
