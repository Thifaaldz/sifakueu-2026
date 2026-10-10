<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringRule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'domain',
        'description',
        'source_module',
        'metric_key',
        'operator',
        'threshold_value',
        'warning_value',
        'severity',
        'priority',
        'active',
        'version',
        'metadata',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'warning_value' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    public function indicatorResults(): HasMany
    {
        return $this->hasMany(MonitoringIndicatorResult::class, 'rule_id');
    }
}
