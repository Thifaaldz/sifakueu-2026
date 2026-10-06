<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenPendidikan extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'degree', 'institution', 'study_program', 'graduation_year', 'field', 'document_path'];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
