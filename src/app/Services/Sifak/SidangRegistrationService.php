<?php

namespace App\Services\Sifak;

use App\Models\Mahasiswa;
use App\Models\SidangRegistration;
use App\Models\SidangType;
use App\Models\TugasAkhir;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SidangRegistrationService
{
    public function __construct(
        private readonly SidangRequirementService $requirements,
        private readonly AuditService $audit,
    ) {}

    public function createForStudent(Mahasiswa $mahasiswa, SidangType $type, array $data = []): SidangRegistration
    {
        $ta = $data['tugas_akhir_id'] ?? TugasAkhir::query()->where('mahasiswa_id', $mahasiswa->id)->latest('id')->value('id');

        $registration = SidangRegistration::create([
            'tenant_id' => $mahasiswa->tenant_id,
            'sidang_type_id' => $type->id,
            'mahasiswa_id' => $mahasiswa->id,
            'tugas_akhir_id' => $ta,
            'semester_id' => $data['semester_id'] ?? null,
            'tahun_akademik_id' => $data['tahun_akademik_id'] ?? null,
            'registration_number' => $this->number($type),
            'status' => $data['status'] ?? 'draft',
        ]);

        $this->requirements->validate($registration);
        $this->audit->record('SIDANG_REGISTRATION_CREATED', 'M1', $registration, [], $registration->toArray());

        return $registration;
    }

    public function submit(SidangRegistration $registration): SidangRegistration
    {
        if (! $this->requirements->isSubmittable($registration)) {
            throw ValidationException::withMessages(['requirements' => 'Pendaftaran belum dapat disubmit karena syarat wajib belum lengkap.']);
        }

        $old = $registration->toArray();
        $registration->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->audit->record('SIDANG_REGISTRATION_SUBMITTED', 'M1', $registration, $old, $registration->fresh()->toArray());

        return $registration->fresh();
    }

    private function number(SidangType $type): string
    {
        return $type->code . '-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
    }
}
