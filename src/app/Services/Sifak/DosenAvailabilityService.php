<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\DosenLokasi;
use App\Services\Shared\Audit\AuditService;

class DosenAvailabilityService
{
    public function __construct(private readonly AuditService $audit) {}

    public function updateLocation(Dosen $dosen, float $latitude, float $longitude, ?float $accuracy = null, string $status = 'available'): DosenLokasi
    {
        $location = DosenLokasi::create([
            'tenant_id' => $dosen->tenant_id,
            'dosen_id' => $dosen->id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'presence_status' => $status,
            'recorded_at' => now(),
        ]);

        $this->audit->record('LOCATION_STATUS_UPDATED', 'M5', $location, [], [
            'dosen_id' => $dosen->id,
            'presence_status' => $status,
            'recorded_at' => $location->recorded_at?->toDateTimeString(),
        ]);

        return $location;
    }

    public function currentAvailability(Dosen $dosen): array
    {
        $location = DosenLokasi::query()
            ->where('dosen_id', $dosen->id)
            ->latest('recorded_at')
            ->first();

        $expiresAt = now()->subMinutes(config('sifak_m5.location.expires_minutes'));

        if (! $location || $location->recorded_at < $expiresAt) {
            return [
                'status' => 'not_available',
                'label' => 'Not Available',
                'recorded_at' => null,
            ];
        }

        return [
            'status' => $location->presence_status,
            'label' => match ($location->presence_status) {
                'available' => 'Available',
                'online' => 'Online Consultation',
                default => 'Not Available',
            },
            'recorded_at' => $location->recorded_at,
        ];
    }
}
