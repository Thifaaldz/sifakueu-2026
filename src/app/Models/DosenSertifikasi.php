<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenSertifikasi extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'name', 'issuer', 'field', 'certificate_number', 'issued_on', 'expires_on', 'file_path', 'validation_status'];

    protected $casts = [
        'issued_on' => 'date',
        'expires_on' => 'date',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
