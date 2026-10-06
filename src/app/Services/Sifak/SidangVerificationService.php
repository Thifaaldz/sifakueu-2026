<?php

namespace App\Services\Sifak;

use App\Models\SidangRegistration;
use App\Models\SidangVerification;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SidangVerificationService
{
    public function __construct(
        private readonly SidangRequirementService $requirements,
        private readonly AuditService $audit,
    ) {}

    public function verify(SidangRegistration $registration, ?string $note = null): SidangRegistration
    {
        if (! $this->requirements->isComplete($registration)) {
            throw ValidationException::withMessages(['requirements' => 'Verifikasi ditolak, syarat wajib belum valid.']);
        }

        $old = $registration->toArray();
        $registration->update([
            'status' => 'ready_for_plotting',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        SidangVerification::create([
            'tenant_id' => $registration->tenant_id,
            'sidang_registration_id' => $registration->id,
            'verifier_id' => Auth::id(),
            'verification_type' => 'admin',
            'status' => 'verified',
            'note' => $note,
            'verified_at' => now(),
        ]);

        $this->audit->record('SIDANG_REGISTRATION_VERIFIED', 'M1', $registration, $old, $registration->fresh()->toArray());

        return $registration->fresh();
    }

    public function requestRevision(SidangRegistration $registration, string $note): SidangRegistration
    {
        $old = $registration->toArray();
        $registration->update(['status' => 'revision_required', 'rejection_reason' => $note]);

        SidangVerification::create([
            'tenant_id' => $registration->tenant_id,
            'sidang_registration_id' => $registration->id,
            'verifier_id' => Auth::id(),
            'verification_type' => 'admin',
            'status' => 'revision_required',
            'note' => $note,
            'verified_at' => now(),
        ]);

        $this->audit->record('SIDANG_REGISTRATION_REVISION_REQUESTED', 'M1', $registration, $old, $registration->fresh()->toArray());

        return $registration->fresh();
    }
}
