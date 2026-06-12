<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalAssessment extends Model
{
    use HasFactory;

    protected $table = 'medical_assessments';
    protected $primaryKey = 'id_assessment';

    public function getRouteKeyName()
    {
        return 'id_assessment';
    }

    /**
     * Kolom yang boleh diisi (MASS ASSIGNMENT)
     * WAJIB termasuk foreign keys!
     */
    protected $fillable = [
        'id_pasien',           // ← WAJIB
        'id_pengguna',         // ← WAJIB (dokter)
        'id_antrian',          // ← WAJIB
        'tanggal_assessment',
        'keluhan_utama',
        'riwayat_penyakit',
        'hasil_pemeriksaan',
        'diagnosis',
        'rencana_terapi',
        'obat_diresepkan',
        'catatan_tambahan',
        'status',
    ];

    protected $casts = [
        'tanggal_assessment' => 'date',
        'hasil_pemeriksaan' => 'array',   // JSON
        'obat_diresepkan' => 'array',      // JSON
    ];

    // Relasi ke Patient
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    // Relasi ke User (Dokter)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id');
    }

    // Relasi ke Queue
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'id_antrian', 'id_antrian');
    }

    // Relasi ke Therapy
    public function therapies(): HasMany
    {
        return $this->hasMany(Therapy::class, 'id_assessment', 'id_assessment');
    }
}