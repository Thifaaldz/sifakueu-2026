<?php

namespace App\Services\Sifak;

use App\Models\MonitoringRule;

class MonitoringRuleService
{
    public function ensureDefaults(int $tenantId): void
    {
        foreach ($this->defaults() as $rule) {
            MonitoringRule::updateOrCreate(
                ['tenant_id' => $tenantId, 'code' => $rule['code']],
                $rule + ['tenant_id' => $tenantId]
            );
        }
    }

    public function defaults(): array
    {
        return [
            [
                'code' => 'KRS_NOT_FINAL',
                'name' => 'KRS belum final',
                'domain' => 'KRS',
                'description' => 'Mahasiswa belum memiliki KRS final pada semester berjalan.',
                'source_module' => 'M4',
                'metric_key' => 'krs_final_missing',
                'operator' => '=',
                'threshold_value' => 1,
                'warning_value' => 1,
                'severity' => 'medium',
                'priority' => 70,
                'active' => true,
            ],
            [
                'code' => 'SKS_PROGRESS_GAP',
                'name' => 'Progress SKS tertinggal',
                'domain' => 'STUDY_PROGRESS',
                'description' => 'SKS lulus berada di bawah target semester.',
                'source_module' => 'M4',
                'metric_key' => 'sks_gap',
                'operator' => '>',
                'threshold_value' => 18,
                'warning_value' => 6,
                'severity' => 'high',
                'priority' => 90,
                'active' => true,
            ],
            [
                'code' => 'IPK_LOW',
                'name' => 'IPK di bawah batas aman',
                'domain' => 'ACADEMIC',
                'description' => 'IPK mahasiswa di bawah threshold akademik.',
                'source_module' => 'M6',
                'metric_key' => 'ipk',
                'operator' => '<',
                'threshold_value' => 2,
                'warning_value' => 2.5,
                'severity' => 'high',
                'priority' => 80,
                'active' => true,
            ],
            [
                'code' => 'TA_PROGRESS_LOW',
                'name' => 'Progress TA rendah',
                'domain' => 'TA',
                'description' => 'Progress TA rendah untuk mahasiswa semester akhir.',
                'source_module' => 'M7',
                'metric_key' => 'ta_progress_percent',
                'operator' => '<',
                'threshold_value' => 25,
                'warning_value' => 50,
                'severity' => 'medium',
                'priority' => 75,
                'active' => true,
            ],
            [
                'code' => 'TA_INACTIVE',
                'name' => 'TA tidak aktif',
                'domain' => 'TA',
                'description' => 'Tidak ada aktivitas TA dalam rentang waktu yang melewati batas.',
                'source_module' => 'M7',
                'metric_key' => 'ta_inactive_days',
                'operator' => '>',
                'threshold_value' => 14,
                'warning_value' => 7,
                'severity' => 'high',
                'priority' => 85,
                'active' => true,
            ],
            [
                'code' => 'SEMESTER_WITHOUT_SEMPRO',
                'name' => 'Belum Sempro',
                'domain' => 'SIDANG',
                'description' => 'Mahasiswa semester lanjut belum memiliki proses Sempro.',
                'source_module' => 'M1',
                'metric_key' => 'semester_without_sempro',
                'operator' => '>=',
                'threshold_value' => 8,
                'warning_value' => 7,
                'severity' => 'medium',
                'priority' => 70,
                'active' => true,
            ],
            [
                'code' => 'SEMESTER_WITHOUT_SIDANG_TA',
                'name' => 'Belum Sidang TA',
                'domain' => 'SIDANG',
                'description' => 'Mahasiswa semester akhir belum memiliki proses Sidang TA.',
                'source_module' => 'M1',
                'metric_key' => 'semester_without_sidang_ta',
                'operator' => '>=',
                'threshold_value' => 9,
                'warning_value' => 8,
                'severity' => 'high',
                'priority' => 90,
                'active' => true,
            ],
            [
                'code' => 'REVISION_OVERDUE',
                'name' => 'Revisi sidang overdue',
                'domain' => 'REVISION',
                'description' => 'Revisi sidang sudah melewati deadline.',
                'source_module' => 'M1',
                'metric_key' => 'revision_overdue_days',
                'operator' => '>',
                'threshold_value' => 0,
                'warning_value' => -3,
                'severity' => 'high',
                'priority' => 95,
                'active' => true,
            ],
            [
                'code' => 'CPL_PLO_GAP',
                'name' => 'Gap CPL/PLO',
                'domain' => 'CPL_PLO',
                'description' => 'Skor CPL/PLO berada di bawah standar tenant.',
                'source_module' => 'M6',
                'metric_key' => 'cpl_gap',
                'operator' => '>=',
                'threshold_value' => 20,
                'warning_value' => 10,
                'severity' => 'medium',
                'priority' => 65,
                'active' => true,
            ],
        ];
    }
}
