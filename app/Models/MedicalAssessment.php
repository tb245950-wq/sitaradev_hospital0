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

    public $incrementing = true;

    protected $fillable = [
        'keluhan_utama',
        'diagnosis',
        'catatan_medis',
        'hasil_pemeriksaan',
        'rencana_terapi',
        'status',
        'tanggal_assessment',
    ];

    protected function casts(): array
    {
        return [
            'hasil_pemeriksaan' => 'json',
            'tanggal_assessment' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'id_antrian', 'id_antrian');
    }

    public function therapies(): HasMany
    {
        return $this->hasMany(Therapy::class, 'id_assessment', 'id_assessment');
    }
}
