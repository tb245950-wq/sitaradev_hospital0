<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    protected $fillable = ['kode', 'nama', 'deskripsi', 'status'];

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
