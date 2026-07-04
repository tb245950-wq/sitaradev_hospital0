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
        'nik_hash',
        'nama_lengkap',
        'nama_panggilan',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telepon_wali',
        'nama_wali',
        'hubungan_wali',
        'riwayat_medis',
        'ktp_photo',
        'ktp_status',
        'ktp_rejected_reason',
        'ktp_verified_at',
        'profile_photo',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'  => 'date',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
            'ktp_verified_at'=> 'datetime',
            'nik'            => \App\Casts\EncryptedField::class,
            'alamat'         => \App\Casts\EncryptedField::class,
        ];
    }

    /**
     * Auto-generate NRM and nik_hash saat creating/updating
     */
    protected static function boot()
    {
        parent::boot();
        
        // CREATING: Generate NRM dan nik_hash
        static::creating(function ($patient) {
            // Auto-generate NRM jika tidak ada
            if (empty($patient->nrm)) {
                // Format: NRM-YYYYMMDD-XXXX
                $patient->nrm = 'NRM-' . date('Ymd') . '-' . 
                    str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            
            // Auto-generate nik_hash dari plaintext NIK
            // IMPORTANT: nik_hash harus dibuat SEBELUM nik di-encrypt oleh EncryptedField cast
            if (!empty($patient->nik) && empty($patient->nik_hash)) {
                $patient->nik_hash = hash('sha256', $patient->nik);
            }
        });
        
        // UPDATING: Update nik_hash jika NIK berubah
        static::updating(function ($patient) {
            // Cek apakah NIK berubah (isDirty check)
            if ($patient->isDirty('nik') && !empty($patient->nik)) {
                // Hash plaintext NIK yang baru
                $patient->nik_hash = hash('sha256', $patient->nik);
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
     * Format: ****************6172 (sembunyikan semua kecuali 4 angka terakhir)
     */
    public function getMaskedNikAttribute(): string
    {
        $nik = $this->nik;
        if (empty($nik)) {
            return '-';
        }
        $len   = strlen($nik);
        $last4 = substr($nik, -4);
        $stars = str_repeat('*', max($len - 4, 12));
        return $stars . $last4;
    }
}