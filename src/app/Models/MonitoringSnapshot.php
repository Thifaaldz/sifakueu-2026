<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringSnapshot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'mahasiswa_id',
        'semester_id',
        'overall_status',
        'risk_score',
        'total_green',
        'total_yellow',
        'total_red',
        'summary_payload',
        'evaluated_at',
    ];

    protected $casts = [
        'summary_payload' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function indicatorResults(): HasMany
    {
        return $this->hasMany(MonitoringIndicatorResult::class, 'snapshot_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'monitoring_snapshot_id');
    }
}
