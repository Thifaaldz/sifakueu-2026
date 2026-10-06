<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'type',
        'severity',
        'status',
        'title',
        'description',
        'escalated_at',
        'assigned_to',
        'payload',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'payload' => 'array',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
