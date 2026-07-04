<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $table = 'queues';
    protected $primaryKey = 'id_antrian';

    public function getRouteKeyName(): string
    {
        return 'id_antrian';
    }

    protected $fillable = [
        // Standard fields (Indonesian naming convention)
        'nomor_antrian',
        'id_pasien',
        'id_pengguna',
        'jenis_layanan',
        'status',
        'prioritas',
        'poli',
        'doctor_id',
        'booked_by',
        'catatan',
        'waktu_daftar',
        'waktu_panggil',
        'waktu_selesai',
    ];

    protected $casts = [
        'waktu_daftar' => 'datetime',
        'waktu_panggil' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    // RELASI
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'id_pasien', 'id_pasien');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function calledBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    // SCOPES
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['waiting', 'calling']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('prioritas', $priority); // FIXED: use 'prioritas' not 'priority'
    }

    // HELPER
    public function isWaiting()
    {
        return $this->status === 'waiting';
    }

    public function isCalling()
    {
        return $this->status === 'calling';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
