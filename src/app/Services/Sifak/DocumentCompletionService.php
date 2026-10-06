<?php

namespace App\Services\Sifak;

use App\Models\DokumenTa;

class DocumentCompletionService
{
    public function isReadyForSidang(DokumenTa $dokumenTa): bool
    {
        $approvedChapters = $dokumenTa->babTas()
            ->whereIn('chapter_number', [1, 2, 3, 4, 5])
            ->where('status', 'approved')
            ->count();

        return $approvedChapters === 5
            && filled($dokumenTa->table_of_contents)
            && filled($dokumenTa->bibliography)
            && in_array($dokumenTa->status, ['approved', 'final'], true);
    }
}
