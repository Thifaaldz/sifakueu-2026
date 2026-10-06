<?php

namespace App\Services\Sifak;

use App\Models\KelasKuliah;
use App\Models\PenawaranMataKuliah;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClassFormationService
{
    public function __construct(
        private readonly CourseDemandService $demand,
        private readonly AuditService $audit,
    ) {}

    /**
     * @return Collection<int, KelasKuliah>
     */
    public function generateDraft(PenawaranMataKuliah $offering): Collection
    {
        $offering = $this->demand->refresh($offering);
        $classCount = max(1, (int) $offering->target_jumlah_kelas);
        $capacity = max(1, (int) ($offering->maksimal_peserta ?: $offering->kuota_default));

        for ($i = 0; $i < $classCount; $i++) {
            $kelas = KelasKuliah::updateOrCreate(
                [
                    'tenant_id' => $offering->tenant_id,
                    'penawaran_mata_kuliah_id' => $offering->id,
                    'kode_kelas' => $this->classCode($offering, $i),
                ],
                [
                    'kapasitas' => $capacity,
                    'jumlah_peserta' => 0,
                    'status' => $offering->jumlah_peminat < $offering->minimal_peserta ? 'warning' : 'draft',
                    'created_by' => Auth::id(),
                ]
            );

            $this->audit->record('CLASS_CREATED', 'M4', $kelas, [], $kelas->toArray());
        }

        return $offering->kelasKuliahs()->orderBy('kode_kelas')->get();
    }

    private function classCode(PenawaranMataKuliah $offering, int $index): string
    {
        $course = $offering->mataKuliah;
        $semester = $offering->semester;
        $suffix = chr(65 + $index);

        return Str::upper(($course?->code ?? 'MK') . '-' . ($semester?->code ?? now()->year) . '-' . $suffix);
    }
}
