<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneratedLetter extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'surat_id', 'letter_number_id', 'letter_template_id', 'template_version', 'docx_file_id', 'pdf_file_id', 'html_path', 'checksum', 'generated_at', 'generated_by'];

    protected $casts = ['generated_at' => 'datetime'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function letterNumber(): BelongsTo
    {
        return $this->belongsTo(LetterNumber::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterTemplate::class, 'letter_template_id');
    }

    public function verificationTokens(): HasMany
    {
        return $this->hasMany(LetterVerificationToken::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(LetterDistribution::class);
    }
}
