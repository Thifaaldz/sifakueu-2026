<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterAttachment extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'stored_file_id', 'attachment_type', 'description', 'uploaded_by'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function storedFile(): BelongsTo
    {
        return $this->belongsTo(StoredFile::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
