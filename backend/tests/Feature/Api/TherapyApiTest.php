<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalAssessment;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TherapyApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $dokter;
    protected $terapis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->dokter = User::factory()->dokter()->create();
        $this->terapis = User::factory()->terapis()->create();
    }

    public function test_can_list_therapies()
    {
        Therapy::factory()->count(3)->create();

        $response = $this->actingAs($this->dokter)
            ->getJson('/api/therapies');

        $response->assertStatus(200);
    }

    public function test_can_create_therapy()
    {
        $assessment = MedicalAssessment::factory()->create();
        $data = [
            'id_assessment' => $assessment->id_assessment,
            'id_pasien' => $assessment->id_pasien,
            'id_terapis' => $this->terapis->id,
            'nama_terapi' => 'Fisioterapi',
            'deskripsi' => 'Latihan fisik rutin',
            'durasi_hari' => 30,
            'frekuensi_per_minggu' => 2,
            'tanggal_mulai' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->dokter)
            ->postJson('/api/therapies', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('therapies', ['nama_terapi' => 'Fisioterapi']);
    }

    public function test_can_create_monitoring()
    {
        $therapy = Therapy::factory()->create();
        $data = [
            'id_terapi' => $therapy->id_terapi,
            'waktu_mulai' => '09:00',
            'waktu_selesai' => '10:00',
            'kehadiran' => 'hadir',
            'catatan_perkembangan' => 'Sangat baik',
            'kondisi_pasien' => 'Stabil',
            'progress_score' => 85,
        ];

        $response = $this->actingAs($this->terapis)
            ->postJson('/api/monitorings', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('therapy_monitorings', ['progress_score' => 85]);
    }

    public function test_can_get_progress_stats()
    {
        $patient = Patient::factory()->create();
        $therapy = Therapy::factory()->create(['id_pasien' => $patient->id_pasien]);
        TherapyMonitoring::factory()->create([
            'id_terapi' => $therapy->id_terapi,
            'id_pasien' => $patient->id_pasien,
            'progress_score' => 70
        ]);
        TherapyMonitoring::factory()->create([
            'id_terapi' => $therapy->id_terapi,
            'id_pasien' => $patient->id_pasien,
            'progress_score' => 90
        ]);

        $response = $this->actingAs($this->dokter)
            ->getJson("/api/patients/{$patient->id_pasien}/progress-stats");

        $response->assertStatus(200)
            ->assertJsonPath('data.rata_rata_skor', 80);
    }
}
