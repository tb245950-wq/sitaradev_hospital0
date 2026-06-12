<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Therapy extends Model
{
    use HasFactory;

    protected $table = 'therapies';
    protected $primaryKey = 'id_terapi';

    public function getRouteKeyName()
    {
        return 'id_terapi';
    }

    protected $fillable = [
        'id_pasien',
        'id_assessment',
        'id_terapis',  // ← Sesuai database
        'nama_terapi',  // ← Sesuai database
        'deskripsi',
        'dosis',
        'durasi_hari',  // ← Sesuai database
        'frekuensi_per_minggu',  // ← Sesuai database
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Relasi ke Patient
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    // Relasi ke MedicalAssessment
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(MedicalAssessment::class, 'id_assessment', 'id_assessment');
    }

    // Relasi ke User (Terapis)
    public function terapis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_terapis', 'id');
    }

    // Relasi ke Monitoring
    public function monitorings(): HasMany
    {
        return $this->hasMany(TherapyMonitoring::class, 'id_terapi', 'id_terapi');
    }
}