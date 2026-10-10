<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterFormField extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'jenis_surat_id', 'field_key', 'label', 'field_type', 'required', 'validation_rule', 'options_json', 'sequence', 'active'];

    protected $casts = ['required' => 'boolean', 'options_json' => 'array', 'active' => 'boolean'];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }
}
