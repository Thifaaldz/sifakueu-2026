<?php

namespace App\Support\Access;

/**
 * Label Bahasa Indonesia untuk judul halaman, breadcrumb, dan tombol (mis. "Buat Pendaftaran Sidang").
 * Label menu per role diatur terpisah di RoleFeatureRegistry.
 */
class ResourceLabels
{
    public const LABELS = [
        'AlertEscalation' => 'Eskalasi Alert', 'AlertFollowup' => 'Tindak Lanjut Alert', 'Alert' => 'Alert Akademik',
        'ApprovalFlow' => 'Alur Approval', 'ApprovalFlowStep' => 'Langkah Approval', 'AuditLog' => 'Audit Log',
        'BebanDosen' => 'Beban Dosen', 'CompetencyGap' => 'Gap Kompetensi', 'Cpl' => 'CPL', 'DokumenTa' => 'Dokumen TA (Lama)',
        'DosenLokasi' => 'Lokasi Dosen', 'DosenPendidikan' => 'Pendidikan Dosen', 'DosenPengalamanIndustri' => 'Pengalaman Industri',
        'DosenPreferensiMk' => 'Preferensi Mata Kuliah', 'DosenProfil' => 'Profil Dosen', 'DosenPublikasi' => 'Publikasi Dosen',
        'Dosen' => 'Dosen', 'DosenSertifikasi' => 'Sertifikasi Dosen', 'Fakultas' => 'Fakultas', 'GeneratedLetter' => 'Dokumen Surat',
        'GraduateProfile' => 'Profil Lulusan', 'JadwalConflict' => 'Konflik Jadwal', 'JadwalHistory' => 'Riwayat Jadwal',
        'JadwalKonsultasi' => 'Jadwal Konsultasi', 'JadwalKuliah' => 'Jadwal Kuliah', 'JenisSurat' => 'Jenis Surat', 'Kbk' => 'KBK',
        'Keahlian' => 'Keahlian', 'KelasKuliah' => 'Kelas Kuliah', 'KrsDetail' => 'Rincian KRS', 'Krs' => 'KRS',
        'KrsValidationResult' => 'Hasil Validasi KRS', 'Kurikulum' => 'Kurikulum', 'LetterArchive' => 'Arsip Surat',
        'LetterAttachment' => 'Lampiran Surat', 'LetterDistribution' => 'Distribusi Surat', 'LetterFormField' => 'Form Field Surat',
        'LetterNumber' => 'Nomor Surat', 'LetterNumberSequence' => 'Sequence Nomor Surat', 'LetterRequestValue' => 'Data Form Surat',
        'LetterTemplate' => 'Template Surat', 'LetterVerification' => 'Log Verifikasi Surat', 'LetterVerificationToken' => 'Token Verifikasi',
        'MahasiswaCertification' => 'Sertifikasi Mahasiswa', 'MahasiswaCplScore' => 'Skor CPL', 'MahasiswaGraduateProfileScore' => 'Skor Profil Lulusan',
        'MahasiswaInterest' => 'Minat Mahasiswa', 'MahasiswaMbkm' => 'MBKM / Magang', 'MahasiswaOrganization' => 'Organisasi Mahasiswa',
        'MahasiswaPloScore' => 'Skor PLO', 'MahasiswaPortfolio' => 'Portofolio', 'MahasiswaProfile' => 'Profil Mahasiswa',
        'Mahasiswa' => 'Mahasiswa', 'MataKuliah' => 'Mata Kuliah', 'MatriksKesesuaian' => 'Matriks Kesesuaian',
        'MonitoringIndicatorResult' => 'Hasil Indikator', 'MonitoringOverride' => 'Override Status', 'MonitoringRule' => 'Aturan Monitoring',
        'MonitoringSnapshot' => 'Snapshot Evaluasi', 'NotificationTemplate' => 'Template Notifikasi', 'PemetaanCplPlo' => 'Pemetaan CPL–PLO',
        'PemetaanMkCpl' => 'Pemetaan MK–CPL', 'PenawaranMataKuliah' => 'Penawaran Mata Kuliah', 'PendaftaranSidang' => 'Pendaftaran Sidang (Lama)',
        'PeriodeKrs' => 'Periode KRS', 'Plo' => 'PLO', 'PlottingDosen' => 'Plotting Dosen', 'ProgramStudi' => 'Program Studi',
        'RecommendationHistory' => 'Riwayat Rekomendasi', 'RekomendasiPengampu' => 'Rekomendasi Pengampu', 'RepositoryItem' => 'Repositori TA',
        'RevisionCycle' => 'Siklus Revisi', 'RiwayatMengajar' => 'Riwayat Mengajar', 'Ruangan' => 'Ruangan', 'RumpunIlmu' => 'Rumpun Ilmu',
        'ScheduledTaskLog' => 'Log Tugas Terjadwal', 'Semester' => 'Semester', 'SidangAssignment' => 'Penugasan Sidang',
        'SidangMinute' => 'Berita Acara', 'SidangRegistration' => 'Pendaftaran Sidang', 'SidangRequirement' => 'Persyaratan Sidang',
        'SidangResult' => 'Hasil Sidang', 'SidangRevision' => 'Revisi Sidang', 'SidangRubric' => 'Rubrik Penilaian',
        'SidangSchedule' => 'Jadwal Sidang', 'SidangScore' => 'Nilai Sidang', 'SidangType' => 'Jenis Sidang', 'SifakNotification' => 'Notifikasi',
        'StoredFile' => 'File Tersimpan', 'StudentRecommendation' => 'Rekomendasi Mahasiswa', 'SuratApproval' => 'Riwayat Approval Surat',
        'Surat' => 'Surat', 'TaApproval' => 'Approval Bab', 'TaComment' => 'Komentar TA', 'TaDocument' => 'Dokumen TA',
        'TaDocumentVersion' => 'Versi Dokumen TA', 'TaProgressLog' => 'Progres TA', 'TaReview' => 'Review TA', 'TaSection' => 'Struktur Bab TA',
        'TahunAkademik' => 'Tahun Akademik', 'TugasAkhir' => 'Tugas Akhir', 'User' => 'Pengguna', 'WorkflowHistory' => 'Riwayat Workflow',
    ];

    public static function for(string $resource): ?string
    {
        return self::LABELS[str(class_basename($resource))->beforeLast('Resource')->value()] ?? null;
    }
}
