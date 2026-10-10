<?php

namespace App\Support\Access;

class AccessControl
{
    public const ROLES = [
        'super_admin',
        'admin_fakultas',
        'admin_prodi',
        'mahasiswa',
        'dosen',
        'dosen_pembimbing',
        'dosen_penguji',
        'dosen_pa',
        'kaprodi',
        'dekan',
        'wd',
        'kbk',
        'lpm',
        'kepala_laboratorium',
        'alumni',
        'baak',
        'user',
    ];

    public const PLATFORM_PERMISSIONS = [
        'view_tenants',
        'create_tenant',
        'update_tenant',
        'suspend_tenant',
        'reactivate_tenant',
        'view_tenant_health',
        'manage_domains',
        'manage_reserved_domains',
        'run_provisioning',
        'retry_provisioning',
        'view_provisioning_logs',
        'run_backup',
        'run_restore',
        'view_platform_audit',
        'manage_platform_users',
        'manage_global_settings',
    ];

    public const TENANT_BUSINESS_PERMISSIONS = [
        'admin_fakultas' => [
            'manage_tenant_profile',
            'manage_tenant_users',
            'manage_tenant_roles',
            'manage_master_data',
            'view_tenant_reports',
            'manage_tenant_settings',
            'manage_surat',
            'verify_surat',
            'generate_nomor_surat',
            'manage_surat_template',
            'manage_surat_archive',
            'view_letter_request',
            'verify_letter_request',
            'request_letter_revision',
            'reject_letter_request',
            'manage_letter_type',
            'manage_letter_form',
            'manage_approval_flow',
            'manage_letter_template',
            'generate_letter_number',
            'override_letter_number',
            'generate_letter_document',
            'regenerate_letter_document',
            'distribute_letter',
            'archive_letter',
            'view_letter_archive',
            'export_letter_report',
            'view_sidang',
            'manage_sidang_registration',
            'verify_sidang_registration',
            'manage_sidang_schedule',
            'generate_sidang_minutes',
            'view_faculty_monitoring',
            'view_monitoring_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'manage_ta_repository',
            'finalize_ta_document',
            'compile_ta_document',
            'publish_ta_repository',
            'change_repository_access',
            'view_faculty_reports',
        ],
        'admin_prodi' => [
            'manage_prodi_students',
            'manage_prodi_dosen',
            'manage_mata_kuliah',
            'manage_kurikulum',
            'verify_sidang',
            'verify_sidang_registration',
            'assign_examiner',
            'manage_sidang_schedule',
            'manage_sidang_requirement',
            'manage_sidang_assignment',
            'finalize_sidang_schedule',
            'finalize_sidang_result',
            'publish_sidang_result',
            'generate_sidang_minutes',
            'manage_class_schedule',
            'manage_krs_period',
            'manage_course_offering',
            'view_krs_monitoring',
            'view_course_demand',
            'manage_class',
            'manage_lecturer_plotting',
            'view_lecturer_recommendation',
            'accept_lecturer_recommendation',
            'manage_schedule',
            'view_schedule_conflict',
            'resolve_schedule_conflict',
            'export_krs',
            'export_schedule',
            'view_letter_request',
            'verify_letter_request',
            'request_letter_revision',
            'reject_letter_request',
            'generate_dosen_matching',
            'review_dosen_recommendation',
            'accept_dosen_recommendation',
            'view_prodi_monitoring',
            'view_prodi_alert',
            'escalate_alert',
            'manage_monitoring_rule',
            'activate_monitoring_rule',
            'override_monitoring_status',
            'view_monitoring_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'view_dosen_profile',
            'view_dosen_workload',
            'view_dosen_competency_gap',
            'view_student_profile',
            'manage_cpl',
            'manage_plo',
            'manage_cpl_mapping',
            'manage_plo_mapping',
            'manage_graduate_profile',
            'recalculate_student_profile',
            'generate_student_recommendation',
            'view_student_recommendation',
            'view_student_profile_dashboard',
            'export_student_profile_report',
            'view_ta_status',
            'view_ta_monitoring',
            'view_ta_progress',
            'manage_ta_metadata',
            'finalize_ta_document',
            'compile_ta_document',
            'view_final_ta_document',
            'view_prodi_reports',
        ],
        'mahasiswa' => [
            'view_own_profile',
            'update_own_profile',
            'create_krs',
            'create_own_krs',
            'update_own_krs',
            'submit_own_krs',
            'view_own_krs',
            'view_schedule',
            'submit_sidang',
            'view_own_sidang',
            'create_own_sidang_registration',
            'update_own_sidang_registration',
            'submit_own_sidang_registration',
            'view_own_sidang_result',
            'create_surat',
            'view_own_surat',
            'view_own_letter_request',
            'create_letter_request',
            'update_own_letter_request',
            'submit_letter_request',
            'cancel_own_letter_request',
            'download_own_letter',
            'view_own_alert',
            'view_own_monitoring',
            'acknowledge_own_alert',
            'view_own_cpl',
            'view_own_plo',
            'view_own_recommendation',
            'view_own_student_profile',
            'update_own_interest',
            'manage_own_portfolio',
            'manage_own_certification',
            'manage_own_mbkm',
            'manage_own_ta',
            'view_own_ta',
            'upload_own_ta_document',
            'submit_ta_document',
            'view_own_ta_history',
            'download_ta_document',
            'view_related_dosen_schedule',
        ],
        'dosen' => [
            'view_own_dosen_profile',
            'update_own_dosen_profile',
            'view_own_schedule',
            'view_own_workload',
            'manage_consultation_schedule',
            'manage_own_dosen_profile',
            'manage_own_education_history',
            'manage_own_certification',
            'manage_own_publication',
            'manage_own_industry_experience',
            'manage_own_course_preference',
            'update_own_presence_location',
            'view_own_matching_score',
            'view_own_recommendation_status',
            'view_assigned_students',
            'view_own_letter_request',
            'create_letter_request',
            'update_own_letter_request',
            'submit_letter_request',
            'download_own_letter',
        ],
        'dosen_pa' => [
            'view_own_dosen_profile',
            'update_own_dosen_profile',
            'view_own_schedule',
            'view_own_workload',
            'manage_consultation_schedule',
            'view_assigned_students',
            'view_pa_students',
            'view_student_krs',
            'approve_krs',
            'reject_krs',
            'request_krs_revision',
            'view_student_monitoring',
            'view_pa_student_profile',
            'view_pa_student_recommendation',
            'view_pa_monitoring',
            'view_pa_alert',
            'create_alert_followup',
            'resolve_alert',
            'followup_student_alert',
            'view_letter_request',
            'verify_letter_request',
            'request_letter_revision',
            'reject_letter_request',
        ],
        'dosen_pembimbing' => [
            'view_own_dosen_profile',
            'update_own_dosen_profile',
            'view_own_schedule',
            'view_own_workload',
            'manage_consultation_schedule',
            'view_assigned_students',
            'view_supervised_students',
            'view_supervised_ta',
            'review_ta',
            'review_ta_document',
            'comment_ta_document',
            'request_ta_revision',
            'approve_ta_document',
            'download_ta_document',
            'comment_ta',
            'approve_ta_chapter',
            'reject_ta_chapter',
            'view_ta_revision_log',
        ],
        'dosen_penguji' => [
            'view_own_dosen_profile',
            'update_own_dosen_profile',
            'view_own_schedule',
            'view_own_workload',
            'view_assigned_students',
            'view_assigned_sidang',
            'view_sidang_document',
            'input_sidang_score',
            'finalize_own_sidang_score',
            'input_sidang_note',
            'input_sidang_revision',
            'view_examiner_history',
        ],
        'kaprodi' => [
            'view_prodi_dashboard',
            'view_prodi_monitoring',
            'view_prodi_alert',
            'escalate_alert',
            'override_monitoring_status',
            'view_monitoring_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'approve_prodi_process',
            'validate_dosen_plotting',
            'view_dosen_matching',
            'review_dosen_recommendation',
            'accept_dosen_recommendation',
            'accept_lecturer_recommendation',
            'finalize_schedule',
            'approve_sidang_process',
            'view_sidang_monitoring',
            'finalize_sidang_result',
            'view_krs_monitoring',
            'view_course_demand',
            'view_dosen_workload',
            'view_competency_gap',
            'validate_rumpun',
            'view_cpl',
            'view_plo',
            'view_student_profile_dashboard',
            'view_student_recommendation',
            'export_student_profile_report',
            'view_prodi_profiles',
            'view_ta_monitoring',
            'view_ta_progress',
            'view_final_ta_document',
            'view_letter_request',
            'approve_letter',
            'reject_letter',
            'view_prodi_reports',
        ],
        'dekan' => [
            'view_faculty_dashboard',
            'view_faculty_monitoring',
            'view_monitoring_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'approve_strategic_surat',
            'view_letter_request',
            'approve_letter',
            'reject_letter',
            'view_faculty_dosen',
            'view_faculty_dosen_profile',
            'view_faculty_dosen_workload',
            'view_faculty_dosen_matching',
            'view_faculty_students',
            'view_faculty_cpl_plo',
            'view_student_profile_dashboard',
            'export_student_profile_report',
            'view_faculty_reports',
        ],
        'wd' => [
            'view_faculty_dashboard',
            'view_faculty_monitoring',
            'view_monitoring_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'approve_strategic_surat',
            'view_letter_request',
            'approve_letter',
            'reject_letter',
            'view_faculty_dosen',
            'view_faculty_students',
            'view_faculty_cpl_plo',
            'view_student_profile_dashboard',
            'export_student_profile_report',
            'view_faculty_reports',
        ],
        'kbk' => [
            'view_kbk_dashboard',
            'manage_rumpun',
            'validate_dosen_expertise',
            'manage_expertise_catalog',
            'view_dosen_matching',
            'view_recommendation',
            'approve_outside_rumpun',
            'view_competency_gap',
        ],
        'lpm' => [
            'view_quality_dashboard',
            'view_cpl',
            'view_plo',
            'view_cpl_gap',
            'view_graduate_profile',
            'view_accreditation_repository',
            'generate_quality_report',
            'view_quality_audit',
            'view_quality_monitoring',
            'view_quality_cpl_plo',
            'view_student_profile_dashboard',
            'view_monitoring_trend',
            'export_monitoring_report',
            'view_repository_metadata',
            'view_final_ta_document',
        ],
        'baak' => [
            'view_academic_recap',
            'view_sidang_recap',
            'view_graduation_status',
            'view_final_ta_document',
            'view_repository_metadata',
            'manage_yudisium',
            'generate_academic_report',
        ],
        'kepala_laboratorium' => [
            'view_lab_dashboard',
            'view_relevant_dosen',
            'view_relevant_dosen_profile',
            'view_relevant_dosen_availability',
            'view_relevant_students',
            'manage_assistant_requirement',
            'manage_research_topic',
            'view_lab_report',
        ],
        'alumni' => [
            'submit_alumni_feedback',
            'submit_employer_feedback',
            'view_own_feedback',
        ],
        'user' => [],
    ];

    public static function allPermissions(): array
    {
        return array_values(array_unique(array_merge(
            self::PLATFORM_PERMISSIONS,
            self::crudPermissions(),
            self::panelPermissions(),
            ...array_values(self::TENANT_BUSINESS_PERMISSIONS),
        )));
    }

    public static function permissionsForRole(string $role): array
    {
        return match ($role) {
            'super_admin' => array_values(array_unique(array_merge(
                self::PLATFORM_PERMISSIONS,
                self::crudFor(['tenant', 'user', 'role', 'activity']),
                ['widget_OverlookWidget', 'widget_LatestAccessLogs'],
            ))),
            'admin_fakultas' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['admin_fakultas'],
                self::crudFor(self::tenantAdminResources()),
                ['widget_OverlookWidget', 'widget_LatestAccessLogs'],
            ))),
            'admin_prodi' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['admin_prodi'],
                self::readCrudFor(array_merge(['fakultas', 'program::studi', 'mahasiswa', 'dosen', 'mata::kuliah', 'kurikulum', 'tahun::akademik', 'semester', 'ruangan', 'rumpun::ilmu', 'kbk', 'krs', 'jadwal::kuliah', 'pendaftaran::sidang', 'dokumen::ta', 'alert'], self::m2Resources(), self::m3Resources(), self::m4Resources(), self::m5Resources(), self::m6Resources(), self::m7ReadOnlyResources(), self::m1Resources())),
                self::writeCrudFor(array_merge(['mahasiswa', 'dosen', 'mata::kuliah', 'kurikulum', 'krs', 'jadwal::kuliah', 'pendaftaran::sidang'], self::m2OperationalResources(), self::m3OperationalResources(), self::m4OperationalResources(), self::m5OperationalResources(), self::m6OperationalResources(), self::m1OperationalResources(), ['tugas::akhir', 'ta::document'])),
            ))),
            'mahasiswa' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['mahasiswa'],
                self::readCrudFor(array_merge(['program::studi', 'mahasiswa', 'krs', 'krs::detail', 'krs::validation::result', 'penawaran::mata::kuliah', 'kelas::kuliah', 'jadwal::kuliah', 'jadwal::history', 'pendaftaran::sidang', 'surat', 'alert', 'dokumen::ta', 'dosen', 'mata::kuliah', 'kurikulum', 'tahun::akademik', 'semester', 'ruangan', 'jadwal::konsultasi'], self::m2MahasiswaResources(), self::m3MahasiswaResources(), self::m6MahasiswaResources(), self::m7MahasiswaResources(), self::m1MahasiswaResources())),
                self::writeCrudFor(array_merge(['krs', 'krs::detail', 'pendaftaran::sidang', 'surat', 'dokumen::ta'], self::m2RequesterWritableResources(), ['tugas::akhir', 'ta::document', 'ta::document::version', 'ta::comment', 'sidang::registration', 'sidang::file'], self::m6MahasiswaWritableResources())),
                ['update_mahasiswa', 'update_alert'],
            ))),
            'dosen' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['dosen'],
                self::readCrudFor(array_merge(['program::studi', 'dosen', 'mahasiswa', 'mata::kuliah', 'kurikulum', 'tahun::akademik', 'semester', 'ruangan', 'rumpun::ilmu', 'kbk', 'jadwal::kuliah'], self::m2MahasiswaResources(), self::m5DosenResources())),
                self::writeCrudFor(array_merge(['dosen::profil', 'dosen::pendidikan', 'dosen::sertifikasi', 'dosen::publikasi', 'dosen::pengalaman::industri', 'dosen::preferensi::mk', 'jadwal::konsultasi', 'dosen::lokasi'], self::m2RequesterWritableResources())),
                ['update_dosen'],
            ))),
            'dosen_pa' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['dosen_pa'],
                self::readCrudFor(array_merge(['dosen', 'mahasiswa', 'jadwal::kuliah', 'jadwal::history', 'krs', 'krs::detail', 'krs::validation::result', 'alert'], self::m3DosenResources(), self::m5DosenResources(), self::m6DosenResources())),
                self::writeCrudFor(array_merge(['dosen::profil', 'dosen::pendidikan', 'dosen::sertifikasi', 'dosen::publikasi', 'dosen::pengalaman::industri', 'dosen::preferensi::mk', 'jadwal::konsultasi', 'dosen::lokasi'], ['alert::followup'])),
                ['update_dosen', 'update_krs', 'update_alert'],
            ))),
            'dosen_pembimbing' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['dosen_pembimbing'],
                self::readCrudFor(array_merge(['dosen', 'mahasiswa', 'jadwal::kuliah', 'dokumen::ta', 'pendaftaran::sidang'], self::m5DosenResources(), self::m7DosenResources(), self::m1DosenResources())),
                self::writeCrudFor(array_merge(['dosen::profil', 'dosen::pendidikan', 'dosen::sertifikasi', 'dosen::publikasi', 'dosen::pengalaman::industri', 'dosen::preferensi::mk', 'jadwal::konsultasi', 'dosen::lokasi'], ['ta::review', 'ta::comment', 'ta::approval', 'sidang::revision'])),
                ['update_dosen', 'update_dokumen::ta', 'update_ta::document'],
            ))),
            'dosen_penguji' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['dosen_penguji'],
                self::readCrudFor(array_merge(['dosen', 'jadwal::kuliah', 'pendaftaran::sidang', 'dokumen::ta'], self::m5DosenResources(), self::m1DosenResources())),
                self::writeCrudFor(['dosen::profil', 'dosen::pendidikan', 'dosen::sertifikasi', 'dosen::publikasi', 'dosen::pengalaman::industri', 'dosen::preferensi::mk', 'jadwal::konsultasi', 'dosen::lokasi', 'sidang::score', 'sidang::revision']),
                ['update_dosen', 'update_pendaftaran::sidang', 'update_sidang::score'],
            ))),
            'kaprodi' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['kaprodi'],
                self::readCrudFor(array_merge(['fakultas', 'program::studi', 'mahasiswa', 'dosen', 'mata::kuliah', 'kurikulum', 'tahun::akademik', 'semester', 'ruangan', 'krs', 'jadwal::kuliah', 'pendaftaran::sidang', 'dokumen::ta', 'alert', 'rumpun::ilmu', 'kbk'], self::m2ReadOnlyResources(), self::m3Resources(), self::m4Resources(), self::m5Resources(), self::m6Resources(), self::m7ReadOnlyResources(), self::m1Resources())),
                self::writeCrudFor(['alert::escalation', 'monitoring::override']),
                ['update_pendaftaran::sidang', 'update_alert', 'update_alert::escalation', 'update_monitoring::override', 'update_rekomendasi::pengampu', 'update_jadwal::kuliah', 'update_jadwal::conflict', 'update_sidang::registration', 'update_sidang::assignment', 'update_sidang::result'],
            ))),
            'dekan', 'wd' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS[$role],
                self::readCrudFor(array_merge(['fakultas', 'program::studi', 'mahasiswa', 'dosen', 'mata::kuliah', 'kurikulum', 'tahun::akademik', 'semester', 'ruangan', 'pendaftaran::sidang', 'surat', 'dokumen::ta', 'alert'], self::m2ReadOnlyResources(), self::m3ReadOnlyResources(), self::m4ReadOnlyResources(), self::m5ReadOnlyResources(), self::m6ReadOnlyResources(), self::m7ReadOnlyResources(), self::m1ReadOnlyResources())),
                ['update_surat'],
            ))),
            'kbk' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['kbk'],
                self::readCrudFor(array_merge(['dosen', 'rumpun::ilmu', 'kbk', 'mata::kuliah'], self::m5Resources())),
                self::writeCrudFor(['keahlian', 'dosen::profil', 'matriks::kesesuaian', 'rekomendasi::pengampu']),
                ['update_rumpun::ilmu', 'update_kbk'],
            ))),
            'lpm' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['lpm'],
                self::readCrudFor(array_merge(['mahasiswa', 'dosen', 'dokumen::ta', 'alert'], self::m3ReadOnlyResources(), self::m6ReadOnlyResources(), self::m7ReadOnlyResources(), self::m1ReadOnlyResources())),
            ))),
            'baak' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['baak'],
                self::readCrudFor(array_merge(['mahasiswa', 'pendaftaran::sidang', 'dokumen::ta', 'alert'], self::m3ReadOnlyResources(), self::m6ReadOnlyResources(), self::m7ReadOnlyResources(), self::m1ReadOnlyResources())),
            ))),
            'kepala_laboratorium' => array_values(array_unique(array_merge(
                self::TENANT_BUSINESS_PERMISSIONS['kepala_laboratorium'],
                self::readCrudFor(array_merge(['mahasiswa', 'dosen', 'mata::kuliah'], self::m5ReadOnlyResources())),
            ))),
            'alumni' => self::TENANT_BUSINESS_PERMISSIONS['alumni'],
            default => [],
        };
    }

    public static function crudPermissions(): array
    {
        return self::crudFor(array_values(array_unique(array_merge(
            self::platformResources(),
            self::tenantAdminResources(),
            ['activity'],
        ))));
    }

    public static function platformResources(): array
    {
        return ['tenant', 'user', 'role'];
    }

    public static function tenantAdminResources(): array
    {
        return [
            'user',
            'role',
            'fakultas',
            'program::studi',
            'mahasiswa',
            'dosen',
            'mata::kuliah',
            'kurikulum',
            'tahun::akademik',
            'semester',
            'ruangan',
            'rumpun::ilmu',
            'kbk',
            'krs',
            'jadwal::kuliah',
            ...self::m4Resources(),
            'pendaftaran::sidang',
            ...self::m1Resources(),
            ...self::m2Resources(),
            'alert',
            ...self::m3Resources(),
            'dokumen::ta',
            ...self::m6Resources(),
            ...self::m7Resources(),
            'audit::log',
            'sifak::notification',
            'notification::template',
            'stored::file',
            'workflow::history',
            'scheduled::task::log',
            ...self::m5Resources(),
        ];
    }

    public static function m5Resources(): array
    {
        return [
            'keahlian',
            'dosen::profil',
            'dosen::pendidikan',
            'dosen::sertifikasi',
            'dosen::publikasi',
            'dosen::pengalaman::industri',
            'riwayat::mengajar',
            'dosen::preferensi::mk',
            'beban::dosen',
            'matriks::kesesuaian',
            'rekomendasi::pengampu',
            'jadwal::konsultasi',
            'dosen::lokasi',
        ];
    }

    public static function m2Resources(): array
    {
        return [
            'jenis::surat',
            'surat',
            'surat::approval',
            'approval::flow',
            'approval::flow::step',
            'letter::form::field',
            'letter::request::value',
            'letter::attachment',
            'letter::verification',
            'letter::number::sequence',
            'letter::number',
            'letter::template',
            'generated::letter',
            'letter::verification::token',
            'letter::distribution',
            'letter::archive',
        ];
    }

    public static function m2OperationalResources(): array
    {
        return self::m2Resources();
    }

    public static function m2ReadOnlyResources(): array
    {
        return self::m2Resources();
    }

    public static function m2MahasiswaResources(): array
    {
        return [
            'jenis::surat',
            'surat',
            'surat::approval',
            'letter::request::value',
            'letter::attachment',
            'letter::verification',
            'letter::number',
            'generated::letter',
            'letter::distribution',
            'letter::archive',
        ];
    }

    public static function m2RequesterWritableResources(): array
    {
        return [
            'surat',
            'letter::request::value',
            'letter::attachment',
        ];
    }

    public static function m3Resources(): array
    {
        return [
            'monitoring::rule',
            'monitoring::snapshot',
            'monitoring::indicator::result',
            'alert::followup',
            'alert::escalation',
            'monitoring::override',
        ];
    }

    public static function m3OperationalResources(): array
    {
        return [
            'monitoring::rule',
            'monitoring::snapshot',
            'monitoring::indicator::result',
            'alert::followup',
            'alert::escalation',
            'monitoring::override',
        ];
    }

    public static function m3ReadOnlyResources(): array
    {
        return [
            'monitoring::snapshot',
            'monitoring::indicator::result',
            'alert::followup',
            'alert::escalation',
        ];
    }

    public static function m3MahasiswaResources(): array
    {
        return [
            'monitoring::snapshot',
            'monitoring::indicator::result',
            'alert::followup',
        ];
    }

    public static function m3DosenResources(): array
    {
        return [
            'monitoring::snapshot',
            'monitoring::indicator::result',
            'alert::followup',
            'alert::escalation',
        ];
    }

    public static function m6Resources(): array
    {
        return [
            'mahasiswa::profile',
            'mahasiswa::interest',
            'mahasiswa::certification',
            'mahasiswa::portfolio',
            'mahasiswa::organization',
            'mahasiswa::mbkm',
            'cpl',
            'plo',
            'pemetaan::mk::cpl',
            'pemetaan::cpl::plo',
            'mahasiswa::cpl::score',
            'mahasiswa::plo::score',
            'graduate::profile',
            'mahasiswa::graduate::profile::score',
            'competency::gap',
            'student::recommendation',
            'recommendation::history',
        ];
    }

    public static function m6OperationalResources(): array
    {
        return self::m6Resources();
    }

    public static function m6ReadOnlyResources(): array
    {
        return self::m6Resources();
    }

    public static function m6MahasiswaResources(): array
    {
        return [
            'mahasiswa::profile',
            'mahasiswa::interest',
            'mahasiswa::certification',
            'mahasiswa::portfolio',
            'mahasiswa::organization',
            'mahasiswa::mbkm',
            'cpl',
            'plo',
            'mahasiswa::cpl::score',
            'mahasiswa::plo::score',
            'graduate::profile',
            'mahasiswa::graduate::profile::score',
            'competency::gap',
            'student::recommendation',
            'recommendation::history',
        ];
    }

    public static function m6MahasiswaWritableResources(): array
    {
        return [
            'mahasiswa::interest',
            'mahasiswa::certification',
            'mahasiswa::portfolio',
            'mahasiswa::organization',
            'mahasiswa::mbkm',
        ];
    }

    public static function m6DosenResources(): array
    {
        return [
            'mahasiswa::profile',
            'mahasiswa::interest',
            'mahasiswa::certification',
            'mahasiswa::portfolio',
            'mahasiswa::organization',
            'mahasiswa::mbkm',
            'mahasiswa::cpl::score',
            'mahasiswa::plo::score',
            'mahasiswa::graduate::profile::score',
            'competency::gap',
            'student::recommendation',
            'recommendation::history',
        ];
    }

    public static function m4Resources(): array
    {
        return [
            'periode::krs',
            'penawaran::mata::kuliah',
            'krs::detail',
            'krs::validation::result',
            'kelas::kuliah',
            'plotting::dosen',
            'jadwal::history',
            'jadwal::conflict',
        ];
    }

    public static function m4OperationalResources(): array
    {
        return [
            'periode::krs',
            'penawaran::mata::kuliah',
            'krs::detail',
            'kelas::kuliah',
            'plotting::dosen',
            'jadwal::conflict',
        ];
    }

    public static function m4ReadOnlyResources(): array
    {
        return [
            'periode::krs',
            'penawaran::mata::kuliah',
            'krs::detail',
            'krs::validation::result',
            'kelas::kuliah',
            'plotting::dosen',
            'jadwal::history',
            'jadwal::conflict',
        ];
    }

    public static function m1Resources(): array
    {
        return [
            'sidang::type',
            'sidang::requirement',
            'sidang::registration',
            'sidang::file',
            'sidang::assignment',
            'sidang::schedule',
            'sidang::rubric',
            'sidang::score',
            'sidang::result',
            'sidang::revision',
            'sidang::minute',
        ];
    }

    public static function m1OperationalResources(): array
    {
        return [
            'sidang::requirement',
            'sidang::registration',
            'sidang::assignment',
            'sidang::schedule',
            'sidang::rubric',
            'sidang::score',
            'sidang::result',
            'sidang::revision',
            'sidang::minute',
        ];
    }

    public static function m1MahasiswaResources(): array
    {
        return [
            'sidang::type',
            'sidang::requirement',
            'sidang::registration',
            'sidang::file',
            'sidang::schedule',
            'sidang::result',
            'sidang::revision',
            'sidang::minute',
        ];
    }

    public static function m1DosenResources(): array
    {
        return [
            'sidang::type',
            'sidang::requirement',
            'sidang::registration',
            'sidang::assignment',
            'sidang::schedule',
            'sidang::rubric',
            'sidang::score',
            'sidang::result',
            'sidang::revision',
            'sidang::minute',
        ];
    }

    public static function m1ReadOnlyResources(): array
    {
        return self::m1Resources();
    }

    public static function m7Resources(): array
    {
        return [
            'tugas::akhir',
            'ta::section',
            'ta::document',
            'ta::document::version',
            'ta::review',
            'ta::comment',
            'ta::approval',
            'ta::progress::log',
            'repository::item',
            'revision::cycle',
        ];
    }

    public static function m7MahasiswaResources(): array
    {
        return [
            'tugas::akhir',
            'ta::document',
            'ta::document::version',
            'ta::comment',
            'ta::progress::log',
            'repository::item',
            'revision::cycle',
        ];
    }

    public static function m7DosenResources(): array
    {
        return [
            'tugas::akhir',
            'ta::document',
            'ta::document::version',
            'ta::review',
            'ta::comment',
            'ta::approval',
            'ta::progress::log',
            'repository::item',
            'revision::cycle',
        ];
    }

    public static function m7ReadOnlyResources(): array
    {
        return [
            'tugas::akhir',
            'ta::section',
            'ta::document',
            'ta::document::version',
            'ta::review',
            'ta::comment',
            'ta::approval',
            'ta::progress::log',
            'repository::item',
            'revision::cycle',
        ];
    }

    public static function m5DosenResources(): array
    {
        return [
            'keahlian',
            'dosen::profil',
            'dosen::pendidikan',
            'dosen::sertifikasi',
            'dosen::publikasi',
            'dosen::pengalaman::industri',
            'riwayat::mengajar',
            'dosen::preferensi::mk',
            'beban::dosen',
            'matriks::kesesuaian',
            'rekomendasi::pengampu',
            'jadwal::konsultasi',
            'dosen::lokasi',
        ];
    }

    public static function m5ReadOnlyResources(): array
    {
        return [
            'keahlian',
            'dosen::profil',
            'dosen::pendidikan',
            'dosen::sertifikasi',
            'dosen::publikasi',
            'dosen::pengalaman::industri',
            'riwayat::mengajar',
            'beban::dosen',
            'matriks::kesesuaian',
            'rekomendasi::pengampu',
            'jadwal::konsultasi',
        ];
    }

    public static function m5OperationalResources(): array
    {
        return [
            'keahlian',
            'dosen::profil',
            'dosen::pendidikan',
            'dosen::sertifikasi',
            'dosen::publikasi',
            'dosen::pengalaman::industri',
            'riwayat::mengajar',
            'dosen::preferensi::mk',
            'beban::dosen',
            'matriks::kesesuaian',
            'rekomendasi::pengampu',
            'jadwal::konsultasi',
        ];
    }

    public static function panelPermissions(): array
    {
        return ['widget_OverlookWidget', 'widget_LatestAccessLogs'];
    }

    public static function crudFor(array $resources): array
    {
        return array_values(array_unique(array_merge(
            self::readCrudFor($resources),
            self::writeCrudFor($resources),
            collect($resources)->flatMap(fn (string $resource) => [
                'delete_' . $resource,
                'delete_any_' . $resource,
            ])->all(),
        )));
    }

    public static function readCrudFor(array $resources): array
    {
        return collect($resources)
            ->flatMap(fn (string $resource) => ['view_any_' . $resource, 'view_' . $resource])
            ->values()
            ->all();
    }

    public static function writeCrudFor(array $resources): array
    {
        return collect($resources)
            ->flatMap(fn (string $resource) => ['create_' . $resource, 'update_' . $resource])
            ->values()
            ->all();
    }
}
