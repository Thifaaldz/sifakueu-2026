<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\MatriksKesesuaian;
use App\Models\RekomendasiPengampu;
use App\Models\Semester;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DosenRecommendationService
{
    public function __construct(
        private readonly DosenMatchingService $matching,
        private readonly AuditService $audit,
    ) {}

    public function refreshForCourse(MataKuliah $mataKuliah, int $limit = 5, ?Semester $semester = null): Collection
    {
        $matrices = Dosen::query()
            ->where('status', 'active')
            ->get()
            ->map(fn (Dosen $dosen) => $this->matching->calculate($dosen, $mataKuliah, $semester))
            ->sortByDesc('final_score')
            ->values();

        RekomendasiPengampu::query()
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id), fn ($query) => $query->whereNull('semester_id'))
            ->whereIn('status', ['generated', 'reviewed'])
            ->update(['status' => 'superseded']);

        $matrices->take($limit)->each(function (MatriksKesesuaian $matrix, int $index) use ($mataKuliah, $semester) {
            RekomendasiPengampu::create([
                'tenant_id' => $mataKuliah->tenant_id,
                'mata_kuliah_id' => $mataKuliah->id,
                'semester_id' => $semester?->id,
                'dosen_id' => $matrix->dosen_id,
                'ranking' => $index + 1,
                'score' => $matrix->final_score,
                'score_breakdown' => $matrix->score_breakdown,
                'summary_reason' => $this->summaryReason($matrix),
                'status' => 'generated',
                'generated_at' => now(),
            ]);
        });

        $this->audit->record('RECOMMENDATION_GENERATED', 'M5', $mataKuliah, [], [
            'mata_kuliah_id' => $mataKuliah->id,
            'semester_id' => $semester?->id,
            'limit' => $limit,
        ]);

        return RekomendasiPengampu::query()
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id), fn ($query) => $query->whereNull('semester_id'))
            ->where('status', 'generated')
            ->with(['dosen', 'mataKuliah'])
            ->orderBy('ranking')
            ->limit($limit)
            ->get();
    }

    public function accept(RekomendasiPengampu $recommendation, ?string $justification = null): RekomendasiPengampu
    {
        if ((float) $recommendation->score < config('sifak_m5.matching.threshold') && blank($justification)) {
            throw ValidationException::withMessages([
                'justification' => 'Justifikasi wajib diisi untuk rekomendasi di bawah threshold.',
            ]);
        }

        $old = $recommendation->toArray();
        $recommendation->update([
            'status' => 'accepted',
            'justification' => $justification,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        RekomendasiPengampu::query()
            ->where('mata_kuliah_id', $recommendation->mata_kuliah_id)
            ->when(
                $recommendation->semester_id,
                fn ($query) => $query->where('semester_id', $recommendation->semester_id),
                fn ($query) => $query->whereNull('semester_id')
            )
            ->where('id', '!=', $recommendation->id)
            ->whereIn('status', ['generated', 'reviewed'])
            ->update(['status' => 'rejected']);

        $this->audit->record('RECOMMENDATION_ACCEPTED', 'M5', $recommendation, $old, $recommendation->fresh()->toArray());

        if ((float) $recommendation->score < config('sifak_m5.matching.threshold')) {
            $this->audit->record('LOW_SCORE_JUSTIFICATION_ADDED', 'M5', $recommendation, [], ['justification' => $justification]);
        }

        return $recommendation->fresh();
    }

    public function reject(RekomendasiPengampu $recommendation, ?string $justification = null): RekomendasiPengampu
    {
        $old = $recommendation->toArray();
        $recommendation->update([
            'status' => 'rejected',
            'justification' => $justification,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->audit->record('RECOMMENDATION_REJECTED', 'M5', $recommendation, $old, $recommendation->fresh()->toArray());

        return $recommendation->fresh();
    }

    private function summaryReason(MatriksKesesuaian $matrix): string
    {
        $parts = [];

        if ($matrix->rumpun_score >= 80) {
            $parts[] = 'rumpun cocok';
        }

        if ($matrix->history_score >= 70) {
            $parts[] = 'riwayat mengajar kuat';
        }

        if ($matrix->publication_score >= 50) {
            $parts[] = 'publikasi relevan';
        }

        if ($matrix->certification_score >= 50) {
            $parts[] = 'sertifikasi relevan';
        }

        if ($matrix->preference_score >= 80) {
            $parts[] = 'preferensi tinggi';
        }

        if ($matrix->overload_penalty > 0) {
            $parts[] = 'beban perlu diperhatikan';
        }

        return $parts ? ucfirst(implode(', ', $parts)) . '.' : 'Kandidat tersedia dengan skor komposit M5.';
    }
}
