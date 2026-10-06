<?php

namespace App\Services\Sifak;

use App\Models\RevisionCycle;
use App\Models\SidangRegistration;
use App\Models\SidangRevision;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;

class SidangRevisionService
{
    public function __construct(private readonly AuditService $audit) {}

    public function create(SidangRegistration $registration, array $data): SidangRevision
    {
        $revision = SidangRevision::create([
            'tenant_id' => $registration->tenant_id,
            'sidang_registration_id' => $registration->id,
            'examiner_id' => $data['examiner_id'] ?? null,
            'description' => $data['description'],
            'category' => $data['category'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'status' => 'open',
        ]);

        if ($registration->tugas_akhir_id) {
            RevisionCycle::firstOrCreate(
                ['tenant_id' => $registration->tenant_id, 'tugas_akhir_id' => $registration->tugas_akhir_id, 'source' => strtolower($registration->type?->code ?? 'sidang_ta'), 'status' => 'open'],
                ['started_at' => now(), 'deadline' => $data['deadline'] ?? null]
            );
        }

        $registration->update(['status' => 'revision']);
        $this->audit->record('SIDANG_REVISION_CREATED', 'M1', $revision, [], $revision->toArray());

        return $revision;
    }

    public function validate(SidangRevision $revision): SidangRevision
    {
        $revision->update(['status' => 'validated', 'resolved_at' => now(), 'validated_by' => Auth::id()]);
        $this->audit->record('SIDANG_REVISION_VALIDATED', 'M1', $revision, [], $revision->fresh()->toArray());

        return $revision->fresh();
    }
}
