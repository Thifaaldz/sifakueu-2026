<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MahasiswaMbkm extends Model
{
    use BelongsToTenant;

    protected $table = 'mahasiswa_mbkms';

    protected $fillable = ['tenant_id', 'mahasiswa_id', 'program_type', 'institution', 'role', 'start_date', 'end_date', 'field', 'description'];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
