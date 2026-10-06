<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SidangFile extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sidang_registration_id', 'sidang_requirement_id', 'stored_file_id', 'file_type', 'status', 'uploaded_by'];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(SidangRegistration::class, 'sidang_registration_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(SidangRequirement::class, 'sidang_requirement_id');
    }

    public function storedFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class);
    }
}
