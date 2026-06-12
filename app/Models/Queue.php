<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queue extends Model
{
    use HasFactory;

    protected $table = 'queues';
    protected $primaryKey = 'id_antrian';

    /**
     * Dapatkan nama key route untuk model binding
     * Ini memberitahu Laravel untuk menggunakan 'id_antrian' bukan 'id'
     */
    public function getRouteKeyName()
    {
        return 'id_antrian';
    }

    protected $fillable = [
        'id_pasien',
        'id_pengguna',
        'nomor_antrian',
        'jenis_layanan',
        'status',
        'prioritas',
        'waktu_daftar',
        'waktu_panggil',
        'waktu_selesai',
        'catatan',
    ];

    protected $casts = [
        'waktu_daftar' => 'datetime',
        'waktu_panggil' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // Relasi
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(MedicalAssessment::class, 'id_antrian', 'id_antrian');
    }
}