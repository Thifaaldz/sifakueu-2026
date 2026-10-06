<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'title',
        'body',
        'available_channels',
        'status',
    ];

    protected $casts = [
        'available_channels' => 'array',
    ];
}
