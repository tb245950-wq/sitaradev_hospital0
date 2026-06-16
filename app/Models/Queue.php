<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $table = 'queues';
    protected $primaryKey = 'id_antrian';

    protected $fillable = [
        'nomor_antrian',
        'queue_number',
        'id_pasien',
        'patient_id',
        'id_pengguna',
        'jenis_layanan',
        'type',
        'status',
        'prioritas',
        'priority',
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
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function user() // Menambahkan relasi user agar tidak crash
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
        return $query->where('priority', $priority);
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
