<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaOrganization extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'organization_name', 'role', 'start_date', 'end_date', 'description'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
