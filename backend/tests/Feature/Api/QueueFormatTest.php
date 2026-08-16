<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueFormatTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_queue_number_format_is_correct()
    {
        $patient = Patient::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/queues', [
            'id_pasien' => $patient->id_pasien,
            'jenis_layanan' => 'assessment'
        ]);

        $response->assertStatus(201);
        $nomor = $response->json('data.nomor');

        // Based on controller comment: "A001 for assessment"
        // But current implementation returns integer 1, 2, 3...
        $this->assertStringStartsWith('A', (string)$nomor, 'Queue number for assessment should start with "A"');
    }
}
