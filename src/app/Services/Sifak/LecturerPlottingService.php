<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\PlottingDosen;
use App\Models\RekomendasiPengampu;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LecturerPlottingService
{
    public function __construct(
        private readonly DosenRecommendationService $recommendations,
        private readonly DosenWorkloadService $workloads,
        private readonly AuditService $audit,
    ) {}

    public function requestRecommendations(KelasKuliah $kelas, int $limit = 5)
    {
        $offering = $kelas->penawaranMataKuliah;
        $recommendations = $this->recommendations->refreshForCourse($offering->mataKuliah, $limit, $offering->semester);

        $this->audit->record('LECTURER_RECOMMENDATION_REQUESTED', 'M4', $kelas, [], [
            'mata_kuliah_id' => $offering->mata_kuliah_id,
            'semester_id' => $offering->semester_id,
            'limit' => $limit,
        ]);

        return $recommendations;
    }

    public function assign(KelasKuliah $kelas, Dosen $dosen, ?RekomendasiPengampu $recommendation = null, string $role = 'utama', ?string $justification = null): PlottingDosen
    {
        if ($dosen->status !== 'active') {
            throw ValidationException::withMessages(['dosen_id' => 'Dosen harus aktif.']);
        }

        if ($recommendation && (float) $recommendation->score < config('sifak_m5.matching.threshold') && blank($justification)) {
            throw ValidationException::withMessages(['justification' => 'Justifikasi wajib untuk kandidat di bawah threshold M5.']);
        }

        $plotting = PlottingDosen::updateOrCreate(
            [
                'tenant_id' => $kelas->tenant_id,
                'kelas_kuliah_id' => $kelas->id,
                'dosen_id' => $dosen->id,
                'role_pengampu' => $role,
            ],
            [
                'rekomendasi_pengampu_id' => $recommendation?->id,
                'sks_beban' => $kelas->penawaranMataKuliah?->mataKuliah?->sks ?? 0,
                'status' => 'assigned',
                'selected_by' => Auth::id(),
                'justification' => $justification,
            ]
        );

        if ($recommendation) {
            $this->recommendations->accept($recommendation, $justification);
        }

        $this->workloads->calculate($dosen, $kelas->penawaranMataKuliah?->semester);
        $this->audit->record('LECTURER_ASSIGNED', 'M4', $plotting, [], $plotting->toArray());

        return $plotting;
    }
}
