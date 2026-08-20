<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalAssessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected $dokter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dokter = User::factory()->dokter()->create();
    }

    public function test_assessment_index_and_show_keys_are_consistent()
    {
        $assessment = MedicalAssessment::factory()->create(['id_pengguna' => $this->dokter->id]);

        $indexResponse = $this->actingAs($this->dokter)->getJson('/api/assessments');
        $showResponse = $this->actingAs($this->dokter)->getJson("/api/assessments/{$assessment->id_assessment}");

        $indexData = $indexResponse->json('data.0');
        $showData = $showResponse->json('data');

        $this->assertNotNull($indexData, 'Index data (data.0) should not be null');
        $this->assertArrayHasKey('id', $showData, 'Show response should have "id" key');
        $this->assertArrayHasKey('id', $indexData, 'Index response should have "id" key');
        
        $this->assertEquals($showData['id'], $indexData['id']);
    }

    public function test_patient_index_and_show_keys_are_consistent()
    {
        $patient = Patient::factory()->create();

        $indexResponse = $this->actingAs($this->dokter)->getJson('/api/patients');
        $showResponse = $this->actingAs($this->dokter)->getJson("/api/patients/{$patient->id_pasien}");

        $indexData = $indexResponse->json('data.0');
        $showData = $showResponse->json('data');

        $this->assertNotNull($indexData, 'Index data (data.0) should not be null');
        $this->assertArrayHasKey('id', $showData, 'Show response should have "id" key');
        $this->assertArrayHasKey('id', $indexData, 'Index response should have "id" key');
    }
}
