<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterNumberSequence extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'jenis_surat_id', 'year', 'month', 'current_sequence', 'reset_policy'];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }
}
