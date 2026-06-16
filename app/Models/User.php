<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ✅ WAJIB untuk Sanctum

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // ✅ Tambahkan HasApiTokens

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $fillable = [
    'name',
    'email',
    'password',
    'role',
    'nip',
    'nik',
    'phone',
    'status',
    'last_login_at', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke Antrian
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'id_pengguna', 'id');
    }

    // Relasi ke Assessment Medis
    public function assessments(): HasMany
    {
        return $this->hasMany(MedicalAssessment::class, 'id_pengguna', 'id');
    }

    // Relasi ke Terapi (sebagai Terapis)
    public function therapiesAsTherapist(): HasMany
    {
        return $this->hasMany(Therapy::class, 'id_terapis', 'id');
    }

    // Relasi ke Monitoring (sebagai Terapis)
    public function monitoringsAsTherapist(): HasMany
    {
        return $this->hasMany(TherapyMonitoring::class, 'id_terapis', 'id');
    }

    // ✅ TAMBAHKAN INI - Relasi ke Laporan
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'id_pengguna', 'id');
    }
}