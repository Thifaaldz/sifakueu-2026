<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenLokasi extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'latitude', 'longitude', 'accuracy', 'presence_status', 'recorded_at'];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
