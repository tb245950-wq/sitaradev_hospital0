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

    public $incrementing = true;

    protected $fillable = [
        'nama_terapi',
        'deskripsi',
        'dosis',
        'durasi_hari',
        'frekuensi_per_minggu',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(MedicalAssessment::class, 'id_assessment', 'id_assessment');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_terapis');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(TherapyMonitoring::class, 'id_terapi', 'id_terapi');
    }
}
