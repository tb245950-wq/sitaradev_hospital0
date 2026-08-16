<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalAssessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $dokter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->dokter = User::factory()->dokter()->create();
    }

    public function test_can_list_assessments()
    {
        MedicalAssessment::factory()->count(5)->create();

        $response = $this->actingAs($this->dokter)
            ->getJson('/api/assessments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'keluhan', 'diagnosis']
                ]
            ]);
    }

    public function test_can_create_assessment()
    {
        $patient = Patient::factory()->create();
        $data = [
            'id_pasien' => $patient->id_pasien,
            'keluhan_utama' => 'Sakit perut',
            'hasil_pemeriksaan' => [
                'tensi' => '110/70',
                'nadi' => '75',
                'suhu' => '37.0'
            ],
            'diagnosis' => 'Gastritis',
            'rencana_terapi' => 'Diet lunak',
            'catatan_medis' => 'Pasien mual',
        ];

        $response = $this->actingAs($this->dokter)
            ->postJson('/api/assessments', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.diagnosis', 'Gastritis');

        $this->assertDatabaseHas('medical_assessments', ['diagnosis' => 'Gastritis']);
    }

    public function test_can_show_assessment_detail()
    {
        $assessment = MedicalAssessment::factory()->create();

        $response = $this->actingAs($this->dokter)
            ->getJson("/api/assessments/{$assessment->id_assessment}");

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnosis', $assessment->diagnosis);
    }

    public function test_can_update_assessment()
    {
        $assessment = MedicalAssessment::factory()->create(['id_pengguna' => $this->dokter->id]);

        $response = $this->actingAs($this->dokter)
            ->putJson("/api/assessments/{$assessment->id_assessment}", [
                'diagnosis' => 'Updated Diagnosis'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnosis', 'Updated Diagnosis');

        $this->assertDatabaseHas('medical_assessments', [
            'id_assessment' => $assessment->id_assessment,
            'diagnosis' => 'Updated Diagnosis'
        ]);
    }

    public function test_can_get_latest_assessment_by_patient()
    {
        $patient = Patient::factory()->create();
        MedicalAssessment::factory()->create([
            'id_pasien' => $patient->id_pasien,
            'tanggal_assessment' => now()->subDay(),
            'diagnosis' => 'Old'
        ]);
        MedicalAssessment::factory()->create([
            'id_pasien' => $patient->id_pasien,
            'tanggal_assessment' => now(),
            'diagnosis' => 'New'
        ]);

        $response = $this->actingAs($this->dokter)
            ->getJson("/api/patients/{$patient->id_pasien}/latest-assessment");

        $response->assertStatus(200)
            ->assertJsonPath('data.diagnosis', 'New');
    }

    public function test_can_delete_assessment_by_admin()
    {
        $assessment = MedicalAssessment::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/assessments/{$assessment->id_assessment}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('medical_assessments', ['id_assessment' => $assessment->id_assessment]);
    }
}
