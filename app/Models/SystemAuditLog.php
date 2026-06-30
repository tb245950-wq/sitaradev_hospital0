<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'action',
        'description',
        'ip_address',
        'old_values',
        'new_values',
        'affected_records',
        'status',
        'error_message',
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'affected_records' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
