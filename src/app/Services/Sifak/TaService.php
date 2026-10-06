<?php

namespace App\Services\Sifak;

use App\Models\Mahasiswa;
use App\Models\TaDocument;
use App\Models\TaSection;
use App\Models\TugasAkhir;
use App\Services\Shared\Audit\AuditService;

class TaService
{
    public function __construct(private readonly AuditService $audit) {}

    public function createForStudent(Mahasiswa $mahasiswa, array $data): TugasAkhir
    {
        $ta = TugasAkhir::updateOrCreate(
            [
                'tenant_id' => $mahasiswa->tenant_id,
                'mahasiswa_id' => $mahasiswa->id,
            ],
            [
                'program_studi_id' => $mahasiswa->program_studi_id,
                'judul' => $data['judul'],
                'judul_en' => $data['judul_en'] ?? null,
                'topik' => $data['topik'] ?? null,
                'keywords' => $data['keywords'] ?? [],
                'pembimbing_1_id' => $data['pembimbing_1_id'] ?? null,
                'pembimbing_2_id' => $data['pembimbing_2_id'] ?? null,
                'tahun_akademik_id' => $data['tahun_akademik_id'] ?? null,
                'semester_id' => $data['semester_id'] ?? null,
                'status' => $data['status'] ?? 'draft',
            ]
        );

        $this->ensureDefaultSections();
        $this->ensureDocuments($ta);
        $this->audit->record('TA_CREATED', 'M7', $ta, [], $ta->toArray());

        return $ta;
    }

    public function ensureDefaultSections(): void
    {
        foreach (config('sifak_m7.sections') as $section) {
            TaSection::updateOrCreate(
                ['code' => $section['code']],
                [
                    'name' => $section['name'],
                    'sequence' => $section['sequence'],
                    'required' => $section['required'],
                    'template_type' => $section['template_type'],
                    'status' => 'active',
                ]
            );
        }
    }

    public function ensureDocuments(TugasAkhir $ta): void
    {
        TaSection::query()
            ->where('status', 'active')
            ->orderBy('sequence')
            ->get()
            ->each(fn (TaSection $section) => TaDocument::firstOrCreate(
                [
                    'tenant_id' => $ta->tenant_id,
                    'tugas_akhir_id' => $ta->id,
                    'ta_section_id' => $section->id,
                ],
                ['status' => 'draft']
            ));
    }
}
