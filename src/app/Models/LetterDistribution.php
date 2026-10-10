<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterDistribution extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'generated_letter_id', 'channel', 'recipient', 'status', 'sent_at', 'error_message'];

    protected $casts = ['sent_at' => 'datetime'];

    public function generatedLetter(): BelongsTo
    {
        return $this->belongsTo(GeneratedLetter::class);
    }
}
