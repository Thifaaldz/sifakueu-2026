<?php

namespace App\Services\Sifak;

use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\KrsValidationResult;
use App\Models\PeriodeKrs;
use Illuminate\Support\Collection;

class KrsValidationService
{
    public function __construct(private readonly AcademicRuleService $rules) {}

    /**
     * @return Collection<int, KrsValidationResult>
     */
    public function validate(Krs $krs, bool $persist = true): Collection
    {
        $krs->loadMissing(['mahasiswa', 'details.mataKuliah', 'details.penawaranMataKuliah']);

        if ($persist) {
            KrsValidationResult::query()->where('krs_id', $krs->id)->delete();
        }

        $results = collect();
        $add = function (string $code, string $severity, bool $passed, string $message, ?KrsDetail $detail = null, array $metadata = []) use ($krs, $persist, $results) {
            $payload = [
                'tenant_id' => $krs->tenant_id,
                'krs_id' => $krs->id,
                'krs_detail_id' => $detail?->id,
                'validation_code' => $code,
                'severity' => $severity,
                'passed' => $passed,
                'message' => $message,
                'metadata' => $metadata,
                'created_at' => now(),
            ];

            $results->push($persist ? KrsValidationResult::create($payload) : new KrsValidationResult($payload));
        };

        $mahasiswa = $krs->mahasiswa;
        $details = $krs->details;

        $add('STUDENT_STATUS', 'error', $this->rules->studentMaySubmitKrs($mahasiswa), 'Status mahasiswa harus aktif.');

        $periode = PeriodeKrs::query()
            ->where('semester_id', $krs->semester_id)
            ->where(function ($query) use ($mahasiswa) {
                $query->whereNull('program_studi_id')->orWhere('program_studi_id', $mahasiswa->program_studi_id);
            })
            ->where('status', 'open')
            ->first();

        $periodOpen = $periode?->isOpenForRegularSubmission() || ($krs->status === 'revision_required' && $periode?->isOpenForRevision());
        $add('KRS_PERIOD', 'error', (bool) $periodOpen, 'Periode KRS/revisi harus sedang dibuka.');

        $totalSks = (int) $details->sum('sks');
        $maxSks = $this->rules->maxSksFor($mahasiswa);
        $add('MAX_SKS', 'error', $totalSks <= $maxSks, "Total SKS {$totalSks} tidak boleh melebihi {$maxSks}.", metadata: ['total_sks' => $totalSks, 'max_sks' => $maxSks]);

        $duplicates = $details->groupBy('mata_kuliah_id')->filter(fn ($items) => $items->count() > 1);
        $add('DUPLICATE_COURSE', 'error', $duplicates->isEmpty(), 'Mata kuliah yang sama tidak boleh dipilih dua kali.', metadata: ['duplicates' => $duplicates->keys()->values()->all()]);

        foreach ($details as $detail) {
            $offering = $detail->penawaranMataKuliah;
            $course = $detail->mataKuliah;

            $add('COURSE_OFFERING_ACTIVE', 'error', $offering?->status === 'open', 'Mata kuliah harus berasal dari penawaran yang terbuka.', $detail);

            $sameProgram = ! $offering || $offering->program_studi_id === $mahasiswa->program_studi_id;
            $add('CURRICULUM_SCOPE', 'error', $sameProgram, 'Penawaran mata kuliah harus sesuai program studi mahasiswa.', $detail);

            $missingPrerequisites = collect($course?->prerequisite_course_ids ?? [])
                ->filter(fn ($courseId) => ! $this->hasApprovedCourse($krs, (int) $courseId))
                ->values()
                ->all();
            $add('PREREQUISITE', 'error', $missingPrerequisites === [], 'Prasyarat mata kuliah harus terpenuhi.', $detail, ['missing' => $missingPrerequisites]);
        }

        $this->markDetails($krs);
        $krs->update([
            'total_sks' => $totalSks,
            'validation_notes' => $results->map(fn (KrsValidationResult $result) => [
                'code' => $result->validation_code,
                'severity' => $result->severity,
                'passed' => $result->passed,
                'message' => $result->message,
            ])->values()->all(),
        ]);

        return $results;
    }

    public function hasBlockingErrors(Krs $krs): bool
    {
        return KrsValidationResult::query()
            ->where('krs_id', $krs->id)
            ->where('passed', false)
            ->where('severity', 'error')
            ->exists();
    }

    private function hasApprovedCourse(Krs $krs, int $mataKuliahId): bool
    {
        return KrsDetail::query()
            ->whereHas('krs', fn ($query) => $query
                ->where('mahasiswa_id', $krs->mahasiswa_id)
                ->whereIn('status', ['approved', 'final']))
            ->where('mata_kuliah_id', $mataKuliahId)
            ->whereIn('status', ['approved', 'selected'])
            ->exists();
    }

    private function markDetails(Krs $krs): void
    {
        $krs->details()->each(function (KrsDetail $detail) {
            $hasError = KrsValidationResult::query()
                ->where('krs_detail_id', $detail->id)
                ->where('passed', false)
                ->where('severity', 'error')
                ->exists();

            $detail->update([
                'validation_status' => $hasError ? 'invalid' : 'valid',
                'validation_note' => $hasError ? 'Perlu perbaikan sebelum KRS diajukan.' : null,
            ]);
        });
    }
}
