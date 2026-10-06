<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaDocumentVersion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'ta_document_id',
        'version_number',
        'stored_file_id',
        'submitted_by',
        'submitted_at',
        'change_summary',
        'status',
        'checksum',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(TaDocument::class, 'ta_document_id');
    }

    public function storedFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TaReview::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaComment::class);
    }
}
