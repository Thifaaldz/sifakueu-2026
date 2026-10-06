<?php

namespace App\Services\Sifak;

use App\Models\RepositoryItem;
use App\Models\TugasAkhir;
use App\Services\Shared\Audit\AuditService;
use Illuminate\Validation\ValidationException;

class TaRepositoryService
{
    public function __construct(private readonly AuditService $audit) {}

    public function createOrUpdate(TugasAkhir $ta, array $data = []): RepositoryItem
    {
        if ($ta->status !== 'finalized' || ! $ta->finalDocumentVersion?->stored_file_id) {
            throw ValidationException::withMessages(['repository' => 'Repository hanya dapat dibuat untuk dokumen final.']);
        }

        $item = RepositoryItem::updateOrCreate(
            ['tenant_id' => $ta->tenant_id, 'tugas_akhir_id' => $ta->id],
            [
                'final_file_id' => $ta->finalDocumentVersion->stored_file_id,
                'title' => $data['title'] ?? $ta->judul,
                'abstract_id' => $data['abstract_id'] ?? null,
                'abstract_en' => $data['abstract_en'] ?? null,
                'keywords' => $data['keywords'] ?? $ta->keywords,
                'author_name' => $ta->mahasiswa?->name,
                'nim' => $ta->mahasiswa?->nim,
                'program_studi_id' => $ta->program_studi_id,
                'supervisor_names' => array_values(array_filter([$ta->pembimbing1?->name, $ta->pembimbing2?->name])),
                'year' => now()->year,
                'access_level' => $data['access_level'] ?? config('sifak_m7.repository.default_access_level'),
                'status' => $data['status'] ?? 'draft',
            ]
        );

        $this->audit->record('TA_REPOSITORY_CREATED', 'M7', $item, [], $item->toArray());

        return $item;
    }

    public function publish(RepositoryItem $item): RepositoryItem
    {
        $old = $item->toArray();
        $item->update(['status' => 'published', 'published_at' => now()]);
        $this->audit->record('TA_REPOSITORY_PUBLISHED', 'M7', $item, $old, $item->fresh()->toArray());

        return $item->fresh();
    }
}
