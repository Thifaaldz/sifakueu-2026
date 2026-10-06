<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\SidangAssignment;
use App\Models\SidangRegistration;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SidangAssignmentService
{
    public function __construct(private readonly AuditService $audit) {}

    public function assign(SidangRegistration $registration, Dosen $dosen, string $role, ?string $justification = null, ?int $recommendationId = null): SidangAssignment
    {
        if ($dosen->status !== 'active') {
            throw ValidationException::withMessages(['dosen_id' => 'Dosen penguji/pembimbing harus aktif.']);
        }

        if (! config('sifak_m1.policy.allow_supervisor_as_examiner') && str_starts_with($role, 'PENGUJI')) {
            $supervisorIds = array_filter([$registration->tugasAkhir?->pembimbing_1_id, $registration->tugasAkhir?->pembimbing_2_id]);

            if (in_array($dosen->id, $supervisorIds, true)) {
                throw ValidationException::withMessages(['dosen_id' => 'Pembimbing tidak boleh dipilih sebagai penguji sesuai kebijakan saat ini.']);
            }
        }

        $assignment = SidangAssignment::updateOrCreate(
            [
                'tenant_id' => $registration->tenant_id,
                'sidang_registration_id' => $registration->id,
                'role' => $role,
            ],
            [
                'dosen_id' => $dosen->id,
                'recommendation_id' => $recommendationId,
                'status' => 'assigned',
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
                'justification' => $justification,
            ]
        );

        if ($registration->status === 'ready_for_plotting') {
            $registration->update(['status' => 'verified']);
        }

        $this->audit->record('SIDANG_ASSIGNMENT_SET', 'M1', $assignment, [], $assignment->toArray());

        return $assignment;
    }
}
