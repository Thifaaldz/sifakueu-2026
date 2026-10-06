# SHARED SERVICES SPECIFICATION
## Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Tahap Implementasi:** Fase 3 — Shared Services  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Tujuan:** Menetapkan layanan bersama yang dapat digunakan ulang oleh seluruh Modul M1–M7 tanpa menduplikasi logika pada setiap modul.

---

# 1. Tujuan Shared Services

Shared Services adalah layanan inti yang dipakai bersama oleh seluruh modul SIFAK.

Layanan bersama yang dibutuhkan:

```text
Audit Log
Notification Service
Storage Service
Workflow / State Management
Queue Service
Scheduler Service
File Service
```

Shared Services bukan modul bisnis baru dan tidak dihitung sebagai M8.

Posisinya adalah lapisan pendukung:

```text
                    SIFAK
                      │
              Shared Services
                      │
     ┌────────────────┼────────────────┐
     │                │                │
 Audit/Log       Notification       Storage/File
     │                │                │
 Workflow          Queue          Scheduler
     │                │                │
     └────────────────┼────────────────┘
                      │
          M1 M2 M3 M4 M5 M6 M7
```

Tujuan utamanya:

- menghindari duplikasi kode;
- menjaga konsistensi proses antar-modul;
- memastikan tenant isolation;
- menyediakan audit yang seragam;
- mendukung proses asynchronous;
- mempermudah maintenance;
- mempermudah QA dan observability;
- mendukung pengembangan sistem yang modular.

---

# 2. Posisi Shared Services dalam Arsitektur

```text
CENTRAL PLATFORM
│
├── Tenant Management
├── Provisioning
├── Platform Audit
└── Global Configuration
        │
        ▼
TENANT CONTEXT
│
├── Authentication / RBAC
├── Master Data
├── Shared Services
│   ├── Audit Log
│   ├── Notification
│   ├── Storage
│   ├── Workflow / State
│   ├── Queue
│   ├── Scheduler
│   └── File Service
│
├── M1 Sidang Sempro & TA
├── M2 Surat Menyurat
├── M3 Monitoring & Alert
├── M4 KRS & Penjadwalan
├── M5 Profiling Dosen
├── M6 Profiling Mahasiswa
└── M7 Dokumen TA & Repositori
```

---

# 3. Prinsip Implementasi

Shared Services mengikuti prinsip berikut:

## 3.1 Reusable

Satu service digunakan banyak modul.

Contoh:

```text
NotificationService
→ M1
→ M2
→ M3
→ M4
→ M5
→ M6
→ M7
```

Tidak dibuat:

```text
M1NotificationService
M2NotificationService
M3NotificationService
```

jika kebutuhan dasarnya sama.

---

## 3.2 Tenant-Aware

Setiap service wajib mengetahui tenant aktif.

Minimal context:

```text
tenant_id
tenant_slug
database_name
user_id
role
```

Service tidak boleh memproses data tenant lain.

---

## 3.3 Auditable

Aksi penting harus dapat dilacak:

```text
siapa
melakukan apa
pada resource apa
kapan
dari tenant mana
perubahan apa
```

---

## 3.4 Idempotent untuk Background Process

Queue job dan scheduler harus aman ketika dijalankan ulang.

Contoh:

```text
Generate PDF
Send Notification
Run Alert Evaluation
Backup Tenant
```

tidak boleh membuat duplikasi yang tidak diinginkan.

---

## 3.5 Fail-Safe

Kegagalan satu layanan pendukung tidak boleh otomatis merusak data utama.

Contoh:

```text
KRS berhasil disetujui
↓
Notification gagal
```

Status KRS tidak boleh rollback hanya karena email gagal, kecuali memang requirement bisnis menyatakan transaksi harus atomic.

---

# 4. Daftar Shared Services

| Kode | Shared Service | Fungsi |
|---|---|---|
| SS01 | Audit Log Service | Mencatat aktivitas dan perubahan data |
| SS02 | Notification Service | Mengirim notifikasi in-app, email, WA opsional |
| SS03 | Storage Service | Mengatur penyimpanan file tenant |
| SS04 | Workflow / State Service | Mengelola status dan transisi proses |
| SS05 | Queue Service | Menjalankan pekerjaan background |
| SS06 | Scheduler Service | Menjalankan proses terjadwal |
| SS07 | File Service | Upload, validasi, versioning, generate, download file |

---

# 5. SS01 — Audit Log Service

## 5.1 Tujuan

Audit Log Service mencatat seluruh aktivitas penting yang terjadi pada sistem.

Audit log diperlukan untuk:

- keamanan;
- traceability;
- kebutuhan QA;
- investigasi error;
- kebutuhan akreditasi;
- monitoring perubahan data;
- tenant governance.

---

## 5.2 Data Audit

Minimal:

| Field | Keterangan |
|---|---|
| id | ID log |
| tenant_id | Tenant sumber aktivitas |
| user_id | User yang melakukan aksi |
| role | Role saat aksi dilakukan |
| action | Jenis aksi |
| module | Modul asal |
| resource_type | Jenis resource |
| resource_id | ID record |
| old_values | Nilai sebelum perubahan |
| new_values | Nilai setelah perubahan |
| ip_address | IP user |
| user_agent | Browser/device |
| request_id | ID request |
| created_at | Waktu kejadian |

---

## 5.3 Contoh Audit

```text
tenant_id:
FASILKOM

module:
M4

actor:
admin_prodi

action:
UPDATE_JADWAL

resource:
jadwal_kuliah

resource_id:
JDK-001

old:
Senin 08:00

new:
Senin 10:00
```

---

## 5.4 Event yang Wajib Diaudit

Minimal:

```text
LOGIN
LOGOUT
FAILED_LOGIN

CREATE
UPDATE
DELETE
RESTORE

APPROVE
REJECT
VERIFY
ASSIGN

UPLOAD
DOWNLOAD
DELETE_FILE

CHANGE_ROLE
CHANGE_PERMISSION

CREATE_TENANT
SUSPEND_TENANT
REACTIVATE_TENANT

RUN_MIGRATION
RUN_BACKUP
RUN_RESTORE
```

---

## 5.5 Modul yang Menggunakan

| Modul | Contoh |
|---|---|
| M1 | perubahan jadwal sidang, penguji, hasil |
| M2 | approval surat, nomor surat |
| M3 | tindak lanjut alert |
| M4 | KRS approval, perubahan jadwal |
| M5 | perubahan profiling dosen |
| M6 | perubahan data profiling mahasiswa |
| M7 | komentar, approval bab, finalisasi dokumen |

---

## 5.6 Business Rule

```text
SS-BR-001
Audit log tidak boleh dapat diubah oleh user tenant biasa.

SS-BR-002
Audit log harus menyimpan tenant context.

SS-BR-003
Audit log tidak boleh menyimpan password atau secret.

SS-BR-004
Audit log untuk perubahan penting harus menyimpan old dan new value bila relevan.

SS-BR-005
Penghapusan audit log mengikuti retention policy dan hanya dapat dilakukan oleh role berwenang.
```

---

# 6. SS02 — Notification Service

## 6.1 Tujuan

Mengelola pengiriman informasi kepada user berdasarkan event sistem.

Channel:

```text
In-App
Email
WhatsApp (opsional)
```

---

## 6.2 Contoh Trigger

### M1

```text
Sidang dijadwalkan
→ Mahasiswa
→ Pembimbing
→ Penguji
```

### M2

```text
Surat disetujui
→ Pemohon
```

### M3

```text
Alert akademik dibuat
→ Mahasiswa
→ Dosen PA
```

### M4

```text
Jadwal kuliah berubah
→ Mahasiswa
→ Dosen
```

### M7

```text
Pembimbing memberi komentar
→ Mahasiswa
```

---

## 6.3 Struktur Data Notification

| Field | Keterangan |
|---|---|
| id | ID notifikasi |
| tenant_id | Tenant |
| user_id | Penerima |
| channel | in_app/email/whatsapp |
| type | Tipe |
| title | Judul |
| message | Pesan |
| reference_type | Resource terkait |
| reference_id | ID resource |
| status | pending/sent/failed/read |
| sent_at | Waktu kirim |
| read_at | Waktu dibaca |
| failed_reason | Alasan gagal |

---

## 6.4 Status

```text
PENDING
↓
QUEUED
↓
SENT
```

Jika gagal:

```text
QUEUED
↓
FAILED
↓
RETRY
```

---

## 6.5 Business Rule

```text
SS-BR-006
Notification harus membawa tenant context.

SS-BR-007
Notification gagal tidak boleh mengubah status bisnis utama.

SS-BR-008
Notifikasi penting dapat memiliki retry policy.

SS-BR-009
User hanya dapat melihat notifikasi miliknya.

SS-BR-010
WhatsApp bersifat opsional sesuai konfigurasi tenant/platform.
```

---

# 7. SS03 — Storage Service

## 7.1 Tujuan

Mengelola tempat penyimpanan file agar terisolasi per tenant.

Storage dapat menggunakan:

```text
Local Laravel Storage
S3-Compatible Storage
MinIO
```

---

## 7.2 Struktur Storage

```text
tenants/
├── fasilkom/
│   ├── profile/
│   ├── surat/
│   ├── sidang/
│   ├── ta/
│   ├── repository/
│   └── temp/
│
└── feb/
    ├── profile/
    ├── surat/
    ├── sidang/
    ├── ta/
    ├── repository/
    └── temp/
```

---

## 7.3 Storage Namespace

Semua path harus dibuat dari tenant context.

Contoh:

```text
tenant:
fasilkom

path:
tenants/fasilkom/ta/2026/...
```

Dilarang membuat path hanya berdasarkan input frontend.

---

## 7.4 Business Rule

```text
SS-BR-011
File tenant A tidak boleh dapat diakses tenant B.

SS-BR-012
Path storage wajib tenant-aware.

SS-BR-013
File sensitif tidak disimpan sebagai public file tanpa authorization.

SS-BR-014
File temporary harus memiliki cleanup policy.

SS-BR-015
File final TA harus memiliki metadata dan histori.
```

---

# 8. SS04 — Workflow / State Management

## 8.1 Tujuan

Mengelola proses yang memiliki banyak status dan transisi.

Digunakan terutama oleh:

```text
M1 Sidang
M2 Surat
M4 KRS
M7 Dokumen TA
```

---

## 8.2 Prinsip

Jangan menggunakan satu boolean:

```text
approved = true
```

untuk proses kompleks.

Gunakan status yang jelas.

---

# 9. Workflow M1 — Sidang

Contoh state:

```text
DRAFT
↓
DIAJUKAN
↓
VERIFIKASI
↓
TERVERIFIKASI
↓
PLOTTING
↓
TERJADWAL
↓
DILAKSANAKAN
↓
REVISI
↓
SELESAI
```

Alternative:

```text
VERIFIKASI
↓
DITOLAK
```

---

## 9.1 Transition Rule

| Dari | Ke | Aktor |
|---|---|---|
| DRAFT | DIAJUKAN | Mahasiswa |
| DIAJUKAN | VERIFIKASI | Sistem/Admin |
| VERIFIKASI | TERVERIFIKASI | Admin |
| VERIFIKASI | DITOLAK | Admin |
| TERVERIFIKASI | PLOTTING | Admin |
| PLOTTING | TERJADWAL | Admin/System |
| TERJADWAL | DILAKSANAKAN | Admin |
| DILAKSANAKAN | REVISI | Penguji/Admin |
| REVISI | SELESAI | Pembimbing/Admin |

---

# 10. Workflow M2 — Surat

```text
DRAFT
↓
DIAJUKAN
↓
VERIFIKASI
↓
APPROVAL
↓
DISETUJUI
↓
NOMOR_SURAT
↓
GENERATED
↓
ARSIP
```

Reject:

```text
VERIFIKASI
↓
REVISI_PEMOHON
```

---

# 11. Workflow M4 — KRS

```text
DRAFT
↓
DIAJUKAN
↓
MENUNGGU_PA
↓
DISETUJUI
↓
FINAL
```

Reject:

```text
MENUNGGU_PA
↓
DITOLAK
↓
DRAFT
```

---

# 12. Workflow M7 — Dokumen TA

Level dokumen:

```text
DRAFT
↓
IN_PROGRESS
↓
REVIEW
↓
REVISION
↓
APPROVED
↓
FINAL
↓
REPOSITORY
```

Level bab:

```text
DRAFT
↓
SUBMITTED
↓
REVIEWED
↓
REVISION
↓
APPROVED
```

---

# 13. Workflow Provisioning Tenant

```text
DRAFT
↓
PROVISIONING
↓
ACTIVE
```

Jika gagal:

```text
PROVISIONING
↓
PROVISIONING_FAILED
↓
RETRY
```

---

# 14. Aturan Workflow

```text
SS-BR-016
Transisi status hanya boleh dilakukan oleh role yang memiliki permission.

SS-BR-017
Transisi tidak valid harus ditolak.

SS-BR-018
Setiap transisi penting harus diaudit.

SS-BR-019
Workflow harus tenant-aware.

SS-BR-020
Status final tidak boleh kembali ke status awal tanpa mekanisme resmi.
```

---

# 15. SS05 — Queue Service

## 15.1 Tujuan

Menjalankan proses yang tidak perlu dilakukan langsung pada request user.

Contoh:

```text
Generate PDF
Send Email
Send WhatsApp
Compile DOCX
Generate berita acara
Run bulk notification
Run import
Process file
Calculate recommendation
```

---

## 15.2 Queue Job

Contoh:

```text
SendNotificationJob
GenerateSidangPdfJob
GenerateSuratPdfJob
CompileTaDocumentJob
ImportMahasiswaJob
ImportDosenJob
CalculateRecommendationJob
GenerateReportJob
```

---

## 15.3 Tenant Context pada Queue

Setiap job minimal membawa:

```text
tenant_id
```

Flow:

```text
Job dibuat di FASILKOM
↓
tenant_id = fasilkom
↓
Queue Worker
↓
Initialize tenant FASILKOM
↓
Switch database
↓
Run job
↓
End tenant context
```

---

## 15.4 Queue Status

```text
PENDING
PROCESSING
COMPLETED
FAILED
RETRYING
```

---

## 15.5 Retry

Contoh:

```text
tries:
3

backoff:
60 seconds
```

Nilai aktual dapat disesuaikan per job.

---

## 15.6 Business Rule

```text
SS-BR-021
Queue job wajib membawa tenant context jika berhubungan dengan tenant.

SS-BR-022
Queue worker tidak boleh menggunakan tenant context dari job sebelumnya.

SS-BR-023
Failed job harus tercatat.

SS-BR-024
Job yang aman di-retry harus idempotent.

SS-BR-025
Job kritis harus memiliki monitoring.
```

---

# 16. SS06 — Scheduler Service

## 16.1 Tujuan

Menjalankan proses secara berkala.

Contoh:

```text
Evaluasi alert
Cleanup temp file
Backup
Reminder
Sinkronisasi data
Recalculate monitoring
Check overdue TA
Check alert escalation
```

---

## 16.2 Jadwal yang Dibutuhkan

| Proses | Contoh Frekuensi |
|---|---|
| Evaluasi alert mahasiswa | Harian |
| Eskalasi alert | Harian |
| Reminder sidang | Harian |
| Reminder revisi TA | Harian |
| Cleanup temp file | Harian/Mingguan |
| Backup tenant | Harian |
| Recalculate dashboard | Sesuai kebutuhan |
| Queue health check | Berkala |

---

## 16.3 Tenant-Aware Scheduler

Flow:

```text
Scheduler
↓
Get ACTIVE tenants
↓
For each tenant
    initialize tenant
    run task
    end tenancy
```

Contoh:

```text
FASILKOM
→ evaluate alerts

FEB
→ evaluate alerts

FIKES
→ evaluate alerts
```

---

## 16.4 Business Rule

```text
SS-BR-026
Scheduler hanya memproses tenant ACTIVE.

SS-BR-027
Scheduler wajib menginisialisasi tenant sebelum query tenant.

SS-BR-028
Kegagalan tenant A tidak boleh menghentikan tenant B.

SS-BR-029
Scheduler run harus dapat dimonitor.

SS-BR-030
Task yang menghasilkan perubahan penting harus diaudit.
```

---

# 17. SS07 — File Service

## 17.1 Tujuan

Mengelola lifecycle file dari upload sampai penyimpanan final.

File Service berbeda dari Storage Service.

```text
Storage Service
→ tempat file disimpan

File Service
→ aturan bagaimana file diupload, divalidasi, diproses, diberi metadata, di-versioning, dan diakses
```

---

## 17.2 Fungsi File Service

```text
Upload
Validation
Rename
Metadata
Versioning
Preview
Download
Generate
Compile
Archive
Delete / Soft Delete
```

---

# 18. Validasi File

Minimal:

```text
extension
MIME type
file size
filename
authorization
tenant path
```

Contoh tipe:

```text
PDF
DOCX
JPG
JPEG
PNG
XLSX
CSV
```

Tidak semua modul menerima seluruh tipe file.

---

# 19. File M1

Contoh:

```text
berkas pendaftaran
berita acara
hasil sidang
revisi
```

---

# 20. File M2

Contoh:

```text
lampiran surat
template surat
surat hasil generate
QR verification
```

---

# 21. File M7

Contoh:

```text
Bab 1
Bab 2
Bab 3
Bab 4
Bab 5
Daftar Pustaka
Dokumen Final PDF
Dokumen Final DOCX
```

---

# 22. Metadata File

| Field | Keterangan |
|---|---|
| id | ID |
| tenant_id | Tenant |
| owner_type | Jenis pemilik |
| owner_id | ID pemilik |
| module | Modul |
| original_name | Nama asli |
| stored_name | Nama storage |
| path | Lokasi |
| disk | local/s3/minio |
| mime_type | MIME |
| size | Size |
| checksum | Hash/checksum |
| version | Versi |
| status | temp/active/final/archived |
| uploaded_by | User |
| created_at | Waktu |

---

# 23. Versioning File

Terutama digunakan untuk M7.

Contoh:

```text
Bab 1 v1
↓
Bab 1 v2
↓
Bab 1 v3
↓
Approved
```

Versi lama tidak langsung dihapus.

---

# 24. Document Generation

Shared File Service dapat digunakan oleh:

```text
M1
→ Berita Acara

M2
→ Surat

M7
→ Kompilasi TA
```

Tool:

```text
DomPDF
Browsershot
PHPWord
LibreOffice Headless
```

---

# 25. Shared Service Dependency per Modul

| Modul | Audit | Notification | Storage | Workflow | Queue | Scheduler | File |
|---|---|---|---|---|---|---|---|
| M1 | Ya | Ya | Ya | Ya | Ya | Ya | Ya |
| M2 | Ya | Ya | Ya | Ya | Ya | Opsional | Ya |
| M3 | Ya | Ya | Tidak utama | Ya | Ya | Ya | Tidak utama |
| M4 | Ya | Ya | Tidak utama | Ya | Ya | Ya | Tidak utama |
| M5 | Ya | Ya | Ya | Tidak utama | Ya | Ya | Ya |
| M6 | Ya | Ya | Ya | Tidak utama | Ya | Ya | Ya |
| M7 | Ya | Ya | Ya | Ya | Ya | Ya | Ya |

---

# 26. Event Internal dan Shared Services

Contoh:

```text
KRS.approved
↓
Audit Log
↓
Notification
↓
Queue
↓
M4/M5 processing
```

Contoh:

```text
TA.bab_disimpan
↓
File Service
↓
Storage Service
↓
Audit Log
↓
M3 update progress
```

Contoh:

```text
Sidang.scheduled
↓
Audit Log
↓
Queue
↓
Notification
↓
Mahasiswa + Dosen
```

---

# 27. Struktur Folder Laravel yang Disarankan

Contoh:

```text
app/
├── Services/
│   ├── Audit/
│   │   └── AuditService.php
│   ├── Notification/
│   │   └── NotificationService.php
│   ├── Storage/
│   │   └── TenantStorageService.php
│   ├── Workflow/
│   │   └── WorkflowService.php
│   └── File/
│       └── FileService.php
│
├── Jobs/
│   ├── SendNotificationJob.php
│   ├── GenerateDocumentJob.php
│   └── ProcessImportJob.php
│
├── Console/
│   └── Commands/
│
├── Events/
├── Listeners/
├── Policies/
└── Models/
```

Struktur aktual dapat disesuaikan dengan gaya modular yang digunakan.

---

# 28. Database Shared Services

Contoh tabel:

```text
audit_logs
notifications
stored_files
file_versions
workflow_histories
failed_jobs
jobs
scheduled_task_logs
```

Jika Laravel Queue menggunakan database driver, tabel queue mengikuti kebutuhan Laravel.

---

# 29. Audit Log Table

Contoh:

```text
audit_logs
├── id
├── tenant_id
├── user_id
├── module
├── action
├── resource_type
├── resource_id
├── old_values
├── new_values
├── ip_address
├── user_agent
├── request_id
└── created_at
```

---

# 30. Workflow History Table

```text
workflow_histories
├── id
├── tenant_id
├── workflow_type
├── resource_id
├── from_state
├── to_state
├── action
├── actor_id
├── note
└── created_at
```

---

# 31. Stored File Table

```text
stored_files
├── id
├── tenant_id
├── module
├── owner_type
├── owner_id
├── original_name
├── stored_name
├── disk
├── path
├── mime_type
├── size
├── checksum
├── version
├── status
├── uploaded_by
└── created_at
```

---

# 32. Scheduled Task Log

```text
scheduled_task_logs
├── id
├── tenant_id
├── task_name
├── started_at
├── finished_at
├── status
├── message
└── created_at
```

---

# 33. Permission Shared Services

Contoh:

```text
view_audit_log
view_own_notifications
manage_notification_template
download_file
upload_file
delete_file
restore_file
view_file_history
retry_failed_job
view_scheduler_log
manage_workflow
```

Hak permission mengikuti role.

---

# 34. Akses Aktor

## Super Admin

Boleh:

```text
view platform audit
view tenant health
view failed provisioning
view system queue/scheduler status
```

Tidak otomatis boleh:

```text
membaca file akademik tenant
```

---

## Admin Tenant

Boleh:

```text
view tenant audit sesuai permission
manage tenant notification configuration
view tenant storage usage
```

---

## Admin Prodi / Fakultas

Boleh:

```text
melihat audit operasional sesuai scope
menggunakan file service
melihat notification status tertentu
```

---

## Mahasiswa / Dosen

Boleh:

```text
melihat notifikasi sendiri
upload/download file sesuai resource
melihat file sesuai policy
```

---

# 35. Notification Template

Template sebaiknya terpusat.

Contoh:

```text
SIDANG_SCHEDULED
SURAT_APPROVED
KRS_APPROVED
KRS_REJECTED
ALERT_CREATED
TA_REVIEWED
TA_APPROVED
```

Setiap template:

```text
code
title
body
available_channels
status
```

---

# 36. Example Notification

```text
Code:
SIDANG_SCHEDULED

Title:
Jadwal Sidang Telah Ditetapkan

Message:
Sidang Anda telah dijadwalkan pada {{tanggal}}
pukul {{waktu}} di ruang {{ruang}}.
```

---

# 37. Error Handling

Setiap shared service harus menggunakan error yang konsisten.

Contoh:

```text
TENANT_CONTEXT_MISSING
FILE_TOO_LARGE
FILE_TYPE_NOT_ALLOWED
WORKFLOW_TRANSITION_INVALID
NOTIFICATION_FAILED
QUEUE_JOB_FAILED
STORAGE_UNAVAILABLE
```

---

# 38. Observability

Minimal log:

```text
tenant_id
request_id
user_id
module
service
action
status
duration
error
timestamp
```

Contoh:

```text
tenant=fasilkom
service=NotificationService
action=send
type=SIDANG_SCHEDULED
status=failed
```

---

# 39. Security

Shared Services harus memperhatikan:

```text
Tenant isolation
RBAC
Policy
File authorization
Signed/temporary download URL jika diperlukan
MIME validation
Upload size limit
Path traversal prevention
Sensitive data masking
Audit integrity
Queue tenant leakage prevention
```

---

# 40. Test Scenario — Audit Log

```text
SS-TC-001
Admin mengubah jadwal
Expected:
audit record tersimpan.

SS-TC-002
Mahasiswa mencoba mengubah audit
Expected:
403 Forbidden.

SS-TC-003
Tenant FEB membaca audit FASILKOM
Expected:
tidak dapat diakses.
```

---

# 41. Test Scenario — Notification

```text
SS-TC-010
KRS disetujui
Expected:
notifikasi mahasiswa dibuat.

SS-TC-011
Email gagal
Expected:
status notification FAILED
dan dapat di-retry.

SS-TC-012
FEB membuka notification FASILKOM
Expected:
tidak dapat diakses.
```

---

# 42. Test Scenario — Storage/File

```text
SS-TC-020
Mahasiswa upload file PDF valid
Expected:
berhasil.

SS-TC-021
Upload extension tidak valid
Expected:
ditolak.

SS-TC-022
FEB mencoba membuka file FASILKOM
Expected:
ditolak.

SS-TC-023
Path traversal attempt
Expected:
ditolak.
```

---

# 43. Test Scenario — Workflow

```text
SS-TC-030
KRS DRAFT → DIAJUKAN
Expected:
berhasil.

SS-TC-031
KRS DRAFT → FINAL langsung
Expected:
ditolak.

SS-TC-032
Role tidak berwenang approve
Expected:
403.
```

---

# 44. Test Scenario — Queue

```text
SS-TC-040
Job FASILKOM dijalankan
Expected:
menggunakan DB FASILKOM.

SS-TC-041
Job gagal
Expected:
masuk failed job / retry.

SS-TC-042
Worker selesai job FEB lalu proses FASILKOM
Expected:
tenant context FEB sudah dibersihkan.
```

---

# 45. Test Scenario — Scheduler

```text
SS-TC-050
Scheduler berjalan
Expected:
semua tenant ACTIVE diproses.

SS-TC-051
Tenant SUSPENDED
Expected:
tidak diproses.

SS-TC-052
Task FEB gagal
Expected:
FASILKOM/FIKES tetap diproses.
```

---

# 46. Acceptance Criteria Shared Services

Shared Services dinyatakan siap apabila:

- [ ] Audit Log Service dapat mencatat aksi penting;
- [ ] audit log membawa tenant context;
- [ ] Notification Service mendukung in-app;
- [ ] email dapat diintegrasikan;
- [ ] WhatsApp dapat dijadikan channel opsional;
- [ ] notification gagal dapat dilacak;
- [ ] storage terisolasi per tenant;
- [ ] file tenant A tidak dapat diakses tenant B;
- [ ] file validation berjalan;
- [ ] workflow M1 dapat menggunakan state transition;
- [ ] workflow M2 dapat menggunakan state transition;
- [ ] workflow M4 dapat menggunakan state transition;
- [ ] workflow M7 dapat menggunakan state transition;
- [ ] invalid transition ditolak;
- [ ] Queue Service membawa tenant context;
- [ ] failed jobs dapat dilihat;
- [ ] Scheduler memproses tenant ACTIVE;
- [ ] scheduler failure satu tenant tidak menghentikan tenant lain;
- [ ] File Service mendukung upload;
- [ ] File Service mendukung metadata;
- [ ] versioning tersedia untuk kebutuhan M7;
- [ ] document generation dapat dipanggil dari service;
- [ ] permission dan policy diterapkan;
- [ ] log dan error dapat dimonitor;
- [ ] M1–M7 dapat memanggil Shared Services tanpa duplikasi implementasi.

---

# 47. Definition of Done

Shared Services dianggap selesai apabila:

1. service class utama tersedia;
2. migration/table pendukung tersedia;
3. tenant context diterapkan;
4. permission tersedia;
5. policy diterapkan;
6. audit log berjalan;
7. notification in-app berjalan;
8. queue worker berjalan;
9. scheduler berjalan;
10. storage tenant terpisah;
11. file validation berjalan;
12. workflow transition tervalidasi;
13. error handling konsisten;
14. logging tersedia;
15. retry mechanism tersedia untuk proses yang membutuhkan;
16. test functional lulus;
17. tenant isolation test lulus;
18. integration test dengan minimal dua modul lulus;
19. tidak ada service yang menyimpan tenant context secara bocor antar-request/job;
20. dokumentasi penggunaan service tersedia.

---

# 48. Urutan Implementasi Shared Services

Urutan yang disarankan:

```text
1. Audit Log
↓
2. Storage Service
↓
3. File Service
↓
4. Workflow / State Management
↓
5. Notification Service
↓
6. Queue Service
↓
7. Scheduler Service
```

Alasan:

- Audit perlu tersedia sejak awal agar perubahan dapat dilacak.
- Storage/File perlu siap sebelum modul yang menggunakan upload.
- Workflow perlu siap sebelum M1/M2/M4/M7.
- Notification memakai Queue untuk proses asynchronous.
- Scheduler digunakan setelah proses bisnis dasar tersedia.

---

# 49. Output Fase Shared Services

Setelah fase ini selesai:

```text
Multi-Tenant           ✓
Auth / RBAC / Panel    ✓
Master Data            ✓
Shared Services        ✓
```

SIFAK siap masuk ke implementasi modul bisnis.

Tahap berikutnya:

```text
M5 Profiling Dosen
+
M4 KRS & Penjadwalan
```

Shared Services yang sudah dibuat langsung digunakan oleh kedua modul tersebut.

---

# 50. Mapping Shared Services ke BRD

Shared Services mendukung kebutuhan BRD:

```text
Keamanan
→ Audit Log + RBAC + File Authorization

Audit
→ Audit Log Service

Notifikasi
→ Notification Service

Dokumen
→ Storage + File Service

Workflow
→ Workflow / State Management

Performa
→ Queue untuk proses berat

Monitoring & Alert
→ Scheduler + Queue + Notification

Multi-Tenant
→ Tenant-aware service pada seluruh shared service
```

---

# 51. Kesimpulan

Shared Services adalah lapisan pendukung bersama untuk seluruh sistem SIFAK.

Struktur akhirnya:

```text
Tenant Context
      ↓
Shared Services
├── Audit Log
├── Notification
├── Storage
├── Workflow
├── Queue
├── Scheduler
└── File Service
      ↓
M1–M7
```

Dengan pendekatan ini, setiap modul tidak perlu membuat mekanisme audit, notification, file, workflow, queue, atau scheduler sendiri.

Hasil akhirnya adalah:

```text
Satu Implementasi Shared Service
+
Banyak Modul Menggunakan
+
Tenant Isolation Tetap Terjaga
+
Maintenance Lebih Mudah
```

---

**SIFAK — Shared Services Specification v1.0**
