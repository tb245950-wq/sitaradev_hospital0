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

    public function getRouteKeyName()
    {
        return 'id_monitoring';
    }

    protected $fillable = [
        'id_terapi',
        'id_pasien',
        'id_pengguna',
        'tanggal_monitoring',
        'sesi_ke',
        'progress',
        'catatan_terapis',
        'skor_perkembangan',
        'kendala',
        'rekomendasi',
    ];

    protected $casts = [
        'tanggal_monitoring' => 'date',
        'skor_perkembangan' => 'integer',
    ];

    // Relasi ke Therapy
    public function therapy(): BelongsTo
    {
        return $this->belongsTo(Therapy::class, 'id_terapi', 'id_terapi');
    }

    // Relasi ke Patient
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    // Relasi ke User (Terapis)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id');
    }
}