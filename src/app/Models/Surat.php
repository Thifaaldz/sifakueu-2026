<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surat extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'jenis_surat_id',
        'requester_id',
        'requester_type',
        'requester_reference_id',
        'program_studi_id',
        'request_number',
        'number',
        'subject',
        'status',
        'payload',
        'attachments',
        'generated_file_path',
        'submitted_at',
        'verified_at',
        'approved_at',
        'completed_at',
        'distributed_at',
        'archived_at',
        'rejected_at',
        'rejection_reason',
        'qr_public_token',
    ];

    protected $casts = [
        'payload' => 'array',
        'attachments' => 'array',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'distributed_at' => 'datetime',
        'archived_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(SuratApproval::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(LetterRequestValue::class);
    }

    public function letterAttachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class);
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(LetterVerification::class);
    }

    public function letterNumber(): HasMany
    {
        return $this->hasMany(LetterNumber::class);
    }

    public function generatedLetters(): HasMany
    {
        return $this->hasMany(GeneratedLetter::class);
    }
}
