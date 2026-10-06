<?php

namespace App\Services\Sifak;

use App\Models\Mahasiswa;

class AcademicRuleService
{
    public function maxSksFor(Mahasiswa $mahasiswa, ?float $ips = null): int
    {
        $ips ??= (float) $mahasiswa->ipk;

        foreach (config('academic_rules.krs.max_sks_by_ips', []) as $rule) {
            if ($ips >= (float) $rule['min']) {
                return (int) $rule['max_sks'];
            }
        }

        return (int) config('academic_rules.krs.default_max_sks', 21);
    }

    public function studentMaySubmitKrs(Mahasiswa $mahasiswa): bool
    {
        return in_array($mahasiswa->status, config('academic_rules.krs.allowed_student_statuses', ['active']), true);
    }
}
