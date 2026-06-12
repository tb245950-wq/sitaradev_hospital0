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
     * Kolom yang boleh diisi (MASS ASSIGNMENT)
     * Foreign keys TIDAK perlu di-fillable jika kita set via relasi
     * TAPI untuk simplicity, kita masukkan saja
     */
    protected $fillable = [
        'id_pasien',      // ← WAJIB ADA
        'id_pengguna',    // ← WAJIB ADA
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

    // Relasi ke Patient
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id');
    }

    // Relasi ke MedicalAssessment
    public function assessments(): HasMany
    {
        return $this->hasMany(MedicalAssessment::class, 'id_antrian', 'id_antrian');
    }
}