<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangMinute extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'document_file_id', 'generated_at', 'generated_by', 'status'];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function documentFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class, 'document_file_id');
    }
}
