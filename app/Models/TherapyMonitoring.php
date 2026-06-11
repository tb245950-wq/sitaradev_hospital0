<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapyMonitoring extends Model
{
    use HasFactory;

    protected $table = 'therapy_monitorings';
    protected $primaryKey = 'id_monitoring';

    public $incrementing = true;

    protected $fillable = [
        'tanggal_sesi',
        'waktu_mulai',
        'waktu_selesai',
        'kehadiran',
        'catatan_perkembangan',
        'kondisi_pasien',
        'rekomendasi',
        'progress_score',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_sesi' => 'date',
            'waktu_mulai' => 'time',
            'waktu_selesai' => 'time',
            'progress_score' => 'integer',
        ];
    }

    public function therapy(): BelongsTo
    {
        return $this->belongsTo(Therapy::class, 'id_terapi', 'id_terapi');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_terapis');
    }
}
