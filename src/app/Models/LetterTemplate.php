<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'jenis_surat_id', 'code', 'name', 'template_format', 'content', 'file_template_id', 'version', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }
}
