# M2 — SURAT MENYURAT
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M2 — Surat Menyurat  
**Tahap Implementasi:** Modul Bisnis Ketujuh / Modul Bisnis Terakhir sebelum Integrasi End-to-End  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, Notification, Storage, File Service, Workflow/State, Queue, Scheduler  
**Tujuan:** Menjabarkan kebutuhan, jenis surat, workflow pengajuan, approval, penomoran otomatis, template, generation, arsip, distribusi, hak akses, integrasi, acceptance criteria, QA, serta strategi implementasi M2 secara detail.

---

# 1. Ringkasan Modul

M2 mengelola proses administrasi surat di tingkat fakultas secara terstruktur dan terdigitalisasi.

```text
Pemohon
↓
Pilih Jenis Surat
↓
Isi Form
↓
Upload Lampiran
↓
Submit
↓
Verifikasi
↓
Approval Berjenjang
↓
Generate Nomor Surat
↓
Generate Dokumen
↓
Distribusi
↓
Arsip
```

M2 bukan sekadar upload file surat, tetapi mengelola lifecycle administrasi mulai dari pengajuan sampai arsip.

---

# 2. Tujuan M2

1. Mengelola pengajuan surat.
2. Menyediakan jenis surat yang configurable.
3. Menyediakan form dinamis sesuai jenis surat.
4. Mengelola lampiran.
5. Mengelola verifikasi administrasi.
6. Mengelola approval berjenjang.
7. Menghasilkan nomor surat otomatis.
8. Memastikan nomor surat unik.
9. Menyediakan template surat.
10. Menghasilkan file surat otomatis.
11. Mendukung PDF dan DOCX.
12. Mendukung QR verification bila digunakan.
13. Menyimpan histori approval.
14. Menyimpan histori perubahan.
15. Mengirim notifikasi status.
16. Mengelola arsip surat.
17. Menyediakan pencarian arsip.
18. Menyediakan laporan surat.
19. Menjaga tenant isolation.
20. Menyediakan audit trail.

---

# 3. Scope M2

## 3.1 In Scope

```text
Jenis Surat
Template Surat
Pengajuan Surat
Form Dinamis
Lampiran
Verifikasi
Approval
Revisi Pengajuan
Penomoran Otomatis
Document Generation
PDF / DOCX
QR Verification
Distribusi
Arsip
Search
Filter
History
Notification
Audit
Laporan
```

## 3.2 Out of Scope

- legal e-signature tersertifikasi jika provider belum tersedia;
- pengiriman surat fisik;
- transaksi pembayaran;
- OCR surat lama;
- enterprise document management eksternal;
- tanda tangan biometrik.

---

# 4. Aktor M2

| Aktor | Peran |
|---|---|
| Mahasiswa | Mengajukan surat, melihat status, download surat |
| Dosen | Mengajukan surat tertentu |
| Admin Prodi | Verifikasi awal sesuai jenis surat |
| Admin Fakultas/TU | Verifikasi administrasi, generate nomor, manage template, arsip |
| Kaprodi | Approval surat sesuai workflow |
| Dekan/WD | Approval tingkat fakultas sesuai workflow |
| Admin Tenant | Konfigurasi jenis surat dan template sesuai permission |
| Super Admin | Tidak mengelola surat tenant secara default |

---

# 5. Panel dan Menu

## 5.1 Mahasiswa Panel

```text
Surat
├── Ajukan Surat
├── Pengajuan Saya
├── Status
├── Surat Selesai
├── Download
└── Riwayat
```

## 5.2 Dosen Panel

```text
Surat
├── Ajukan Surat
├── Pengajuan Saya
├── Status
└── Riwayat
```

## 5.3 Admin Panel

```text
Surat Menyurat
├── Dashboard
├── Pengajuan Baru
├── Verifikasi
├── Menunggu Approval
├── Revisi Pengajuan
├── Penomoran Surat
├── Generate Surat
├── Surat Selesai
├── Arsip
├── Jenis Surat
├── Template Surat
├── Approval Flow
└── Laporan
```

## 5.4 Pimpinan Panel

```text
Approval Surat
├── Menunggu Approval
├── Riwayat Approval
├── Surat Ditolak
└── Surat Disetujui
```

---

# 6. Dependensi Master Data

M2 menggunakan:

```text
Fakultas
Program Studi
Mahasiswa
Dosen
Tahun Akademik
Semester
```

Jika surat berkaitan dengan sidang, TA, atau KRS, M2 membaca referensi dari modul terkait tanpa menduplikasi data.

---

# 7. Struktur Data Utama

```text
Jenis Surat
Template
Form Schema
Pengajuan
Lampiran
Verifikasi
Approval
Nomor Surat
Generated Document
Distribution
Archive
History
```

---

# 8. letter_types

```text
letter_types
├── id
├── code
├── name
├── description
├── requester_type
├── requires_attachment
├── requires_number
├── template_id nullable
├── approval_flow_id nullable
├── active
└── timestamps
```

Contoh jenis surat:

```text
Surat Keterangan Mahasiswa Aktif
Surat Pengantar Penelitian
Surat Tugas Dosen
Surat Izin Penelitian
Surat Pengantar Magang
Surat Keterangan Akademik
Surat Pengantar Sidang
Surat Bimbingan
```

---

# 9. requester_type

```text
STUDENT
LECTURER
ADMIN
MULTI
```

---

# 10. letter_form_fields

```text
letter_form_fields
├── id
├── letter_type_id
├── field_key
├── label
├── field_type
├── required
├── validation_rule nullable
├── options_json nullable
├── sequence
├── active
└── timestamps
```

Field type:

```text
TEXT
TEXTAREA
DATE
NUMBER
SELECT
MULTISELECT
CHECKBOX
FILE
```

---

# 11. letter_requests

```text
letter_requests
├── id
├── letter_type_id
├── requester_user_id
├── requester_type
├── requester_reference_id nullable
├── prodi_id nullable
├── request_number
├── status
├── submitted_at nullable
├── completed_at nullable
├── rejected_at nullable
├── rejection_reason nullable
└── timestamps
```

Status:

```text
DRAFT
SUBMITTED
UNDER_VERIFICATION
REVISION_REQUIRED
VERIFIED
WAITING_APPROVAL
APPROVED
NUMBERED
GENERATING
GENERATED
DISTRIBUTED
ARCHIVED
REJECTED
CANCELLED
```

---

# 12. letter_request_values

```text
letter_request_values
├── id
├── letter_request_id
├── field_id
├── value_text nullable
├── value_json nullable
└── timestamps
```

---

# 13. letter_attachments

```text
letter_attachments
├── id
├── letter_request_id
├── stored_file_id
├── attachment_type
├── description nullable
├── uploaded_by
└── timestamps
```

---

# 14. letter_verifications

```text
letter_verifications
├── id
├── letter_request_id
├── verifier_id
├── status
├── note nullable
├── verified_at
└── timestamps
```

Status:

```text
PENDING
VERIFIED
REVISION_REQUIRED
REJECTED
```

---

# 15. approval_flows

```text
approval_flows
├── id
├── code
├── name
├── description
├── active
└── timestamps
```

---

# 16. approval_flow_steps

```text
approval_flow_steps
├── id
├── approval_flow_id
├── step_order
├── role_code
├── approval_type
├── required
├── can_reject
├── can_request_revision
└── timestamps
```

Approval type:

```text
APPROVAL
ACKNOWLEDGEMENT
VERIFICATION
```

---

# 17. letter_approvals

```text
letter_approvals
├── id
├── letter_request_id
├── approval_step_id
├── approver_id
├── status
├── note nullable
├── approved_at nullable
├── rejected_at nullable
└── timestamps
```

Status:

```text
PENDING
APPROVED
REJECTED
REVISION_REQUIRED
SKIPPED
```

---

# 18. Contoh Approval Flow

```text
Mahasiswa
↓
Admin Prodi Verification
↓
Kaprodi Approval
↓
Admin Fakultas
↓
Generate Number
↓
Generate Letter
```

Contoh tingkat fakultas:

```text
Admin/TU
↓
Kaprodi
↓
Dekan / WD
↓
Generate Number
↓
Generate Document
```

---

# 19. Workflow Pengajuan Surat

```text
DRAFT
↓
SUBMITTED
↓
UNDER_VERIFICATION
↓
VERIFIED
↓
WAITING_APPROVAL
↓
APPROVED
↓
NUMBERED
↓
GENERATING
↓
GENERATED
↓
DISTRIBUTED
↓
ARCHIVED
```

Jika perlu revisi:

```text
UNDER_VERIFICATION
↓
REVISION_REQUIRED
↓
DRAFT
```

Jika ditolak:

```text
UNDER_VERIFICATION / WAITING_APPROVAL
↓
REJECTED
```

---

# 20. Business Rule Workflow

```text
M2-BR-001 Pengajuan hanya dapat diproses jika jenis surat aktif.
M2-BR-002 Form wajib harus terisi sebelum submit.
M2-BR-003 Lampiran wajib harus tersedia sebelum submit.
M2-BR-004 Step approval harus mengikuti urutan.
M2-BR-005 Approval step berikutnya tidak aktif sebelum step sebelumnya selesai.
M2-BR-006 Surat hanya mendapatkan nomor setelah approval lengkap.
M2-BR-007 Surat final tidak boleh diedit tanpa proses resmi.
M2-BR-008 Semua rejection/revision wajib memiliki catatan.
```

---

# 21. Penomoran Surat Otomatis

Pola harus configurable.

```text
{sequence}/{kode_surat}/{kode_fakultas}/{bulan_romawi}/{tahun}
```

Contoh:

```text
012/AKD/FASILKOM/IX/2026
```

---

# 22. letter_number_sequences

```text
letter_number_sequences
├── id
├── letter_type_id nullable
├── year
├── month nullable
├── current_sequence
├── reset_policy
└── timestamps
```

Reset policy:

```text
YEARLY
MONTHLY
NEVER
```

---

# 23. letter_numbers

```text
letter_numbers
├── id
├── letter_request_id
├── letter_type_id
├── sequence_number
├── formatted_number
├── generated_at
├── generated_by
└── timestamps
```

---

# 24. Rule Penomoran

```text
M2-BR-009 Nomor surat harus unik dalam scope tenant.
M2-BR-010 Sequence harus di-lock saat generation untuk mencegah duplicate concurrent request.
M2-BR-011 Nomor final tidak boleh diubah tanpa permission khusus.
M2-BR-012 Pembatalan surat tidak boleh menyebabkan nomor lama digunakan ulang kecuali kebijakan eksplisit mengizinkan.
```

---

# 25. Concurrency Penomoran

```text
Begin Transaction
↓
Lock Sequence
↓
Increment
↓
Create Letter Number
↓
Commit
```

Gunakan transaction dan atomic locking agar dua admin tidak memperoleh nomor yang sama.

---

# 26. Template Surat

Template menyimpan:

```text
header
body
footer
signature block
variables
layout
```

---

# 27. letter_templates

```text
letter_templates
├── id
├── code
├── name
├── letter_type_id nullable
├── template_format
├── content
├── file_template_id nullable
├── version
├── active
└── timestamps
```

Format:

```text
HTML
DOCX
```

---

# 28. Template Variables

```text
{{nomor_surat}}
{{tanggal}}
{{nama_mahasiswa}}
{{nim}}
{{program_studi}}
{{nama_dosen}}
{{nama_kaprodi}}
{{nama_dekan}}
{{judul_ta}}
```

Variable hanya berasal dari data tervalidasi.

---

# 29. Template Versioning

```text
Template v1
→ Surat lama tetap mengacu v1

Template v2
→ Surat baru memakai v2
```

Perubahan template tidak boleh mengubah surat lama.

---

# 30. Document Generation

```text
APPROVED
↓
Generate Number
↓
Load Template Version
↓
Resolve Variables
↓
Generate DOCX / HTML
↓
Generate PDF
↓
Store
↓
Checksum
↓
Status GENERATED
```

---

# 31. Tool Document Generation

```text
PHPWord
DomPDF
Browsershot
LibreOffice Headless
```

Contoh:

```text
DOCX template
→ PHPWord

DOCX → PDF
→ LibreOffice Headless
```

---

# 32. generated_letters

```text
generated_letters
├── id
├── letter_request_id
├── letter_number_id
├── template_id
├── template_version
├── docx_file_id nullable
├── pdf_file_id
├── checksum
├── generated_at
├── generated_by
└── timestamps
```

---

# 33. QR Verification

Flow:

```text
QR
↓
Verification URL
↓
Public Token
↓
Status Surat Valid / Tidak Valid
```

Informasi publik sebaiknya minimum:

```text
nomor surat
jenis
tanggal
penerbit
status validitas
```

---

# 34. letter_verification_tokens

```text
letter_verification_tokens
├── id
├── generated_letter_id
├── public_token
├── active
├── expires_at nullable
└── timestamps
```

---

# 35. Distribusi Surat

Channel:

```text
Download
Email
In-App
WhatsApp link opsional
```

---

# 36. letter_distributions

```text
letter_distributions
├── id
├── generated_letter_id
├── channel
├── recipient
├── status
├── sent_at nullable
├── error_message nullable
└── timestamps
```

---

# 37. Arsip Surat

Metadata:

```text
Nomor
Jenis Surat
Pemohon
Prodi
Tanggal
Status
Template Version
File
Checksum
```

---

# 38. letter_archives

```text
letter_archives
├── id
├── generated_letter_id
├── archive_code
├── classification
├── retention_until nullable
├── archived_at
├── archived_by
└── timestamps
```

---

# 39. Retention

```text
PERMANENT
5_YEARS
10_YEARS
CUSTOM
```

Hard delete tidak dilakukan tanpa retention policy.

---

# 40. Search Arsip

Filter:

```text
Nomor Surat
Jenis
Pemohon
Prodi
Tahun
Bulan
Status
Tanggal
```

Search:

```text
Nama
NIM
NIDN
Nomor
Keperluan
```

---

# 41. Dashboard Mahasiswa

```text
Pengajuan Aktif
Menunggu Verifikasi
Menunggu Approval
Perlu Revisi
Surat Selesai
```

---

# 42. Dashboard Admin

```text
Pengajuan Baru
Menunggu Verifikasi
Perlu Revisi
Menunggu Approval
Approved
Belum Bernomor
Belum Generated
Surat Hari Ini
```

---

# 43. Dashboard Pimpinan

```text
Menunggu Approval
Approved Hari Ini
Rejected
Riwayat Approval
```

---

# 44. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M2-FR-001 | Sistem dapat mengelola jenis surat. |
| M2-FR-002 | Sistem dapat menentukan requester type per jenis surat. |
| M2-FR-003 | Sistem dapat mengelola form dinamis. |
| M2-FR-004 | Sistem dapat mengelola field wajib. |
| M2-FR-005 | Pemohon dapat membuat draft pengajuan. |
| M2-FR-006 | Pemohon dapat upload lampiran. |
| M2-FR-007 | Sistem memvalidasi form sebelum submit. |
| M2-FR-008 | Admin dapat melakukan verifikasi. |
| M2-FR-009 | Admin dapat meminta revisi. |
| M2-FR-010 | Sistem dapat menjalankan approval berjenjang. |
| M2-FR-011 | Approver dapat approve/reject/request revision. |
| M2-FR-012 | Sistem menyimpan histori approval. |
| M2-FR-013 | Sistem dapat generate nomor surat otomatis. |
| M2-FR-014 | Nomor surat harus unik. |
| M2-FR-015 | Sistem mendukung reset sequence sesuai policy. |
| M2-FR-016 | Sistem dapat mengelola template. |
| M2-FR-017 | Template memiliki versioning. |
| M2-FR-018 | Sistem dapat melakukan variable replacement. |
| M2-FR-019 | Sistem dapat generate DOCX jika diperlukan. |
| M2-FR-020 | Sistem dapat generate PDF. |
| M2-FR-021 | Sistem menyimpan checksum. |
| M2-FR-022 | Sistem dapat menyediakan QR verification opsional. |
| M2-FR-023 | Sistem dapat mendistribusikan surat. |
| M2-FR-024 | Pemohon dapat download surat selesai. |
| M2-FR-025 | Sistem dapat mengarsipkan surat final. |
| M2-FR-026 | Admin dapat mencari arsip. |
| M2-FR-027 | Sistem dapat membuat laporan surat. |
| M2-FR-028 | Sistem mengirim notifikasi status. |
| M2-FR-029 | Semua perubahan penting diaudit. |
| M2-FR-030 | Semua proses tenant-aware. |

---

# 45. Business Rules Detail

| ID | Rule |
|---|---|
| M2-BR-013 | Pengajuan hanya untuk jenis surat aktif. |
| M2-BR-014 | Pemohon hanya dapat mengubah pengajuan miliknya sebelum status terkunci. |
| M2-BR-015 | Lampiran wajib harus tersedia. |
| M2-BR-016 | Approval mengikuti step order. |
| M2-BR-017 | Approver hanya dapat memproses step sesuai kewenangan. |
| M2-BR-018 | Nomor hanya dihasilkan setelah approval final. |
| M2-BR-019 | Nomor surat unik dalam tenant. |
| M2-BR-020 | Surat yang sudah generated tidak boleh diedit langsung. |
| M2-BR-021 | Regeneration harus memiliki alasan dan history. |
| M2-BR-022 | Template lama tetap dipertahankan. |
| M2-BR-023 | Tenant A tidak dapat membaca surat Tenant B. |
| M2-BR-024 | File final hanya dapat diakses user berwenang. |
| M2-BR-025 | Rejection/revision harus memiliki alasan. |
| M2-BR-026 | Override/renumber wajib diaudit. |

---

# 46. Permission M2

```text
view_own_letter_request
create_letter_request
update_own_letter_request
submit_letter_request
cancel_own_letter_request
download_own_letter

view_letter_request
verify_letter_request
request_letter_revision
reject_letter_request

approve_letter
reject_letter

manage_letter_type
manage_letter_form
manage_approval_flow
manage_letter_template

generate_letter_number
override_letter_number

generate_letter_document
regenerate_letter_document

distribute_letter
archive_letter
view_letter_archive
export_letter_report
```

---

# 47. Role Mapping

## Mahasiswa

```text
view_own_letter_request
create_letter_request
update_own_letter_request
submit_letter_request
cancel_own_letter_request
download_own_letter
```

## Dosen

```text
view_own_letter_request
create_letter_request
update_own_letter_request
submit_letter_request
download_own_letter
```

## Admin Prodi

```text
view_letter_request
verify_letter_request
request_letter_revision
reject_letter_request
```

## Admin Fakultas / TU

```text
view_letter_request
verify_letter_request
manage_letter_type
manage_letter_template
generate_letter_number
generate_letter_document
distribute_letter
archive_letter
view_letter_archive
export_letter_report
```

## Kaprodi / Dekan / WD

```text
approve_letter
reject_letter
view_letter_request
```

---

# 48. Policy / Data Scope

```text
Mahasiswa
→ pengajuan sendiri.

Dosen
→ pengajuan sendiri.

Admin Prodi
→ prodi scope.

Kaprodi
→ prodi sendiri.

Admin Fakultas
→ tenant/fakultas.

Dekan/WD
→ tenant/fakultas sesuai workflow.

Tenant
→ database tenant aktif.
```

---

# 49. Integrasi M1

Jika berita acara atau surat hasil sidang membutuhkan penomoran formal:

```text
M1 Sidang Completed
↓
M2 Numbering / Document Service
↓
Nomor Administratif
↓
Dokumen Formal
```

M1 tetap pemilik data sidang; M2 hanya menangani administrasi surat/nomor jika diperlukan.

---

# 50. Integrasi M7

```text
M7 TA Finalized
↓
M2 dapat membuat surat pengantar/pengesahan
```

M2 tidak menduplikasi dokumen TA.

---

# 51. Integrasi Shared Services

## Audit Log

```text
request created
request submitted
verification
revision
approval
rejection
number generated
template changed
document generated
distributed
archived
```

## Notification

```text
submitted
revision required
verified
waiting approval
approved
rejected
letter ready
```

## Storage/File

```text
attachment
template file
DOCX
PDF
archive
```

## Queue

```text
document generation
PDF conversion
bulk notification
report generation
```

## Scheduler

```text
pending approval reminder
stale request reminder
archive retention check
```

---

# 52. Audit Events M2

```text
LETTER_REQUEST_CREATED
LETTER_REQUEST_UPDATED
LETTER_REQUEST_SUBMITTED
LETTER_REQUEST_VERIFIED
LETTER_REVISION_REQUESTED
LETTER_REQUEST_REJECTED
LETTER_APPROVED
LETTER_APPROVAL_REJECTED
LETTER_NUMBER_GENERATED
LETTER_NUMBER_OVERRIDDEN
LETTER_TEMPLATE_CREATED
LETTER_TEMPLATE_UPDATED
LETTER_DOCUMENT_GENERATED
LETTER_DOCUMENT_REGENERATED
LETTER_DISTRIBUTED
LETTER_ARCHIVED
```

---

# 53. Internal Events M2

Diterbitkan:

```text
M2.LETTER_REQUEST_SUBMITTED
M2.LETTER_VERIFIED
M2.LETTER_APPROVED
M2.LETTER_REJECTED
M2.LETTER_NUMBER_GENERATED
M2.LETTER_GENERATED
M2.LETTER_ARCHIVED
```

Dapat menerima:

```text
M1.SIDANG_COMPLETED
M7.TA_FINALIZED
```

---

# 54. Service Layer

```text
LetterTypeService
LetterRequestService
LetterVerificationService
LetterApprovalService
LetterNumberService
LetterTemplateService
LetterGenerationService
LetterDistributionService
LetterArchiveService
```

---

# 55. Action Layer

```text
CreateLetterRequestAction
SubmitLetterRequestAction
VerifyLetterRequestAction
RequestLetterRevisionAction
ApproveLetterAction
RejectLetterAction
GenerateLetterNumberAction
GenerateLetterDocumentAction
RegenerateLetterDocumentAction
DistributeLetterAction
ArchiveLetterAction
```

---

# 56. Filament Resource

```text
LetterTypeResource
LetterRequestResource
ApprovalFlowResource
LetterTemplateResource
LetterArchiveResource
```

---

# 57. Custom Pages

```text
LetterDashboardPage
LetterVerificationPage
LetterApprovalPage
LetterNumberingPage
LetterGenerationPage
LetterArchiveSearchPage
```

---

# 58. Livewire Components

```text
DynamicLetterForm
LetterStatusTimeline
ApprovalTimeline
LetterPreview
LetterNumberPreview
ArchiveFilter
```

---

# 59. Queue Jobs

```text
GenerateLetterDocumentJob
ConvertLetterToPdfJob
SendLetterNotificationJob
GenerateLetterReportJob
```

Semua job tenant-aware.

---

# 60. Scheduler Tasks

```text
PendingApprovalReminder
StaleLetterRequestReminder
ArchiveRetentionCheck
FailedGenerationRetryCheck
```

---

# 61. Error Code

```text
LETTER_TYPE_NOT_FOUND
LETTER_TYPE_INACTIVE
LETTER_REQUEST_NOT_FOUND
LETTER_FORM_INVALID
LETTER_ATTACHMENT_REQUIRED
LETTER_VERIFICATION_REQUIRED
LETTER_APPROVAL_STEP_INVALID
LETTER_APPROVER_UNAUTHORIZED
LETTER_NUMBER_ALREADY_EXISTS
LETTER_SEQUENCE_ERROR
LETTER_TEMPLATE_NOT_FOUND
LETTER_GENERATION_FAILED
LETTER_ACCESS_DENIED
CROSS_TENANT_ACCESS_DENIED
```

---

# 62. Tenant Isolation

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.letter_requests
→ tenants/fasilkom/surat/...

feb.sifakueu.test
→ sifak_tenant_feb.letter_requests
→ tenants/feb/surat/...
```

Database dan storage wajib terisolasi.

---

# 63. Security

M2 harus menguji:

```text
Tenant isolation
Role bypass
Permission bypass
IDOR
Unauthorized approval
Unauthorized number generation
Number duplication
Template injection
XSS
CSRF
SQL injection
Path traversal
Unauthorized file download
MIME spoofing
Mass assignment
```

---

# 64. QA Test Scenario — Pengajuan

```text
M2-TC-001
Mahasiswa membuat draft surat aktif
Expected:
berhasil.

M2-TC-002
Field wajib kosong
Expected:
submit ditolak.

M2-TC-003
Lampiran wajib tidak ada
Expected:
submit ditolak.

M2-TC-004
Mahasiswa membuka request user lain
Expected:
403.
```

---

# 65. QA Test Scenario — Verifikasi

```text
M2-TC-010
Admin verifikasi request valid
Expected:
VERIFIED.

M2-TC-011
Data kurang
Expected:
REVISION_REQUIRED.

M2-TC-012
Admin luar scope mencoba verifikasi
Expected:
403.
```

---

# 66. QA Test Scenario — Approval

```text
M2-TC-020
Kaprodi approve step miliknya
Expected:
APPROVED.

M2-TC-021
Step kedua mencoba approve sebelum step pertama
Expected:
ditolak.

M2-TC-022
Approver salah role
Expected:
403.
```

---

# 67. QA Test Scenario — Penomoran

```text
M2-TC-030
Approval lengkap
Expected:
nomor dapat digenerate.

M2-TC-031
Approval belum lengkap
Expected:
generate nomor ditolak.

M2-TC-032
Dua admin generate bersamaan
Expected:
nomor tetap unik.

M2-TC-033
Nomor final diubah tanpa permission
Expected:
403.
```

---

# 68. QA Test Scenario — Template & Generation

```text
M2-TC-040
Template aktif digunakan
Expected:
surat generate berhasil.

M2-TC-041
Variable tidak tersedia
Expected:
generation gagal dengan error jelas.

M2-TC-042
Template v1 berubah ke v2
Expected:
surat lama tetap menggunakan versi lama.

M2-TC-043
Generation gagal
Expected:
status gagal dan retry tersedia.
```

---

# 69. QA Test Scenario — Archive

```text
M2-TC-050
Surat generated
Expected:
dapat diarsipkan.

M2-TC-051
Belum final
Expected:
arsip ditolak.

M2-TC-052
Search nomor
Expected:
surat ditemukan sesuai scope.
```

---

# 70. QA Test Scenario — Tenant Isolation

```text
M2-TC-060
FASILKOM membaca surat FEB
Expected:
ditolak.

M2-TC-061
FASILKOM mencoba download file FEB
Expected:
ditolak.

M2-TC-062
Queue generation FEB
Expected:
DB/storage FEB saja yang digunakan.
```

---

# 71. Acceptance Criteria M2

- [ ] Jenis surat dapat dibuat.
- [ ] Jenis surat dapat diaktif/nonaktifkan.
- [ ] Requester type dapat dikonfigurasi.
- [ ] Form dinamis berjalan.
- [ ] Field wajib tervalidasi.
- [ ] Lampiran dapat diupload.
- [ ] Draft dapat dibuat.
- [ ] Submit berjalan.
- [ ] Verifikasi berjalan.
- [ ] Revision request berjalan.
- [ ] Approval flow berjalan.
- [ ] Multi-step approval berjalan.
- [ ] Rejection berjalan.
- [ ] Nomor surat dapat digenerate.
- [ ] Nomor surat unik.
- [ ] Concurrency penomoran aman.
- [ ] Template dapat dikelola.
- [ ] Template memiliki versioning.
- [ ] Variable replacement berjalan.
- [ ] DOCX dapat digenerate jika dibutuhkan.
- [ ] PDF dapat digenerate.
- [ ] Checksum tersimpan.
- [ ] QR verification opsional dapat digunakan.
- [ ] Surat dapat didistribusikan.
- [ ] Pemohon dapat download.
- [ ] Surat dapat diarsipkan.
- [ ] Pencarian arsip berjalan.
- [ ] Laporan surat tersedia.
- [ ] Notification berjalan.
- [ ] Audit log berjalan.
- [ ] Queue tenant-aware.
- [ ] Scheduler tenant-aware.
- [ ] Tenant isolation lulus.
- [ ] Authorization test lulus.

---

# 72. Definition of Done M2

M2 dianggap selesai apabila:

1. migration selesai;
2. model dan relation selesai;
3. permission tersedia;
4. policy tersedia;
5. service layer tersedia;
6. action layer tersedia;
7. Mahasiswa Panel terintegrasi;
8. Dosen Panel terintegrasi;
9. Admin Panel terintegrasi;
10. Pimpinan Panel terintegrasi;
11. letter type berjalan;
12. dynamic form berjalan;
13. upload attachment berjalan;
14. verification berjalan;
15. approval flow berjalan;
16. number generation aman;
17. template versioning berjalan;
18. document generation berjalan;
19. PDF generation berjalan;
20. QR verification opsional berjalan jika digunakan;
21. distribution berjalan;
22. archive berjalan;
23. search/filter archive berjalan;
24. audit log berjalan;
25. notification berjalan;
26. Queue tenant-aware;
27. Scheduler tenant-aware;
28. integrasi M1 tersedia jika dibutuhkan;
29. integrasi M7 tersedia jika dibutuhkan;
30. functional test lulus;
31. authorization test lulus;
32. security test utama lulus;
33. tenant isolation test lulus;
34. dokumentasi internal tersedia.

---

# 73. Urutan Implementasi M2

```text
1. Letter Type
↓
2. Dynamic Form Schema
↓
3. Letter Request
↓
4. Attachment
↓
5. Verification
↓
6. Approval Flow
↓
7. Approval Steps
↓
8. Letter Number Sequence
↓
9. Safe Number Generation
↓
10. Template
↓
11. Template Versioning
↓
12. Variable Resolver
↓
13. Document Generation
↓
14. PDF Conversion
↓
15. QR Verification
↓
16. Distribution
↓
17. Archive
↓
18. Search & Filter
↓
19. Report
↓
20. Integration M1/M7
↓
21. QA
```

---

# 74. Sprint Rekomendasi

## Sprint M2-1 — Request Foundation

```text
Letter Type
Dynamic Form
Request
Attachment
Permission
Policy
Audit
```

Output:

```text
Pemohon dapat membuat pengajuan surat.
```

## Sprint M2-2 — Verification & Approval

```text
Verification
Revision
Approval Flow
Approval Step
Rejection
Notification
```

Output:

```text
Pengajuan dapat diproses sampai approved.
```

## Sprint M2-3 — Numbering

```text
Sequence
Format
Transaction Lock
Number Generation
History
```

Output:

```text
Nomor surat otomatis aman dan unik.
```

## Sprint M2-4 — Template & Generation

```text
Template
Version
Variable Resolver
DOCX
PDF
Queue
```

Output:

```text
Surat dapat digenerate otomatis.
```

## Sprint M2-5 — Distribution & Archive

```text
Download
Email
QR Verification
Archive
Search
Retention
```

Output:

```text
Surat selesai dapat didistribusikan dan diarsipkan.
```

## Sprint M2-6 — Integration & QA

```text
M1 Integration
M7 Integration
Dashboard
Reports
Security
Tenant Isolation
Regression
```

Output:

```text
M2 siap untuk integrasi end-to-end.
```

---

# 75. Output Akhir M2

```text
Mahasiswa/Dosen dapat mengajukan surat
+
Admin dapat memverifikasi
+
Approval dapat dilakukan berjenjang
+
Nomor surat dibuat otomatis
+
Template dapat digunakan ulang
+
Surat dapat digenerate PDF/DOCX
+
Status dapat dipantau
+
Surat dapat didistribusikan
+
Arsip dapat dicari
+
Audit seluruh proses tersedia
```

---

# 76. Status Implementasi M1–M7

```text
M5 — Profiling Dosen                    ✓
M4 — KRS & Penjadwalan                 ✓
M7 — Dokumen TA & Repository           ✓
M1 — Sidang Sempro & TA                ✓
M6 — Profiling Mahasiswa               ✓
M3 — Monitoring & Alert                ✓
M2 — Surat Menyurat                    ✓
```

---

# 77. Tahap Berikutnya Setelah M2

```text
INTEGRASI END-TO-END M1–M7
↓
NFR IMPLEMENTATION
↓
SECURITY HARDENING
↓
OBSERVABILITY
↓
BACKUP / RESTORE
↓
QA FULL SYSTEM
↓
REGRESSION
↓
UAT
↓
FASILKOM PILOT
↓
CREATE TENANT KEDUA
↓
TENANT ISOLATION VALIDATION
↓
PRODUCTION READINESS
```

---

# 78. Kesimpulan

M2 terdiri dari lima lapisan:

```text
REQUEST LAYER
├── Jenis Surat
├── Dynamic Form
├── Lampiran
└── Verifikasi

APPROVAL LAYER
├── Approval Flow
├── Multi-Step Approval
├── Revision
└── Rejection

NUMBERING LAYER
├── Sequence
├── Format
├── Concurrency Control
└── History

DOCUMENT LAYER
├── Template
├── Version
├── Variable Resolver
├── DOCX/PDF
└── QR Verification

ARCHIVE LAYER
├── Distribution
├── Search
├── Retention
└── Audit
```

Dengan struktur ini, M2 menjadi modul administrasi surat yang konsisten, aman, multi-tenant, dan terintegrasi dengan fondasi SIFAK tanpa menduplikasi business logic modul lain.

---

**SIFAK — M2 Surat Menyurat — Detailed Specification v1.0**
