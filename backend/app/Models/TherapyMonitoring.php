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
        'id_terapis',  // ← Sesuai database
        'tanggal_sesi',  // ← Sesuai database
        'waktu_mulai',
        'waktu_selesai',
        'kehadiran',
        'catatan_perkembangan',  // ← Sesuai database
        'kondisi_pasien',
        'rekomendasi',
        'progress_score',  // ← Sesuai database
    ];

    protected $casts = [
        'tanggal_sesi' => 'date',
        'waktu_mulai' => 'datetime:H:i',
        'waktu_selesai' => 'datetime:H:i',
        'progress_score' => 'integer',
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
    public function terapis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_terapis', 'id');
    }
}