<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_patient_validation_rules()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/patients', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nrm', 'nik', 'nama_lengkap', 'tanggal_lahir', 'jenis_kelamin', 'alamat']);
        
        $response = $this->actingAs($this->admin)
            ->postJson('/api/patients', [
                'nrm' => 'NRM-1',
                'nik' => 'not-numeric',
                'nama_lengkap' => 'Name',
                'tanggal_lahir' => 'not-a-date',
                'jenis_kelamin' => 'X',
                'alamat' => 'Address',
                'no_telepon_wali' => '081',
                'nama_wali' => 'Wali',
                'hubungan_wali' => 'Hubungan'
            ]);
        
        $response->assertJsonValidationErrors(['nik', 'tanggal_lahir', 'jenis_kelamin']);
    }

    public function test_assessment_validation_rules()
    {
        $dokter = User::factory()->dokter()->create();
        
        $response = $this->actingAs($dokter)
            ->postJson('/api/assessments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['id_pasien', 'keluhan_utama', 'hasil_pemeriksaan', 'diagnosis']);
        
        $response = $this->actingAs($dokter)
            ->postJson('/api/assessments', [
                'id_pasien' => 99999, // Non-existent
                'hasil_pemeriksaan' => 'not-an-array'
            ]);
        
        $response->assertJsonValidationErrors(['id_pasien', 'hasil_pemeriksaan']);
    }

    public function test_monitoring_validation_rules()
    {
        $terapis = User::factory()->terapis()->create();
        
        $response = $this->actingAs($terapis)
            ->postJson('/api/monitorings', [
                'waktu_mulai' => '10:00',
                'waktu_selesai' => '09:00', // Selesai before mulai
                'progress_score' => 150, // Out of range
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['waktu_selesai', 'progress_score']);
    }
}
