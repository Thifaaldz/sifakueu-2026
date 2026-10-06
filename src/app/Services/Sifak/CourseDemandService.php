<?php

namespace App\Services\Sifak;

use App\Models\PenawaranMataKuliah;
use App\Services\Shared\Audit\AuditService;

class CourseDemandService
{
    public function __construct(private readonly AuditService $audit) {}

    public function refresh(PenawaranMataKuliah $offering): PenawaranMataKuliah
    {
        $old = $offering->toArray();
        $demand = $offering->krsDetails()
            ->whereIn('status', ['selected', 'approved'])
            ->count();

        $target = max(1, (int) ceil($demand / max(1, $offering->maksimal_peserta ?: $offering->kuota_default)));

        $offering->update([
            'jumlah_peminat' => $demand,
            'target_jumlah_kelas' => $target,
        ]);

        $this->audit->record('COURSE_DEMAND_UPDATED', 'M4', $offering, $old, $offering->fresh()->toArray());

        return $offering->fresh();
    }
}
