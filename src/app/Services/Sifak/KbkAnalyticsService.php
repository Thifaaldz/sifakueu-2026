<?php

namespace App\Services\Sifak;

use App\Models\Dosen;
use App\Models\Keahlian;
use App\Models\MataKuliah;
use App\Models\MatriksKesesuaian;
use App\Models\RumpunIlmu;
use Illuminate\Support\Collection;

class KbkAnalyticsService
{
    public function summary(): array
    {
        return [
            'total_dosen' => Dosen::query()->where('status', 'active')->count(),
            'dosen_per_rumpun' => $this->dosenPerRumpun(),
            'dosen_per_keahlian' => $this->dosenPerKeahlian(),
            'overload_dosen' => Dosen::query()->whereHas('bebanDosens', fn ($query) => $query->where('workload_status', 'overload'))->count(),
            'mk_tanpa_kandidat_kuat' => $this->mkTanpaKandidatKuat(),
            'gap_kompetensi' => $this->gapKompetensi(),
        ];
    }

    private function dosenPerRumpun(): Collection
    {
        return RumpunIlmu::query()
            ->withCount(['dosens' => fn ($query) => $query->where('status', 'active')])
            ->orderByDesc('dosens_count')
            ->get()
            ->map(fn ($rumpun) => ['name' => $rumpun->name, 'count' => $rumpun->dosens_count]);
    }

    private function dosenPerKeahlian(): Collection
    {
        return Keahlian::query()
            ->withCount('dosens')
            ->orderByDesc('dosens_count')
            ->limit(10)
            ->get()
            ->map(fn ($keahlian) => ['name' => $keahlian->name, 'count' => $keahlian->dosens_count]);
    }

    private function mkTanpaKandidatKuat(): int
    {
        return MataKuliah::query()
            ->whereDoesntHave('rekomendasiPengampus', fn ($query) => $query->where('score', '>=', config('sifak_m5.matching.threshold')))
            ->count();
    }

    private function gapKompetensi(): Collection
    {
        return MataKuliah::query()
            ->whereDoesntHave('rekomendasiPengampus', fn ($query) => $query->where('score', '>=', config('sifak_m5.matching.threshold')))
            ->with('rumpunIlmu')
            ->limit(10)
            ->get()
            ->map(fn ($mk) => [
                'mata_kuliah' => $mk->name,
                'rumpun' => $mk->rumpunIlmu?->name,
                'best_score' => MatriksKesesuaian::query()->where('mata_kuliah_id', $mk->id)->max('final_score') ?? 0,
            ]);
    }
}
