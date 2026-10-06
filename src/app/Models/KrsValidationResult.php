<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsValidationResult extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'krs_id',
        'krs_detail_id',
        'validation_code',
        'severity',
        'passed',
        'message',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class);
    }

    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class);
    }
}
