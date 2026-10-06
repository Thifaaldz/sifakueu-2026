<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'slug',
        'subdomain',
        'database_name',
        'database_connection',
        'status',
        'enabled_modules',
        'settings',
        'created_by',
        'provisioned_at',
    ];

    protected $casts = [
        'enabled_modules' => 'array',
        'settings' => 'array',
        'provisioned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            $tenant->uuid ??= (string) Str::uuid();
            $tenant->enabled_modules ??= array_keys(config('sifak.modules'));
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(TenantAuditLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
