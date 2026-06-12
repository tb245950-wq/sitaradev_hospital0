<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientApiTest extends TestCase
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

    public function test_can_list_patients_with_pagination()
    {
        Patient::factory()->count(20)->create();

        $response = $this->actingAs($this->dokter)
            ->getJson('/api/patients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id_pasien', 'nrm', 'nik', 'nama_lengkap']
                    ],
                    'current_page',
                    'last_page',
                    'total'
                ]
            ]);
        
        $this->assertEquals(15, count($response->json('data.data')));
    }

    public function test_can_search_patients()
    {
        Patient::factory()->create(['nama_lengkap' => 'Unique Patient Name']);
        Patient::factory()->create(['nrm' => 'NRM-99999']);

        $response = $this->actingAs($this->dokter)
            ->getJson('/api/patients?search=Unique');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));

        $response = $this->actingAs($this->dokter)
            ->getJson('/api/patients?search=99999');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
    }

    public function test_can_create_patient()
    {
        $data = [
            'nrm' => 'NRM-12345',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'New Patient',
            'tanggal_lahir' => '2010-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_telepon_wali' => '08123456789',
            'nama_wali' => 'Wali Name',
            'hubungan_wali' => 'Ayah',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/patients', $data);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama', 'New Patient');

        $this->assertDatabaseHas('patients', ['nrm' => 'NRM-12345']);
    }

    public function test_cannot_create_patient_with_duplicate_nrm()
    {
        Patient::factory()->create(['nrm' => 'NRM-DUPE']);

        $data = Patient::factory()->make(['nrm' => 'NRM-DUPE'])->toArray();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/patients', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nrm']);
    }

    public function test_can_show_patient_detail()
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->dokter)
            ->getJson("/api/patients/{$patient->id_pasien}");

        $response->assertStatus(200)
            ->assertJsonPath('data.nama', $patient->nama_lengkap);
    }

    public function test_can_update_patient()
    {
        $patient = Patient::factory()->create(['nama_lengkap' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/patients/{$patient->id_pasien}", [
                'nama_lengkap' => 'New Name'
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.nama', 'New Name');

        $this->assertDatabaseHas('patients', [
            'id_pasien' => $patient->id_pasien,
            'nama_lengkap' => 'New Name'
        ]);
    }

    public function test_can_delete_patient_soft_delete()
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/patients/{$patient->id_pasien}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('patients', ['id_pasien' => $patient->id_pasien]);
    }
}
