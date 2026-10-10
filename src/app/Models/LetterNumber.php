<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterNumber extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'jenis_surat_id', 'sequence_number', 'formatted_number', 'generated_at', 'generated_by'];

    protected $casts = ['generated_at' => 'datetime'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
