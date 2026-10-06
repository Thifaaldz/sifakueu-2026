<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangScheduleHistory extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    protected $fillable = ['tenant_id', 'sidang_schedule_id', 'old_date', 'old_start', 'old_end', 'old_room_id', 'new_date', 'new_start', 'new_end', 'new_room_id', 'reason', 'changed_by', 'created_at'];

    protected $casts = [
        'old_date' => 'date',
        'new_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(SidangSchedule::class, 'sidang_schedule_id');
    }
}
