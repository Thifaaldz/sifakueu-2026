<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterVerificationToken extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'generated_letter_id', 'public_token', 'active', 'expires_at'];

    protected $casts = ['active' => 'boolean', 'expires_at' => 'datetime'];

    public function generatedLetter(): BelongsTo
    {
        return $this->belongsTo(GeneratedLetter::class);
    }
}
