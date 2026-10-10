<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterVerification extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'verifier_id', 'status', 'note', 'verified_at'];

    protected $casts = ['verified_at' => 'datetime'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }
}
