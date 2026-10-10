<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterArchive extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'generated_letter_id', 'archive_code', 'classification', 'retention_until', 'archived_at', 'archived_by'];

    protected $casts = ['retention_until' => 'date', 'archived_at' => 'datetime'];

    public function generatedLetter(): BelongsTo
    {
        return $this->belongsTo(GeneratedLetter::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
