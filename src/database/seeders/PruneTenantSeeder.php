<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PruneTenantSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSlug = config('sifak.default_tenant_slug', 'fasilkom');
        $databasePrefix = Str::slug(env('PROJECT_NAME', env('DB_DATABASE', 'sifakueu')), '_') . '_tenant_';

        $this->pruneTenantDataFromCentralDatabase();

        $tenants = Tenant::query()
            ->where('slug', '!=', $defaultSlug)
            ->get(['id', 'slug', 'database_name']);

        foreach ($tenants as $tenant) {
            $databaseName = $tenant->database_name;

            $tenant->delete();

            if (
                is_string($databaseName)
                && str_starts_with($databaseName, $databasePrefix)
                && $databaseName !== $databasePrefix . Str::slug($defaultSlug, '_')
                && preg_match('/^[A-Za-z0-9_]+$/', $databaseName)
            ) {
                DB::statement('DROP DATABASE IF EXISTS `' . str_replace('`', '``', $databaseName) . '`');
            }
        }
    }

    private function pruneTenantDataFromCentralDatabase(): void
    {
        $tables = [
            'alert_escalations',
            'alert_followups',
            'monitoring_overrides',
            'sidang_minutes',
            'sidang_revisions',
            'sidang_results',
            'sidang_examiner_summaries',
            'sidang_scores',
            'sidang_rubrics',
            'sidang_schedule_histories',
            'sidang_schedules',
            'sidang_assignments',
            'sidang_verifications',
            'sidang_files',
            'sidang_requirement_results',
            'sidang_registrations',
            'sidang_requirements',
            'sidang_types',
            'repository_items',
            'revision_cycles',
            'ta_progress_logs',
            'ta_approvals',
            'ta_comments',
            'ta_reviews',
            'ta_document_versions',
            'ta_documents',
            'ta_sections',
            'tugas_akhirs',
            'repositori_tas',
            'log_revisis',
            'bab_tas',
            'dokumen_tas',
            'template_dokumens',
            'rekomendasi_plos',
            'skor_cpl_mahasiswas',
            'pemetaan_cpl_plos',
            'pemetaan_mk_cpls',
            'plos',
            'cpls',
            'alerts',
            'monitoring_indicator_results',
            'monitoring_snapshots',
            'monitoring_rules',
            'recommendation_histories',
            'student_recommendations',
            'competency_gaps',
            'mahasiswa_graduate_profile_scores',
            'graduate_profile_plo',
            'graduate_profiles',
            'mahasiswa_plo_scores',
            'mahasiswa_mbkms',
            'mahasiswa_organizations',
            'mahasiswa_portfolios',
            'mahasiswa_certifications',
            'mahasiswa_interests',
            'mahasiswa_profiles',
            'letter_archives',
            'letter_distributions',
            'letter_verification_tokens',
            'generated_letters',
            'letter_templates',
            'letter_numbers',
            'letter_number_sequences',
            'letter_verifications',
            'letter_attachments',
            'letter_request_values',
            'letter_form_fields',
            'surat_approvals',
            'surats',
            'approval_flow_steps',
            'approval_flows',
            'jenis_surats',
            'pendaftaran_sidangs',
            'jadwal_conflicts',
            'jadwal_histories',
            'krs_validation_results',
            'krs_details',
            'plotting_dosen',
            'dosen_lokasis',
            'jadwal_konsultasis',
            'matriks_kesesuaians',
            'jadwal_kuliahs',
            'kelas_kuliah',
            'penawaran_mata_kuliah',
            'periode_krs',
            'krs',
            'ruangans',
            'mata_kuliahs',
            'dosens',
            'mahasiswas',
            'kurikulums',
            'kbks',
            'rumpun_ilmus',
            'program_studis',
        ];

        foreach ($tables as $table) {
            DB::table($table)->delete();
        }

        $tenantUserIds = DB::table('users')
            ->whereNotNull('tenant_id')
            ->pluck('id');

        if ($tenantUserIds->isNotEmpty()) {
            DB::table('model_has_roles')
                ->where('model_type', \App\Models\User::class)
                ->whereIn('model_id', $tenantUserIds)
                ->delete();

            DB::table('model_has_permissions')
                ->where('model_type', \App\Models\User::class)
                ->whereIn('model_id', $tenantUserIds)
                ->delete();

            DB::table('users')
                ->whereIn('id', $tenantUserIds)
                ->delete();
        }
    }
}
