<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenPublikasi extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'dosen_id', 'title', 'year', 'type', 'publisher', 'doi_url', 'field', 'keywords', 'source'];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class);
    }
}
