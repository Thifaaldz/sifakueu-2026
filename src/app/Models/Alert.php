<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'monitoring_snapshot_id',
        'rule_id',
        'indicator_result_id',
        'type',
        'alert_type',
        'severity',
        'risk_status',
        'status',
        'title',
        'description',
        'escalated_at',
        'assigned_to',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolved_by',
        'closed_at',
        'closed_by',
        'due_at',
        'payload',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'due_at' => 'datetime',
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

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(MonitoringSnapshot::class, 'monitoring_snapshot_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(MonitoringRule::class, 'rule_id');
    }

    public function indicatorResult(): BelongsTo
    {
        return $this->belongsTo(MonitoringIndicatorResult::class, 'indicator_result_id');
    }

    public function acknowledger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function followups(): HasMany
    {
        return $this->hasMany(AlertFollowup::class);
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(AlertEscalation::class);
    }
}
