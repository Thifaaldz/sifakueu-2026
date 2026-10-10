<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequestValue extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'field_id', 'value_text', 'value_json'];

    protected $casts = ['value_json' => 'array'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(LetterFormField::class, 'field_id');
    }
}
