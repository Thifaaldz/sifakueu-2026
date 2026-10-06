# M3 — MONITORING & ALERT
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M3 — Monitoring & Alert  
**Tahap Implementasi:** Modul Bisnis Keenam setelah M5, M4, M7, M1, dan M6  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, M4 KRS & Penjadwalan, M6 Profiling Mahasiswa, M7 Manajemen Dokumen TA, M1 Sidang Sempro & TA  
**Tujuan:** Menjabarkan kebutuhan, indikator monitoring, rule engine, alert, klasifikasi hijau/kuning/merah, eskalasi, dashboard, hak akses, integrasi, acceptance criteria, QA, serta strategi implementasi M3 secara detail.

---

# 1. Ringkasan Modul

M3 merupakan modul pemantauan kondisi akademik mahasiswa dan proses akademik utama secara terintegrasi.

M3 menggabungkan data dari modul lain:

```text
M4
→ KRS
→ SKS
→ status pengisian
→ histori akademik
→ semester berjalan

M6
→ CPL
→ PLO
→ competency gap
→ profil mahasiswa
→ recommendation status

M7
→ progress TA
→ review pending
→ revisi
→ last activity

M1
→ status Sempro
→ status Sidang TA
→ revisi pasca sidang
→ hasil proses sidang
```

Data tersebut diolah menjadi:

```text
Monitoring
↓
Rule Evaluation
↓
Risk Classification
↓
HIJAU / KUNING / MERAH
↓
Alert
↓
Tindak Lanjut
↓
Eskalasi
↓
Riwayat
```

M3 tidak menggantikan keputusan Dosen PA, Kaprodi, atau pimpinan.

Sistem memberikan indikator dan alert sebagai alat bantu pengambilan tindakan.

---

# 2. Tujuan M3

M3 bertujuan untuk:

1. memonitor kondisi akademik mahasiswa;
2. memonitor status KRS;
3. memonitor SKS dan progress studi;
4. memonitor IPK/IPS jika data tersedia;
5. memonitor progres TA;
6. memonitor status Sempro dan Sidang TA;
7. memonitor revisi yang belum selesai;
8. memonitor gap CPL/PLO;
9. memonitor potensi keterlambatan kelulusan;
10. mengklasifikasikan risiko mahasiswa;
11. menghasilkan alert otomatis;
12. memberikan alert kepada mahasiswa;
13. memberikan alert kepada Dosen PA;
14. memberikan alert kepada Kaprodi/Admin;
15. mendukung escalation rule;
16. mencatat tindak lanjut;
17. menyediakan dashboard mahasiswa;
18. menyediakan dashboard Dosen PA;
19. menyediakan dashboard Admin/Kaprodi;
20. menyediakan dashboard pimpinan;
21. mendukung indikator IKU internal yang dibutuhkan;
22. menyediakan riwayat monitoring;
23. menyediakan explainability kenapa status mahasiswa berubah;
24. menjaga tenant isolation.

---

# 3. Scope M3

## 3.1 In Scope

```text
Academic Monitoring
KRS Monitoring
SKS Monitoring
IPK/IPS Monitoring
TA Progress Monitoring
Sempro Monitoring
Sidang TA Monitoring
Revision Monitoring
CPL/PLO Monitoring
Risk Classification
Green / Yellow / Red
Alert Generation
Alert Escalation
Alert Acknowledgement
Follow-up
Dashboard
Trend
History
Notification
Audit
Scheduler
Queue
```

## 3.2 Out of Scope

Untuk versi awal:

- diagnosis psikologis;
- prediksi dropout berbasis ML kompleks;
- disciplinary scoring;
- keputusan DO otomatis;
- keputusan kelulusan otomatis;
- ranking publik mahasiswa;
- intervensi akademik tanpa persetujuan manusia;
- profiling sensitif di luar kebutuhan akademik.

---

# 4. Aktor M3

| Aktor | Peran |
|---|---|
| Mahasiswa | Melihat status monitoring, alert, rekomendasi tindak lanjut miliknya |
| Dosen PA | Melihat mahasiswa PA, alert, membuat follow-up |
| Admin Prodi | Monitoring seluruh mahasiswa pada scope prodi |
| Kaprodi | Melihat dashboard risiko, trend, dan tindak lanjut |
| Dekan/WD | Melihat monitoring agregat fakultas |
| LPM/Gugus Mutu | Melihat indikator mutu/CPL/PLO secara agregat |
| M4 Service | Menyediakan data KRS/SKS/semester |
| M6 Service | Menyediakan CPL/PLO/gap/profil |
| M7 Service | Menyediakan progress TA |
| M1 Service | Menyediakan status Sempro/Sidang |
| Notification Service | Mengirim alert |
| Scheduler | Menjalankan evaluasi rutin |

---

# 5. Panel dan Menu

## 5.1 Mahasiswa Panel

```text
Monitoring
├── Status Akademik
├── Status Risiko
├── KRS & SKS
├── Progress TA
├── Sempro & Sidang
├── CPL/PLO
├── Alert Saya
├── Rekomendasi Tindak Lanjut
└── Riwayat Monitoring
```

---

## 5.2 Dosen Panel — Dosen PA

```text
Monitoring Mahasiswa PA
├── Ringkasan
├── Mahasiswa Hijau
├── Mahasiswa Kuning
├── Mahasiswa Merah
├── Alert Aktif
├── Follow-up
├── Riwayat Alert
└── Trend
```

---

## 5.3 Admin Panel

```text
Monitoring & Alert
├── Dashboard
├── Semua Mahasiswa
├── Risk Classification
├── Alert Aktif
├── Alert History
├── Rule Management
├── Follow-up Monitoring
├── Prodi Monitoring
└── Laporan
```

---

## 5.4 Pimpinan Panel

```text
Dashboard Monitoring
├── Risiko Mahasiswa
├── Kelulusan Tepat Waktu
├── Alert per Prodi
├── Progress TA
├── CPL/PLO Gap
├── Trend Semester
└── Laporan
```

---

# 6. Dependensi Data

M3 menggunakan data:

```text
Master Data
├── Mahasiswa
├── Program Studi
├── Semester
└── Tahun Akademik

M4
├── KRS Status
├── Total SKS
├── Histori Semester
└── Mata Kuliah

M6
├── CPL
├── PLO
├── Competency Gap
└── Student Profile

M7
├── Progress TA
├── Last Activity
├── Revision Deadline
└── Finalization Status

M1
├── Sempro Status
├── Sidang Status
├── Revision Status
└── Completion
```

---

# 7. Konsep Monitoring

Monitoring terdiri dari beberapa domain:

```text
ACADEMIC
KRS
STUDY_PROGRESS
TA
SIDANG
CPL_PLO
REVISION
```

Setiap domain memiliki:

```text
indicator
rule
threshold
severity
status
explanation
```

---

# 8. Risk Classification

M3 menggunakan tiga kategori utama:

```text
HIJAU
KUNING
MERAH
```

Makna umum:

```text
HIJAU
→ kondisi normal / tidak membutuhkan intervensi khusus

KUNING
→ perlu perhatian / follow-up

MERAH
→ perlu tindakan prioritas
```

---

# 9. Prinsip Klasifikasi

Klasifikasi tidak boleh hanya berdasarkan satu nilai tunggal.

Sistem dapat menggunakan:

```text
jumlah indikator merah
jumlah indikator kuning
severity tertinggi
rule priority
manual override terbatas
```

---

# 10. Contoh Indikator

Contoh indikator yang dapat digunakan:

```text
KRS belum diisi
KRS belum disetujui
SKS tertinggal
IPK/IPS turun
CPL gap tinggi
PLO gap tinggi
TA tidak aktif
Review TA terlalu lama
Revisi overdue
Belum Sempro
Belum Sidang TA
Sidang tertunda
```

---

# 11. monitoring_rules

```text
monitoring_rules
├── id
├── code
├── name
├── domain
├── description
├── source_module
├── metric_key
├── operator
├── threshold_value
├── warning_value nullable
├── severity
├── priority
├── active
├── version
└── timestamps
```

Operator contoh:

```text
<
<=
>
>=
=
IN
NOT_IN
DAYS_SINCE
```

---

# 12. monitoring_snapshots

Menyimpan hasil evaluasi mahasiswa pada waktu tertentu.

```text
monitoring_snapshots
├── id
├── mahasiswa_id
├── semester_id
├── overall_status
├── risk_score nullable
├── total_green
├── total_yellow
├── total_red
├── evaluated_at
└── timestamps
```

---

# 13. monitoring_indicator_results

```text
monitoring_indicator_results
├── id
├── snapshot_id
├── rule_id
├── metric_value
├── status
├── explanation
├── source_reference_type nullable
├── source_reference_id nullable
└── timestamps
```

Status:

```text
GREEN
YELLOW
RED
NOT_APPLICABLE
```

---

# 14. alerts

```text
alerts
├── id
├── mahasiswa_id
├── rule_id
├── indicator_result_id
├── alert_type
├── severity
├── title
├── message
├── status
├── assigned_to nullable
├── acknowledged_at nullable
├── resolved_at nullable
├── resolved_by nullable
└── timestamps
```

---

# 15. Alert Status

```text
OPEN
ACKNOWLEDGED
IN_FOLLOW_UP
RESOLVED
CLOSED
```

---

# 16. alert_followups

```text
alert_followups
├── id
├── alert_id
├── actor_id
├── action_type
├── note
├── next_action_date nullable
├── status
└── timestamps
```

---

# 17. alert_escalations

```text
alert_escalations
├── id
├── alert_id
├── from_role
├── to_role
├── reason
├── escalated_at
└── timestamps
```

---

# 18. monitoring_overrides

Untuk koreksi terbatas.

```text
monitoring_overrides
├── id
├── mahasiswa_id
├── original_status
├── override_status
├── reason
├── valid_until nullable
├── approved_by
└── timestamps
```

Override tidak menghapus hasil rule asli.

---

# 19. Rule Domain — KRS

Contoh rule:

```text
KRS belum dibuat
KRS masih DRAFT
KRS belum disetujui PA
KRS melewati deadline
```

Contoh:

```text
Jika periode KRS tinggal <= 3 hari
dan KRS belum SUBMITTED
→ YELLOW

Jika periode KRS sudah CLOSED
dan KRS belum FINAL
→ RED
```

---

# 20. Rule Domain — SKS / Study Progress

Contoh:

```text
SKS aktual
vs
SKS target semester
```

Contoh target sederhana:

```text
Semester 4
Target SKS = 72
Aktual = 52
Gap = 20
```

Rule:

```text
gap <= 6
→ GREEN

gap 7–18
→ YELLOW

gap > 18
→ RED
```

Threshold harus configurable.

---

# 21. Rule Domain — IPK / IPS

Jika data tersedia:

```text
IPK stabil
→ GREEN

Penurunan moderat
→ YELLOW

Penurunan signifikan
→ RED
```

Contoh:

```text
IPS current < 2.50
→ YELLOW

IPS current < 2.00
→ RED
```

Angka final mengikuti aturan akademik tenant.

---

# 22. Rule Domain — TA Progress

Data dari M7:

```text
progress_percent
last_activity
review_pending
revision_deadline
```

Contoh:

```text
No activity <= 7 hari
→ GREEN

No activity 8–14 hari
→ YELLOW

No activity > 14 hari
→ RED
```

Threshold dapat dikonfigurasi.

---

# 23. Rule Domain — Sempro

Contoh:

```text
Semester >= threshold
dan belum daftar Sempro
→ YELLOW

Semester jauh melewati target
dan belum Sempro
→ RED
```

Target semester mengikuti kebijakan prodi.

---

# 24. Rule Domain — Sidang TA

Contoh:

```text
Dokumen M7 ready
tetapi belum daftar sidang > N hari
→ YELLOW

Sudah masuk semester akhir
belum Sidang TA
→ RED
```

---

# 25. Rule Domain — Revisi Sidang

Data M1 + M7:

```text
revision deadline belum dekat
→ GREEN

deadline <= 3 hari
→ YELLOW

deadline lewat
→ RED
```

---

# 26. Rule Domain — CPL/PLO

Data dari M6:

```text
CPL/PLO gap
```

Contoh:

```text
gap < 10
→ GREEN

gap 10–19
→ YELLOW

gap >= 20
→ RED
```

---

# 27. Overall Status

Contoh logika versi awal:

```text
Jika ada indikator RED priority tinggi
→ RED

Else jika ada indikator YELLOW
→ YELLOW

Else
→ GREEN
```

Versi lanjutan dapat menggunakan weighted score.

---

# 28. Weighted Risk Score Opsional

Contoh:

```text
Risk Score =
Σ(indicator_weight × severity_score)
```

Mapping:

```text
GREEN  = 0
YELLOW = 1
RED    = 2
```

Risk score hanya menjadi pendukung.

---

# 29. Explainability

Setiap status harus memiliki penjelasan.

Contoh:

```text
Status:
RED

Alasan:
- KRS belum final setelah periode ditutup
- SKS tertinggal 20 dari target
- TA tidak memiliki aktivitas 16 hari
```

Jangan hanya menampilkan:

```text
Mahasiswa = MERAH
```

tanpa alasan.

---

# 30. Dashboard Mahasiswa

Widget:

```text
Status Saya
Total SKS
KRS Status
TA Progress
Sempro Status
Sidang Status
CPL/PLO Gap
Alert Aktif
```

Contoh:

```text
Status Akademik:
KUNING

Alert:
2

Penyebab:
- TA tidak aktif 10 hari
- KRS belum approved
```

---

# 31. Dashboard Dosen PA

Widget:

```text
Total Mahasiswa PA
Hijau
Kuning
Merah
Alert Baru
Belum Follow-up
Overdue Follow-up
```

---

# 32. Dashboard Admin/Kaprodi

Widget:

```text
Total Mahasiswa
Hijau
Kuning
Merah
Alert Aktif
Alert Tereskalasi
Mahasiswa TA Terlambat
Mahasiswa Belum Sempro
Mahasiswa Belum Sidang
```

---

# 33. Dashboard Dekan/WD

Agregat:

```text
Risk per Prodi
Trend Hijau/Kuning/Merah
Kelulusan Tepat Waktu
Progress TA
Alert Critical
```

---

# 34. Dashboard LPM

Fokus mutu:

```text
CPL Gap
PLO Gap
Trend Angkatan
Kompetensi Rendah
Data Agregat
```

---

# 35. Alert Generation

Flow:

```text
Scheduler / Event
↓
Evaluate Rule
↓
Status berubah?
↓
Generate Alert
↓
Assign Recipient
↓
Notification
↓
Follow-up
```

---

# 36. Duplicate Alert Prevention

Jangan membuat alert sama berulang setiap hari.

Gunakan:

```text
rule_id
+
mahasiswa_id
+
status OPEN
```

Jika alert aktif sudah ada:

```text
update existing alert
```

bukan membuat alert baru.

---

# 37. Alert Severity

```text
INFO
WARNING
CRITICAL
```

Mapping:

```text
GREEN
→ biasanya no alert / INFO

YELLOW
→ WARNING

RED
→ CRITICAL
```

---

# 38. Alert Recipient

Contoh:

```text
Mahasiswa
Dosen PA
Admin Prodi
Kaprodi
```

Recipient tergantung rule.

---

# 39. Alert Escalation

Contoh:

```text
Alert RED
↓
Dosen PA tidak follow-up 3 hari
↓
Escalate ke Kaprodi
```

Atau:

```text
Revision overdue 7 hari
↓
Dosen PA
↓
Kaprodi
```

---

# 40. Follow-up

Jenis tindak lanjut:

```text
CONTACT_STUDENT
ACADEMIC_COUNSELING
REQUEST_DOCUMENT
REQUEST_KRS_REVISION
TA_CONSULTATION
ESCALATE
OTHER
```

---

# 41. Follow-up Workflow

```text
OPEN
↓
ACKNOWLEDGED
↓
IN_FOLLOW_UP
↓
RESOLVED
↓
CLOSED
```

---

# 42. Mahasiswa Feedback

Mahasiswa dapat:

```text
Acknowledge alert
Melihat rekomendasi tindakan
Melihat status follow-up
```

Mahasiswa tidak dapat:

```text
menghapus alert
mengubah severity
menutup alert secara sepihak
```

---

# 43. Monitoring Event-Driven

Selain scheduler, M3 dapat menerima event.

Contoh:

```text
M4.KRS_FINALIZED
↓
Recalculate KRS indicator
```

```text
M7.TA_PROGRESS_UPDATED
↓
Recalculate TA indicator
```

```text
M1.SIDANG_RESULT_PUBLISHED
↓
Recalculate sidang indicator
```

```text
M6.CPL_UPDATED
↓
Recalculate CPL indicator
```

---

# 44. Scheduler-Based Monitoring

Scheduler diperlukan untuk kondisi berbasis waktu.

Contoh:

```text
no activity
deadline approaching
deadline overdue
semester age
pending follow-up
```

---

# 45. Scheduler Flow

```text
Get ACTIVE tenants
↓
For each tenant
    initialize tenant
    load active rules
    evaluate students
    generate/update alerts
    end tenancy
```

---

# 46. Queue

Untuk jumlah mahasiswa besar:

```text
EvaluateStudentMonitoringJob
EvaluateBatchMonitoringJob
GenerateAlertNotificationJob
GenerateMonitoringReportJob
```

Semua job membawa tenant context.

---

# 47. Monitoring Snapshot

Snapshot berguna untuk trend.

Contoh:

```text
2026-09-01
Hijau

2026-09-15
Kuning

2026-10-01
Merah
```

Dapat digunakan untuk:

```text
trend mahasiswa
trend angkatan
trend prodi
```

---

# 48. Kelulusan Tepat Waktu

M3 dapat memonitor indikator:

```text
semester berjalan
SKS tercapai
progress TA
Sempro
Sidang
```

Tujuannya:

```text
identifikasi mahasiswa yang berpotensi terlambat
```

Bukan memprediksi secara absolut.

---

# 49. Contoh Rule Kelulusan

```text
Semester >= 7
dan
SKS < threshold
dan
TA progress < threshold
→ YELLOW / RED
```

Rule final harus sesuai kebijakan prodi.

---

# 50. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M3-FR-001 | Sistem dapat mengelola monitoring rules. |
| M3-FR-002 | Rule dapat diaktif/nonaktifkan. |
| M3-FR-003 | Rule memiliki domain dan severity. |
| M3-FR-004 | Sistem dapat membaca KRS dari M4. |
| M3-FR-005 | Sistem dapat membaca SKS/progress akademik. |
| M3-FR-006 | Sistem dapat membaca data CPL/PLO dari M6. |
| M3-FR-007 | Sistem dapat membaca progress TA dari M7. |
| M3-FR-008 | Sistem dapat membaca status Sempro/Sidang dari M1. |
| M3-FR-009 | Sistem dapat mengevaluasi rule per mahasiswa. |
| M3-FR-010 | Sistem dapat menghasilkan status Hijau/Kuning/Merah. |
| M3-FR-011 | Sistem dapat menyimpan monitoring snapshot. |
| M3-FR-012 | Sistem dapat menyimpan indicator result. |
| M3-FR-013 | Sistem dapat menghasilkan alert. |
| M3-FR-014 | Sistem mencegah duplicate alert aktif. |
| M3-FR-015 | Sistem dapat mengirim notification. |
| M3-FR-016 | Dosen PA dapat melihat alert mahasiswa PA. |
| M3-FR-017 | Dosen PA dapat melakukan follow-up. |
| M3-FR-018 | Sistem dapat mengeskalasi alert. |
| M3-FR-019 | Kaprodi dapat melihat alert tereskalasi. |
| M3-FR-020 | Mahasiswa dapat melihat alert miliknya. |
| M3-FR-021 | Mahasiswa dapat acknowledge alert. |
| M3-FR-022 | Sistem dapat menampilkan explainability. |
| M3-FR-023 | Sistem dapat menampilkan trend. |
| M3-FR-024 | Sistem menyediakan dashboard Dosen PA. |
| M3-FR-025 | Sistem menyediakan dashboard Admin/Kaprodi. |
| M3-FR-026 | Sistem menyediakan dashboard pimpinan. |
| M3-FR-027 | Sistem menyediakan dashboard LPM sesuai scope. |
| M3-FR-028 | Sistem mendukung event-driven recalculation. |
| M3-FR-029 | Sistem mendukung scheduled recalculation. |
| M3-FR-030 | Semua aktivitas penting diaudit. |
| M3-FR-031 | Semua proses tenant-aware. |

---

# 51. Business Rules M3

| ID | Rule |
|---|---|
| M3-BR-001 | Status monitoring harus memiliki alasan yang dapat dijelaskan. |
| M3-BR-002 | Rule harus berasal dari konfigurasi yang tervalidasi. |
| M3-BR-003 | Mahasiswa hanya melihat monitoring miliknya sendiri. |
| M3-BR-004 | Dosen PA hanya melihat mahasiswa PA. |
| M3-BR-005 | Admin/Kaprodi hanya melihat data sesuai scope prodi. |
| M3-BR-006 | Alert yang sama tidak boleh diduplikasi selama masih aktif. |
| M3-BR-007 | Alert RED dapat memicu escalation sesuai konfigurasi. |
| M3-BR-008 | Manual override wajib memiliki alasan. |
| M3-BR-009 | Override tidak menghapus hasil rule asli. |
| M3-BR-010 | Perubahan rule harus diaudit. |
| M3-BR-011 | Rule version harus disimpan agar histori dapat dijelaskan. |
| M3-BR-012 | Tenant A tidak dapat membaca monitoring Tenant B. |
| M3-BR-013 | M3 tidak menentukan kelulusan/DO secara otomatis. |
| M3-BR-014 | Keputusan tindak lanjut tetap dilakukan role manusia yang berwenang. |

---

# 52. Permission M3

```text
view_own_monitoring
view_own_alert
acknowledge_own_alert

view_pa_monitoring
view_pa_alert
create_alert_followup
resolve_alert

view_prodi_monitoring
view_prodi_alert
escalate_alert

manage_monitoring_rule
activate_monitoring_rule
override_monitoring_status

view_monitoring_dashboard
view_monitoring_trend
view_quality_monitoring

export_monitoring_report
```

---

# 53. Role Mapping

## Mahasiswa

```text
view_own_monitoring
view_own_alert
acknowledge_own_alert
```

## Dosen PA

```text
view_pa_monitoring
view_pa_alert
create_alert_followup
resolve_alert
```

## Admin Prodi

```text
view_prodi_monitoring
view_prodi_alert
escalate_alert
manage_monitoring_rule
```

## Kaprodi

```text
view_prodi_monitoring
view_prodi_alert
view_monitoring_dashboard
view_monitoring_trend
override_monitoring_status
```

## Dekan / WD

```text
view_monitoring_dashboard
view_monitoring_trend
export_monitoring_report
```

## LPM

```text
view_quality_monitoring
view_monitoring_trend
```

---

# 54. Policy / Data Scope

```text
Mahasiswa
→ data sendiri.

Dosen PA
→ mahasiswa PA.

Admin Prodi
→ prodi scope.

Kaprodi
→ prodi sendiri.

Dekan/WD
→ tenant/fakultas.

LPM
→ agregat mutu sesuai scope.

Tenant
→ database tenant aktif.
```

---

# 55. Rule Configuration

Contoh:

```text
rule.code = TA_INACTIVE_14_DAYS
domain = TA
metric_key = ta.days_since_last_activity
operator = >
threshold_value = 14
severity = RED
priority = HIGH
```

---

# 56. Rule Versioning

Contoh:

```text
RULE-V1
threshold = 14

RULE-V2
threshold = 10
```

Snapshot lama tetap mereferensikan versi rule saat evaluasi.

---

# 57. Rule Management UI

Admin/Kaprodi berwenang dapat melihat:

```text
Code
Name
Domain
Threshold
Severity
Priority
Status
Version
Last Updated
```

Perubahan rule penting harus melalui permission.

---

# 58. Search dan Filter

Monitoring list:

```text
Search:
NIM
Nama

Filter:
Prodi
Angkatan
Semester
Status
Alert Severity
Domain
Dosen PA
TA Status
Sidang Status
```

---

# 59. Trend Analysis

Trend dapat ditampilkan:

```text
per mahasiswa
per angkatan
per prodi
per semester
```

Contoh:

```text
Semester 1: Hijau
Semester 2: Hijau
Semester 3: Kuning
Semester 4: Merah
```

---

# 60. Monitoring Summary

Contoh mahasiswa:

```text
Status:
KUNING

Indicators:
KRS          GREEN
SKS          GREEN
CPL          YELLOW
TA           YELLOW
SIDANG       NOT_APPLICABLE
```

---

# 61. Integrasi M4

M3 membaca:

```text
KRS status
SKS
semester
course history
```

Event:

```text
M4.KRS_SUBMITTED
M4.KRS_APPROVED
M4.KRS_FINALIZED
```

---

# 62. Integrasi M6

M3 membaca:

```text
CPL score
PLO score
competency gap
profile status
```

Event:

```text
M6.CPL_UPDATED
M6.PLO_UPDATED
M6.COMPETENCY_GAP_UPDATED
```

---

# 63. Integrasi M7

M3 membaca:

```text
progress_percent
last_activity
review pending
revision deadline
finalization status
```

Event:

```text
M7.TA_PROGRESS_UPDATED
M7.TA_REVISION_REQUESTED
M7.TA_FINALIZED
```

---

# 64. Integrasi M1

M3 membaca:

```text
registration
verification
schedule
result
revision
completion
```

Event:

```text
M1.REGISTRATION_SUBMITTED
M1.SCHEDULE_FINALIZED
M1.SIDANG_COMPLETED
M1.SIDANG_REVISION_CREATED
M1.SIDANG_RESULT_PUBLISHED
```

---

# 65. Integrasi Shared Services

## Audit

Mencatat:

```text
rule changed
monitoring recalculated
alert generated
alert acknowledged
follow-up created
alert escalated
alert resolved
override applied
```

## Notification

```text
alert baru
alert escalation
follow-up reminder
critical alert
```

## Queue

```text
batch evaluation
bulk notification
large report
```

## Scheduler

```text
daily evaluation
deadline checks
follow-up checks
escalation checks
```

---

# 66. Audit Events

```text
MONITORING_RULE_CREATED
MONITORING_RULE_UPDATED
MONITORING_RULE_ACTIVATED
MONITORING_RULE_DEACTIVATED

MONITORING_EVALUATED
MONITORING_STATUS_CHANGED

ALERT_CREATED
ALERT_ACKNOWLEDGED
ALERT_FOLLOWUP_CREATED
ALERT_ESCALATED
ALERT_RESOLVED

MONITORING_OVERRIDE_APPLIED
```

---

# 67. Internal Events M3

Diterbitkan:

```text
M3.MONITORING_UPDATED
M3.STATUS_CHANGED
M3.ALERT_CREATED
M3.ALERT_ESCALATED
M3.ALERT_RESOLVED
```

Diterima:

```text
M4.KRS_FINALIZED

M6.CPL_UPDATED
M6.PLO_UPDATED
M6.COMPETENCY_GAP_UPDATED

M7.TA_PROGRESS_UPDATED
M7.TA_FINALIZED

M1.SIDANG_RESULT_PUBLISHED
M1.SIDANG_REVISION_CREATED
```

---

# 68. Service Layer

```text
MonitoringRuleService
MonitoringEvaluationService
MonitoringSnapshotService
RiskClassificationService
AlertService
AlertEscalationService
AlertFollowupService
MonitoringTrendService
MonitoringDashboardService
```

---

# 69. Action Layer

```text
EvaluateStudentMonitoringAction
GenerateAlertAction
AcknowledgeAlertAction
CreateAlertFollowupAction
EscalateAlertAction
ResolveAlertAction
ApplyMonitoringOverrideAction
GenerateMonitoringSnapshotAction
```

---

# 70. Filament Resource

```text
MonitoringRuleResource
AlertResource
MonitoringSnapshotResource
```

---

# 71. Custom Pages

```text
MonitoringDashboardPage
StudentMonitoringPage
AlertDashboardPage
AlertFollowupPage
MonitoringTrendPage
QualityMonitoringPage
```

---

# 72. Livewire Components

```text
RiskStatusBadge
MonitoringIndicatorList
AlertTimeline
FollowupForm
StudentTrendChart
ProdiRiskSummary
TaProgressMonitor
CplGapMonitor
```

---

# 73. Queue Jobs

```text
EvaluateStudentMonitoringJob
EvaluateMonitoringBatchJob
GenerateAlertNotificationJob
GenerateMonitoringReportJob
```

Semua job tenant-aware.

---

# 74. Scheduler Tasks

```text
DailyMonitoringEvaluation
KrsDeadlineCheck
TaInactivityCheck
RevisionDeadlineCheck
AlertEscalationCheck
FollowupReminder
```

---

# 75. Notification Templates

```text
MONITORING_WARNING
MONITORING_CRITICAL
ALERT_ESCALATED
FOLLOWUP_REMINDER
REVISION_OVERDUE
KRS_INCOMPLETE
TA_INACTIVE
```

---

# 76. Example Alert

```text
Title:
Progress Tugas Akhir Perlu Perhatian

Severity:
WARNING

Message:
Tidak terdapat aktivitas dokumen TA selama 10 hari.

Recommendation:
Hubungi dosen pembimbing dan jadwalkan konsultasi.
```

---

# 77. Error Code

```text
MONITORING_RULE_NOT_FOUND
MONITORING_RULE_INVALID
MONITORING_METRIC_UNAVAILABLE

ALERT_NOT_FOUND
ALERT_ALREADY_RESOLVED
ALERT_ACCESS_DENIED

FOLLOWUP_ACCESS_DENIED
ESCALATION_NOT_ALLOWED

CROSS_TENANT_ACCESS_DENIED
```

---

# 78. Tenant Isolation

Contoh:

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.monitoring_snapshots
→ sifak_tenant_fasilkom.alerts

feb.sifakueu.test
→ sifak_tenant_feb.monitoring_snapshots
→ sifak_tenant_feb.alerts
```

Tidak ada evaluasi lintas tenant pada request normal.

---

# 79. Security

M3 harus menguji:

```text
Tenant isolation
Role bypass
Permission bypass
IDOR
Unauthorized rule modification
Unauthorized alert resolution
Unauthorized override
Mass export access
SQL injection
XSS
CSRF
```

---

# 80. QA Test Scenario — Rule Evaluation

```text
M3-TC-001
Rule aktif dan metric memenuhi GREEN
Expected:
indicator GREEN.

M3-TC-002
Metric melewati warning threshold
Expected:
YELLOW.

M3-TC-003
Metric melewati critical threshold
Expected:
RED.
```

---

# 81. QA Test Scenario — Alert

```text
M3-TC-010
Indicator RED
Expected:
alert dibuat.

M3-TC-011
Alert sama masih OPEN
Expected:
tidak membuat duplicate alert.

M3-TC-012
Indicator kembali normal
Expected:
alert dapat diproses ke resolved sesuai rule.
```

---

# 82. QA Test Scenario — Dosen PA

```text
M3-TC-020
Dosen PA melihat mahasiswa PA
Expected:
allowed.

M3-TC-021
Dosen PA melihat mahasiswa bukan scope
Expected:
403.

M3-TC-022
Dosen PA membuat follow-up
Expected:
history tersimpan.
```

---

# 83. QA Test Scenario — Escalation

```text
M3-TC-030
Alert RED tidak ditindaklanjuti sesuai SLA
Expected:
escalate ke Kaprodi.

M3-TC-031
Alert sudah resolved
Expected:
tidak dieskalasi.
```

---

# 84. QA Test Scenario — Integration

```text
M3-TC-040
M4 KRS finalized
Expected:
KRS indicator recalculated.

M3-TC-041
M7 TA progress berubah
Expected:
TA indicator recalculated.

M3-TC-042
M6 CPL gap berubah
Expected:
CPL indicator recalculated.

M3-TC-043
M1 revisi overdue
Expected:
alert dapat dibuat.
```

---

# 85. QA Test Scenario — Tenant Isolation

```text
M3-TC-050
FASILKOM membaca alert FEB
Expected:
ditolak.

M3-TC-051
Queue evaluation FEB
Expected:
hanya database FEB dipakai.

M3-TC-052
Kaprodi FASILKOM akses dashboard FEB
Expected:
ditolak.
```

---

# 86. Acceptance Criteria M3

M3 dinyatakan siap apabila:

- [ ] monitoring rule dapat dibuat;
- [ ] monitoring rule dapat diaktif/nonaktifkan;
- [ ] rule version tersimpan;
- [ ] KRS data dapat dibaca dari M4;
- [ ] data CPL/PLO dapat dibaca dari M6;
- [ ] TA progress dapat dibaca dari M7;
- [ ] status sidang dapat dibaca dari M1;
- [ ] rule dapat dievaluasi per mahasiswa;
- [ ] status Hijau/Kuning/Merah dapat dihasilkan;
- [ ] explainability tersedia;
- [ ] snapshot dapat disimpan;
- [ ] alert dapat dibuat;
- [ ] duplicate alert dapat dicegah;
- [ ] mahasiswa dapat melihat alert sendiri;
- [ ] Dosen PA dapat melihat mahasiswa PA;
- [ ] follow-up dapat dicatat;
- [ ] escalation dapat dilakukan;
- [ ] override terbatas dapat dilakukan;
- [ ] dashboard mahasiswa tersedia;
- [ ] dashboard Dosen PA tersedia;
- [ ] dashboard Admin/Kaprodi tersedia;
- [ ] dashboard pimpinan tersedia;
- [ ] dashboard LPM tersedia;
- [ ] trend dapat ditampilkan;
- [ ] notification berjalan;
- [ ] queue tenant-aware;
- [ ] scheduler tenant-aware;
- [ ] audit log berjalan;
- [ ] tenant isolation lulus;
- [ ] authorization test lulus.

---

# 87. Definition of Done M3

M3 dianggap selesai apabila:

1. migration selesai;
2. model dan relation selesai;
3. permission tersedia;
4. policy tersedia;
5. service layer tersedia;
6. action layer tersedia;
7. rule engine berjalan;
8. risk classification berjalan;
9. snapshot berjalan;
10. alert generation berjalan;
11. duplicate prevention berjalan;
12. notification berjalan;
13. follow-up berjalan;
14. escalation berjalan;
15. override berjalan;
16. explainability tersedia;
17. trend tersedia;
18. Mahasiswa Panel terintegrasi;
19. Dosen PA Panel terintegrasi;
20. Admin Panel terintegrasi;
21. Pimpinan Panel terintegrasi;
22. M4 integration berjalan;
23. M6 integration berjalan;
24. M7 integration berjalan;
25. M1 integration berjalan;
26. Queue tenant-aware;
27. Scheduler tenant-aware;
28. audit log berjalan;
29. functional test lulus;
30. authorization test lulus;
31. integration test lulus;
32. tenant isolation test lulus;
33. security test utama lulus;
34. dokumentasi internal tersedia.

---

# 88. Urutan Implementasi M3

Urutan yang disarankan:

```text
1. Monitoring Rule
↓
2. Indicator Definition
↓
3. M4 Data Adapter
↓
4. M6 Data Adapter
↓
5. M7 Data Adapter
↓
6. M1 Data Adapter
↓
7. Evaluation Engine
↓
8. Risk Classification
↓
9. Snapshot
↓
10. Explainability
↓
11. Alert Generation
↓
12. Duplicate Prevention
↓
13. Notification
↓
14. Follow-up
↓
15. Escalation
↓
16. Override
↓
17. Dashboard Mahasiswa
↓
18. Dashboard Dosen PA
↓
19. Dashboard Admin/Kaprodi
↓
20. Dashboard Pimpinan/LPM
↓
21. Trend
↓
22. Scheduler
↓
23. Queue
↓
24. QA
```

---

# 89. Sprint Rekomendasi

## Sprint M3-1 — Monitoring Foundation

```text
Rule
Indicator
Permission
Policy
Audit
```

Output:

```text
Rule monitoring dapat dikonfigurasi.
```

---

## Sprint M3-2 — Data Integration

```text
M4 Adapter
M6 Adapter
M7 Adapter
M1 Adapter
```

Output:

```text
M3 dapat membaca indikator lintas modul.
```

---

## Sprint M3-3 — Evaluation & Risk

```text
Evaluation Engine
Green/Yellow/Red
Snapshot
Explainability
```

Output:

```text
Status mahasiswa dapat dihitung.
```

---

## Sprint M3-4 — Alert

```text
Alert Generation
Duplicate Prevention
Notification
Acknowledge
```

Output:

```text
Alert aktif berjalan.
```

---

## Sprint M3-5 — Follow-up & Escalation

```text
Follow-up
Escalation
Override
History
```

Output:

```text
Tindak lanjut dapat dilacak.
```

---

## Sprint M3-6 — Dashboard & Trend

```text
Mahasiswa Dashboard
Dosen PA Dashboard
Admin/Kaprodi Dashboard
Pimpinan Dashboard
LPM Dashboard
Trend
```

Output:

```text
Monitoring dapat digunakan operasional.
```

---

## Sprint M3-7 — Scheduler, Queue & QA

```text
Daily Evaluation
Deadline Check
Batch Queue
Security
Tenant Isolation
Regression
```

Output:

```text
M3 siap untuk production/pilot.
```

---

# 90. Output Akhir M3

Setelah M3 selesai:

```text
Mahasiswa memiliki status monitoring
+
Dosen PA dapat melihat mahasiswa berisiko
+
Kaprodi dapat melihat kondisi prodi
+
Pimpinan dapat melihat trend fakultas
+
Alert dapat dibuat otomatis
+
Alert dapat ditindaklanjuti
+
Alert dapat dieskalasi
+
Progress akademik dapat ditelusuri
+
M3 dapat membantu pemantauan kelulusan tepat waktu
```

---

# 91. Hubungan dengan Roadmap Berikutnya

Setelah:

```text
M5 ✓
M4 ✓
M7 ✓
M1 ✓
M6 ✓
M3 ✓
```

modul berikutnya adalah:

```text
M2 — Surat Menyurat
```

M2 relatif independen dan menjadi modul bisnis terakhir sebelum:

```text
Integrasi End-to-End
↓
NFR
↓
Security
↓
QA
↓
FASILKOM Pilot
```

---

# 92. Kesimpulan

M3 bukan hanya dashboard warna.

M3 terdiri dari empat lapisan:

```text
DATA LAYER
├── M4
├── M6
├── M7
└── M1

RULE LAYER
├── Indicator
├── Threshold
├── Severity
└── Version

ALERT LAYER
├── Generate
├── Notify
├── Follow-up
├── Escalate
└── Resolve

MONITORING LAYER
├── Hijau/Kuning/Merah
├── Dashboard
├── Trend
└── Explainability
```

Dengan struktur ini, M3 menjadi pusat monitoring akademik lintas modul tanpa menggantikan keputusan Dosen PA, Kaprodi, atau pimpinan.

---

**SIFAK — M3 Monitoring & Alert — Detailed Specification v1.0**
