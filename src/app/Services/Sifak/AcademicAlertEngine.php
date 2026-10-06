<?php

namespace App\Services\Sifak;

use App\Models\Alert;
use App\Models\Mahasiswa;

class AcademicAlertEngine
{
    public function evaluate(Mahasiswa $mahasiswa): array
    {
        $alerts = [];

        if ((float) $mahasiswa->ipk < 2.5) {
            $alerts[] = $this->upsert($mahasiswa, 'ipk_low', 'high', 'IPK di bawah batas aman', 'IPK mahasiswa kurang dari 2.50.');
        }

        if ($mahasiswa->semester >= 6 && $mahasiswa->sks_lulus < 72) {
            $alerts[] = $this->upsert($mahasiswa, 'sks_late_risk', 'high', 'Risiko terlambat lulus', 'SKS lulus kurang dari 50% pada semester 6 atau lebih.');
        }

        if ($mahasiswa->sks_lulus >= 110 && ! $mahasiswa->dokumenTas()->whereIn('status', ['approved', 'final'])->exists()) {
            $alerts[] = $this->upsert($mahasiswa, 'ta_document_missing', 'medium', 'Dokumen TA belum siap', 'Mahasiswa sudah memenuhi SKS namun dokumen TA belum disetujui.');
        }

        return $alerts;
    }

    private function upsert(Mahasiswa $mahasiswa, string $type, string $severity, string $title, string $description): Alert
    {
        return Alert::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'type' => $type,
                'status' => 'open',
            ],
            [
                'tenant_id' => $mahasiswa->tenant_id,
                'severity' => $severity,
                'title' => $title,
                'description' => $description,
                'payload' => [
                    'ipk' => $mahasiswa->ipk,
                    'semester' => $mahasiswa->semester,
                    'sks_lulus' => $mahasiswa->sks_lulus,
                ],
            ]
        );
    }
}
