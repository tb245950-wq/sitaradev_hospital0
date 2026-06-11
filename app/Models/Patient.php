<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';
    protected $primaryKey = 'id_pasien';

    public $incrementing = true;

    protected $fillable = [
        'nrm',
        'nik',
        'nama_lengkap',
        'nama_panggilan',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon_wali',
        'nama_wali',
        'hubungan_wali',
        'riwayat_medis',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'id_pasien', 'id_pasien');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(MedicalAssessment::class, 'id_pasien', 'id_pasien');
    }

    public function therapies(): HasMany
    {
        return $this->hasMany(Therapy::class, 'id_pasien', 'id_pasien');
    }

    public function monitorings(): HasMany
    {
        return $this->hasMany(TherapyMonitoring::class, 'id_pasien', 'id_pasien');
    }
}
