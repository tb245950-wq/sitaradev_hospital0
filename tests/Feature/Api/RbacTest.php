<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
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

    public function test_admin_can_access_all_patient_routes()
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->admin)
            ->getJson('/api/patients')->assertStatus(200);
        
        $this->actingAs($this->admin)
            ->postJson('/api/patients', Patient::factory()->make()->toArray())->assertStatus(201);
        
        $this->actingAs($this->admin)
            ->putJson("/api/patients/{$patient->id_pasien}", ['nama_lengkap' => 'Updated Name'])->assertStatus(200);
        
        $this->actingAs($this->admin)
            ->deleteJson("/api/patients/{$patient->id_pasien}")->assertStatus(200);
    }

    public function test_dokter_can_create_patient_but_cannot_delete()
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->dokter)
            ->postJson('/api/patients', Patient::factory()->make()->toArray())->assertStatus(201);
        
        $this->actingAs($this->dokter)
            ->deleteJson("/api/patients/{$patient->id_pasien}")->assertStatus(403);
    }

    public function test_terapis_cannot_create_or_delete_patient()
    {
        $patient = Patient::factory()->create();

        $this->actingAs($this->terapis)
            ->postJson('/api/patients', Patient::factory()->make()->toArray())->assertStatus(403);
        
        $this->actingAs($this->terapis)
            ->deleteJson("/api/patients/{$patient->id_pasien}")->assertStatus(403);
    }

    public function test_only_dokter_can_create_assessment()
    {
        $patient = Patient::factory()->create();
        $assessmentData = [
            'id_pasien' => $patient->id_pasien,
            'keluhan_utama' => 'Sakit kepala',
            'hasil_pemeriksaan' => [
                'tensi' => '120/80',
                'nadi' => '80',
                'suhu' => '36.5'
            ],
            'diagnosis' => 'Migrain',
            'rencana_terapi' => 'Istirahat cukup',
            'catatan_medis' => 'Pasien tampak lemas',
        ];

        $this->actingAs($this->dokter)
            ->postJson('/api/assessments', $assessmentData)
            ->assertStatus(201);
        
        $this->actingAs($this->admin)
            ->postJson('/api/assessments', $assessmentData)->assertStatus(403);
        
        $this->actingAs($this->terapis)
            ->postJson('/api/assessments', $assessmentData)->assertStatus(403);
    }

    public function test_only_admin_can_delete_assessment()
    {
        $assessment = \App\Models\MedicalAssessment::factory()->create();
        
        $this->actingAs($this->dokter)
            ->deleteJson("/api/assessments/{$assessment->id_assessment}")->assertStatus(403);
        
        $this->actingAs($this->terapis)
            ->deleteJson("/api/assessments/{$assessment->id_assessment}")->assertStatus(403);

        $this->actingAs($this->admin)
            ->deleteJson("/api/assessments/{$assessment->id_assessment}")->assertStatus(200);
    }

    public function test_report_access_rights()
    {
        // Daily Report: Admin & Dokter
        $this->actingAs($this->admin)->getJson('/api/reports/daily')->assertStatus(200);
        $this->actingAs($this->dokter)->getJson('/api/reports/daily')->assertStatus(200);
        $this->actingAs($this->terapis)->getJson('/api/reports/daily')->assertStatus(403);

        // Dashboard: Admin, Dokter, Terapis
        $this->actingAs($this->admin)->getJson('/api/reports/dashboard')->assertStatus(200);
        $this->actingAs($this->dokter)->getJson('/api/reports/dashboard')->assertStatus(200);
        $this->actingAs($this->terapis)->getJson('/api/reports/dashboard')->assertStatus(200);
    }
}
