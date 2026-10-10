<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringOverride extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'snapshot_id',
        'original_status',
        'override_status',
        'reason',
        'valid_until',
        'approved_by',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(MonitoringSnapshot::class, 'snapshot_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
