<?php

namespace Database\Factories;

use App\Models\MedicalAssessment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalAssessmentFactory extends Factory
{
    protected $model = MedicalAssessment::class;

    public function definition(): array
    {
        return [
            'id_pasien' => Patient::factory(),
            'id_pengguna' => User::factory()->dokter(),
            'tanggal_assessment' => now(),
            'keluhan_utama' => fake()->sentence(),
            'riwayat_penyakit' => fake()->sentence(),
            'hasil_pemeriksaan' => [
                'tensi' => '120/80',
                'nadi' => '80',
                'suhu' => '36.5',
                'berat_badan' => 15,
                'tinggi_badan' => 100,
            ],
            'diagnosis' => fake()->word(),
            'rencana_terapi' => fake()->sentence(),
            'obat_diresepkan' => ['Paracetamol', 'Vitamin C'],
            'catatan_tambahan' => fake()->sentence(),
            'catatan_medis' => fake()->paragraph(),
            'status' => 'final',
        ];
    }
}
