<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertFollowup extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'alert_id',
        'actor_id',
        'action_type',
        'note',
        'next_action_date',
        'status',
        'payload',
    ];

    protected $casts = [
        'next_action_date' => 'date',
        'payload' => 'array',
    ];

    public function alert(): BelongsTo
    {
        return $this->belongsTo(Alert::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
