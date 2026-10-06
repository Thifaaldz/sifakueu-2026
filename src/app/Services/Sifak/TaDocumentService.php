<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\RevisionCycle;
use App\Models\StoredFile;
use App\Models\TaApproval;
use App\Models\TaComment;
use App\Models\TaDocument;
use App\Models\TaDocumentVersion;
use App\Models\TaReview;
use App\Services\Shared\Audit\AuditService;
use App\Services\Shared\File\FileService;
use App\Services\Shared\Notification\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TaDocumentService
{
    public function __construct(
        private readonly FileService $files,
        private readonly TaProgressService $progress,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
    ) {}

    public function uploadVersion(TaDocument $document, UploadedFile $file, ?string $summary = null): TaDocumentVersion
    {
        $versionNumber = ((int) $document->versions()->max('version_number')) + 1;
        $ta = $document->tugasAkhir;
        $storedFile = $this->files->upload($file, 'ta', $document, [
            'directory' => ($ta->mahasiswa?->nim ?? $ta->id) . '/' . strtolower($document->section?->code ?? 'section'),
            'extensions' => config('sifak_m7.upload.extensions'),
            'max_kb' => config('sifak_m7.upload.max_kb'),
            'status' => 'active',
        ]);

        $version = TaDocumentVersion::create([
            'tenant_id' => $document->tenant_id,
            'ta_document_id' => $document->id,
            'version_number' => $versionNumber,
            'stored_file_id' => $storedFile->id,
            'submitted_by' => Auth::id(),
            'change_summary' => $summary,
            'status' => 'draft',
            'checksum' => $storedFile->checksum,
        ]);

        $document->update([
            'current_version_id' => $version->id,
            'status' => 'draft',
        ]);

        $this->audit->record('TA_VERSION_UPLOADED', 'M7', $version, [], $version->toArray());

        return $version;
    }

    public function submit(TaDocument $document): TaDocumentVersion
    {
        $version = $document->currentVersion;

        if (! $version) {
            throw ValidationException::withMessages(['version' => 'Belum ada versi dokumen untuk disubmit.']);
        }

        $version->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
        ]);
        $document->update(['status' => 'submitted']);
        $document->tugasAkhir->update(['status' => 'in_review']);

        foreach ([$document->tugasAkhir->pembimbing1, $document->tugasAkhir->pembimbing2] as $dosen) {
            if ($dosen?->user_id) {
                $this->notifications->create($dosen->user_id, 'TA_DOCUMENT_SUBMITTED', 'Dokumen TA Menunggu Review', 'Mahasiswa mengirim ' . $document->section->name . ' untuk direview.', reference: $document);
            }
        }

        $this->audit->record('TA_VERSION_SUBMITTED', 'M7', $version, [], $version->fresh()->toArray());

        return $version->fresh();
    }

    public function addComment(TaDocumentVersion $version, Dosen $reviewer, string $comment, array $meta = []): TaComment
    {
        $taComment = TaComment::create([
            'tenant_id' => $version->tenant_id,
            'ta_document_version_id' => $version->id,
            'reviewer_id' => $reviewer->id,
            'comment' => $comment,
            'page_reference' => $meta['page_reference'] ?? null,
            'section_reference' => $meta['section_reference'] ?? null,
            'status' => 'open',
        ]);

        $this->audit->record('TA_COMMENT_ADDED', 'M7', $taComment, [], $taComment->toArray());

        return $taComment;
    }

    public function requestRevision(TaDocumentVersion $version, Dosen $reviewer, string $summary): TaReview
    {
        $review = TaReview::create([
            'tenant_id' => $version->tenant_id,
            'ta_document_version_id' => $version->id,
            'reviewer_id' => $reviewer->id,
            'review_status' => 'revision_required',
            'summary' => $summary,
            'reviewed_at' => now(),
        ]);

        $version->update(['status' => 'reviewed']);
        $version->document->update(['status' => 'revision_required']);
        $version->document->tugasAkhir->update(['status' => 'revision']);

        RevisionCycle::firstOrCreate(
            ['tenant_id' => $version->tenant_id, 'tugas_akhir_id' => $version->document->tugas_akhir_id, 'status' => 'open'],
            ['source' => 'supervisor', 'started_at' => now()]
        );

        $this->audit->record('TA_REVISION_REQUESTED', 'M7', $review, [], $review->toArray());

        return $review;
    }

    public function approve(TaDocumentVersion $version, Dosen $approver, ?string $note = null): TaApproval
    {
        $document = $version->document;
        $document->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $version->update(['status' => 'reviewed']);

        $approval = TaApproval::create([
            'tenant_id' => $document->tenant_id,
            'ta_document_id' => $document->id,
            'approver_id' => $approver->id,
            'approval_type' => 'section',
            'status' => 'approved',
            'note' => $note,
            'approved_at' => now(),
        ]);

        $this->progress->recalculate($document->tugasAkhir, 'Section approved: ' . $document->section?->code);
        $this->audit->record('TA_SECTION_APPROVED', 'M7', $approval, [], $approval->toArray());

        return $approval;
    }
}
