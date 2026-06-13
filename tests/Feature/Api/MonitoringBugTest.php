<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Therapy;
use App\Models\TherapyMonitoring;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringBugTest extends TestCase
{
    use RefreshDatabase;

    protected $terapis;

    protected function setUp(): void
    {
        parent::setUp();
        $this->terapis = User::factory()->terapis()->create();
    }

    public function test_monitoring_update_with_space_in_kehadiran_fails_database()
    {
        $therapy = Therapy::factory()->create();
        $monitoring = TherapyMonitoring::factory()->create([
            'id_terapi' => $therapy->id_terapi,
            'id_pasien' => $therapy->id_pasien,
            'id_terapis' => $this->terapis->id,
            'kehadiran' => 'hadir'
        ]);

        // "tidak hadir" with space
        $response = $this->actingAs($this->terapis)->putJson("/api/monitorings/{$monitoring->id_monitoring}", [
            'kehadiran' => 'tidak hadir'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('therapy_monitorings', [
            'id_monitoring' => $monitoring->id_monitoring,
            'kehadiran' => 'tidak_hadir' // Should be underscored
        ]);
    }
}
