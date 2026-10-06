<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SidangSchedule extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'sidang_registration_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'ruangan_id',
        'meeting_url',
        'mode',
        'status',
        'created_by',
        'finalized_by',
        'started_at',
        'started_by',
        'completed_at',
        'completed_by',
        'conflict_payload',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'conflict_payload' => 'array',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(SidangScheduleHistory::class);
    }
}
