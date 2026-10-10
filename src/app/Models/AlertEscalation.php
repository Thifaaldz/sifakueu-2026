<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertEscalation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'alert_id',
        'from_role',
        'to_role',
        'reason',
        'escalated_by',
        'escalated_at',
    ];

    protected $casts = [
        'escalated_at' => 'datetime',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function escalator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_by');
    }
}
