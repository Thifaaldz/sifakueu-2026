<?php

namespace App\Services\Sifak;

use App\Models\Alert;
use App\Models\Mahasiswa;

class AcademicAlertEngine
{
    public function __construct(private readonly MonitoringEvaluationService $monitoringEvaluationService) {}

    public function evaluate(Mahasiswa $mahasiswa): array
    {
        $snapshot = $this->monitoringEvaluationService->evaluate($mahasiswa);

        return Alert::query()
            ->where('monitoring_snapshot_id', $snapshot->id)
            ->get()
            ->all();
    }
}
