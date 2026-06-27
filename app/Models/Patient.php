<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'patients';
    protected $primaryKey = 'id_pasien';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
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
            'nik' => \App\Casts\EncryptedField::class,
            'alamat' => \App\Casts\EncryptedField::class,
        ];
    }

    /**
     * Auto-generate NRM saat creating
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($patient) {
            // Auto-generate NRM jika tidak ada
            if (empty($patient->nrm)) {
                // Format: NRM-YYYYMMDD-XXXX
                $patient->nrm = 'NRM-' . date('Ymd') . '-' . 
                    str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Queue
     */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'id_pasien', 'id_pasien');
    }

    /**
     * Relasi ke Assessment
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(MedicalAssessment::class, 'id_pasien', 'id_pasien');
    }

    /**
     * Relasi ke Therapy
     */
    public function therapies(): HasMany
    {
        return $this->hasMany(Therapy::class, 'id_pasien', 'id_pasien');
    }

    /**
     * Relasi ke Monitoring
     */
    public function monitorings(): HasMany
    {
        return $this->hasMany(TherapyMonitoring::class, 'id_pasien', 'id_pasien');
    }

    /**
     * Get the masked NIK attribute
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = $this->nik;
        if (empty($nik)) {
            return '-';
        }
        return substr($nik, 0, 4) . '***********';
    }
}