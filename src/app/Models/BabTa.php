<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BabTa extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'dokumen_ta_id',
        'chapter_number',
        'title',
        'content',
        'version',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function dokumenTa(): BelongsTo
    {
        return $this->belongsTo(DokumenTa::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'approved_by');
    }
}
