<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ScheduledTaskLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'task_name',
        'started_at',
        'finished_at',
        'status',
        'message',
        'context',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'context' => 'array',
    ];
}
