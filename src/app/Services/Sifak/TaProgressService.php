<?php

namespace App\Services\Sifak;

use App\Models\TaProgressLog;
use App\Models\TaSection;
use App\Models\TugasAkhir;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Support\Facades\Auth;

class TaProgressService
{
    public function __construct(private readonly AuditService $audit) {}

    public function recalculate(TugasAkhir $ta, ?string $note = null): TugasAkhir
    {
        $old = $ta->toArray();
        $requiredSectionIds = TaSection::query()->where('required', true)->where('status', 'active')->pluck('id');
        $requiredCount = max(1, $requiredSectionIds->count());
        $approvedCount = $ta->documents()
            ->whereIn('ta_section_id', $requiredSectionIds)
            ->whereIn('status', ['approved', 'final'])
            ->count();

        $progress = round(($approvedCount / $requiredCount) * 100, 2);
        $status = $ta->status;

        if ($progress >= 100 && ! in_array($status, ['finalized', 'archived'], true)) {
            $status = 'ready_for_finalization';
        } elseif ($progress > 0 && $status === 'draft') {
            $status = 'active';
        }

        $ta->update([
            'progress_percent' => $progress,
            'status' => $status,
        ]);

        TaProgressLog::create([
            'tenant_id' => $ta->tenant_id,
            'tugas_akhir_id' => $ta->id,
            'progress_type' => 'recalculate',
            'old_status' => $old['status'] ?? null,
            'new_status' => $status,
            'progress_percent' => $progress,
            'note' => $note,
            'actor_id' => Auth::id(),
            'created_at' => now(),
        ]);

        $this->audit->record('TA_PROGRESS_UPDATED', 'M7', $ta, $old, $ta->fresh()->toArray());

        return $ta->fresh();
    }

    public function readiness(TugasAkhir $ta): array
    {
        $required = TaSection::query()->where('required', true)->where('status', 'active')->orderBy('sequence')->get();
        $approvedIds = $ta->documents()->whereIn('status', ['approved', 'final'])->pluck('ta_section_id')->all();
        $pending = $required->reject(fn (TaSection $section) => in_array($section->id, $approvedIds, true))->pluck('code')->values()->all();

        return [
            'student_id' => $ta->mahasiswa_id,
            'ta_status' => $ta->status,
            'document_ready' => $pending === [],
            'final_document_available' => filled($ta->final_document_version_id),
            'pending_sections' => $pending,
        ];
    }
}
