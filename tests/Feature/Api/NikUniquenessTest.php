<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NikUniquenessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations including nik_hash
        $this->artisan('migrate');
        
        $this->admin = User::factory()->admin()->create();
    }

    /**
     * Test: NIK uniqueness using nik_hash on patient creation (Staff endpoint)
     */
    public function test_nik_uniqueness_on_patient_creation_via_staff()
    {
        // Create first patient with NIK
        $firstPatient = Patient::create([
            'nrm' => 'NRM-TEST-0001',
            'nik' => '1234567890123456', // Plaintext NIK
            'nama_lengkap' => 'Patient One',
            'tanggal_lahir' => '2020-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Test No. 1',
            'no_telepon_wali' => '08123456789',
            'nama_wali' => 'Wali One',
            'hubungan_wali' => 'Ayah',
        ]);

        // Assert nik_hash was auto-generated
        $this->assertNotNull($firstPatient->nik_hash);
        $this->assertEquals(hash('sha256', '1234567890123456'), $firstPatient->nik_hash);

        // Attempt to create second patient with same NIK via API
        $response = $this->actingAs($this->admin)
            ->postJson('/api/patients', [
                'nrm' => 'NRM-TEST-0002',
                'nik' => '1234567890123456', // Duplicate NIK
                'nama_lengkap' => 'Patient Two',
                'tanggal_lahir' => '2020-02-01',
                'jenis_kelamin' => 'P',
                'alamat' => 'Jl. Test No. 2',
                'no_telepon_wali' => '08123456780',
                'nama_wali' => 'Wali Two',
                'hubungan_wali' => 'Ibu',
            ]);

        // Should be rejected with 422 validation error
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nik');
        $response->assertJson([
            'errors' => [
                'nik' => ['NIK sudah terdaftar dalam sistem.']
            ]
        ]);

        // Assert only 1 patient exists with that NIK
        $this->assertCount(1, Patient::where('nik_hash', hash('sha256', '1234567890123456'))->get());
    }

    /**
     * Test: NIK uniqueness on patient registration (Patient portal endpoint)
     */
    public function test_nik_uniqueness_on_patient_registration_via_portal()
    {
        // Create a user-patient pair with NIK
        $user1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role' => 'pasien',
            'status' => 'active'
        ]);

        $patient1 = Patient::create([
            'user_id' => $user1->id,
            'nrm' => 'NRM-TEST-0003',
            'nik' => '9876543210987654',
            'nama_lengkap' => 'John Doe',
            'tanggal_lahir' => '2019-05-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Patient Portal No. 1',
            'no_telepon_wali' => '08199999999',
            'nama_wali' => 'Jane Doe',
            'hubungan_wali' => 'Ibu',
        ]);

        // Attempt to register new patient with same NIK via /api/pasien/register
        $response = $this->postJson('/api/pasien/register', [
            'name' => 'Different Person',
            'email' => 'different@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nik' => '9876543210987654', // Duplicate NIK
            'phone' => '08111111111',
            'date_of_birth' => '2019-06-01',
            'gender' => 'male',
            'address' => 'Jl. Different No. 1',
            'parent_name' => 'Parent Name',
            'parent_phone' => '08122222222'
        ]);

        // Should be rejected
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('nik');
        $this->assertStringContainsString('NIK sudah terdaftar', $response->json('errors.nik.0'));
    }

    /**
     * Test: NIK uniqueness on patient update (exclude current patient)
     */
    public function test_nik_uniqueness_on_patient_update_excludes_self()
    {
        // Create two patients with different NIKs
        $patient1 = Patient::create([
            'nrm' => 'NRM-TEST-0004',
            'nik' => '1111111111111111',
            'nama_lengkap' => 'Patient Alpha',
            'tanggal_lahir' => '2020-03-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Alpha',
            'no_telepon_wali' => '08133333333',
            'nama_wali' => 'Wali Alpha',
            'hubungan_wali' => 'Ayah',
        ]);

        $patient2 = Patient::create([
            'nrm' => 'NRM-TEST-0005',
            'nik' => '2222222222222222',
            'nama_lengkap' => 'Patient Beta',
            'tanggal_lahir' => '2020-04-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Beta',
            'no_telepon_wali' => '08144444444',
            'nama_wali' => 'Wali Beta',
            'hubungan_wali' => 'Ibu',
        ]);

        // Update patient1 with its own NIK (should succeed)
        $response1 = $this->actingAs($this->admin)
            ->putJson("/api/patients/{$patient1->id_pasien}", [
                'nik' => '1111111111111111', // Same NIK
                'alamat' => 'Jl. Alpha Updated',
            ]);

        $response1->assertStatus(200);

        // Attempt to update patient2 with patient1's NIK (should fail)
        $response2 = $this->actingAs($this->admin)
            ->putJson("/api/patients/{$patient2->id_pasien}", [
                'nik' => '1111111111111111', // Duplicate of patient1
                'alamat' => 'Jl. Beta Updated',
            ]);

        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors('nik');
    }

    /**
     * Test: nik_hash is auto-generated when creating patient
     */
    public function test_nik_hash_auto_generated_on_create()
    {
        $nikValue = '5555555555555555';

        $patient = Patient::create([
            'nrm' => 'NRM-TEST-0006',
            'nik' => $nikValue,
            'nama_lengkap' => 'Test Auto Hash',
            'tanggal_lahir' => '2021-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Auto Hash',
            'no_telepon_wali' => '08155555555',
            'nama_wali' => 'Wali Hash',
            'hubungan_wali' => 'Ayah',
        ]);

        $expectedHash = hash('sha256', $nikValue);

        $this->assertEquals($expectedHash, $patient->nik_hash);
        $this->assertDatabaseHas('patients', [
            'id_pasien' => $patient->id_pasien,
            'nik_hash' => $expectedHash,
        ]);
    }

    /**
     * Test: nik_hash is updated when NIK is changed
     */
    public function test_nik_hash_updated_on_nik_change()
    {
        $patient = Patient::create([
            'nrm' => 'NRM-TEST-0007',
            'nik' => '6666666666666666',
            'nama_lengkap' => 'Test Update Hash',
            'tanggal_lahir' => '2021-02-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Update Hash',
            'no_telepon_wali' => '08166666666',
            'nama_wali' => 'Wali Update',
            'hubungan_wali' => 'Ibu',
        ]);

        $oldHash = $patient->nik_hash;

        // Update NIK to a new value
        $newNik = '7777777777777777';
        $patient->update(['nik' => $newNik]);

        $patient->refresh();
        $expectedNewHash = hash('sha256', $newNik);

        $this->assertNotEquals($oldHash, $patient->nik_hash);
        $this->assertEquals($expectedNewHash, $patient->nik_hash);
    }

    /**
     * Test: Different NIKs can be created successfully
     */
    public function test_different_niks_can_be_created()
    {
        $patient1 = Patient::create([
            'nrm' => 'NRM-TEST-0008',
            'nik' => '8888888888888888',
            'nama_lengkap' => 'Patient Eight',
            'tanggal_lahir' => '2021-03-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Eight',
            'no_telepon_wali' => '08177777777',
            'nama_wali' => 'Wali Eight',
            'hubungan_wali' => 'Ayah',
        ]);

        $patient2 = Patient::create([
            'nrm' => 'NRM-TEST-0009',
            'nik' => '9999999999999999', // Different NIK
            'nama_lengkap' => 'Patient Nine',
            'tanggal_lahir' => '2021-04-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Nine',
            'no_telepon_wali' => '08188888888',
            'nama_wali' => 'Wali Nine',
            'hubungan_wali' => 'Ibu',
        ]);

        $this->assertNotEquals($patient1->nik_hash, $patient2->nik_hash);
        $this->assertCount(2, Patient::whereIn('nik_hash', [$patient1->nik_hash, $patient2->nik_hash])->get());
    }
}
