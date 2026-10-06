<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'jenis_surat_id',
        'requester_id',
        'number',
        'subject',
        'status',
        'payload',
        'attachments',
        'generated_file_path',
    ];

    protected $casts = [
        'payload' => 'array',
        'attachments' => 'array',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SuratApproval::class);
    }
}
