<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringIndicatorResult extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'snapshot_id',
        'rule_id',
        'indicator',
        'metric_value',
        'status',
        'threshold_value',
        'explanation',
        'source_reference_type',
        'source_reference_id',
        'payload',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'threshold_value' => 'decimal:2',
        'payload' => 'array',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(MonitoringSnapshot::class, 'snapshot_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(MonitoringRule::class, 'rule_id');
    }
}
