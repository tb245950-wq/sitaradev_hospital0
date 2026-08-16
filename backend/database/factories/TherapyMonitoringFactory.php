<?php

namespace Database\Factories;

use App\Models\TherapyMonitoring;
use App\Models\Therapy;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TherapyMonitoringFactory extends Factory
{
    protected $model = TherapyMonitoring::class;

    public function definition(): array
    {
        return [
            'id_terapi' => Therapy::factory(),
            'id_pasien' => Patient::factory(),
            'id_terapis' => User::factory()->terapis(),
            'tanggal_sesi' => now(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:00',
            'kehadiran' => 'hadir',
            'catatan_perkembangan' => fake()->sentence(),
            'kondisi_pasien' => fake()->sentence(),
            'rekomendasi' => fake()->sentence(),
            'progress_score' => fake()->numberBetween(60, 100),
        ];
    }
}
