# M7 — MANAJEMEN DOKUMEN TA & REPOSITORI DIGITAL
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M7 — Manajemen Dokumen TA & Repositori Digital  
**Tahap Implementasi:** Modul Bisnis Ketiga setelah M5 dan M4  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, M5 Profiling Dosen, M4 KRS & Penjadwalan  
**Tujuan:** Menjabarkan kebutuhan, alur, data, versioning, review, approval, kompilasi dokumen, repository, hak akses, integrasi, acceptance criteria, dan strategi implementasi M7 secara detail.

---

# 1. Ringkasan Modul

M7 merupakan modul yang mengelola siklus dokumen Tugas Akhir mahasiswa mulai dari penyusunan, upload, review pembimbing, revisi, approval per bagian, finalisasi, kompilasi, hingga penyimpanan ke repositori digital.

Alur utama:

```text
Mahasiswa memiliki topik/judul TA
↓
Membuat Dokumen TA
↓
Mengelola Bab / Bagian
↓
Upload / Update Versi
↓
Submit ke Pembimbing
↓
Review
↓
Komentar / Revisi
↓
Approve per Bab
↓
Seluruh bagian terpenuhi
↓
Finalisasi Dokumen
↓
Generate / Compile Final Document
↓
Status Eligible untuk proses M1
↓
Arsip ke Repositori Digital
```

M7 menjadi fondasi langsung bagi:

```text
M1 — Sidang Sempro & TA
→ validasi kesiapan dokumen
→ dokumen final
→ revisi pasca sidang

M3 — Monitoring & Alert
→ progress TA
→ keterlambatan revisi
→ status approval

M6 — Profiling Mahasiswa
→ topik TA
→ bidang minat
→ histori akademik/portofolio
```

---

# 2. Tujuan M7

M7 bertujuan untuk:

1. mengelola dokumen TA mahasiswa secara terstruktur;
2. menyimpan judul dan metadata TA;
3. mengelola bagian dokumen seperti Bab 1–Bab 5;
4. mendukung upload file per bagian;
5. menyimpan version history;
6. mendukung review pembimbing;
7. mendukung komentar dan catatan revisi;
8. mendukung approval per bagian;
9. mendukung status progres TA;
10. mendukung finalisasi dokumen;
11. mendukung kompilasi dokumen final;
12. menyimpan dokumen final ke repositori;
13. menjaga tenant isolation;
14. menjaga data ownership;
15. menyediakan histori perubahan;
16. menyediakan data kesiapan untuk M1;
17. menyediakan data progress ke M3;
18. menyediakan metadata yang dapat digunakan M6;
19. mendukung audit;
20. mendukung backup dan recovery file.

---

# 3. Scope M7

## 3.1 In Scope

```text
Metadata TA
Judul TA
Pembimbing
Struktur Dokumen
Bab / Bagian
Upload File
Versioning
Review
Komentar
Revisi
Approval Bab
Progress TA
Finalisasi
Compile Dokumen
Final PDF/DOCX
Repository
Metadata Repository
Hak Akses File
Audit
Notification
Queue
Scheduler
Integrasi M1
Integrasi M3
Integrasi M6
```

## 3.2 Out of Scope

Untuk versi awal:

- plagiarism checking otomatis jika layanan eksternal belum tersedia;
- similarity scoring sebagai keputusan kelulusan;
- editor Word penuh di browser;
- kolaborasi real-time seperti Google Docs;
- public repository lintas tenant;
- integrasi DOI;
- penerbitan jurnal otomatis;
- OCR;
- AI rewriting atau AI auto-approval.

---

# 4. Aktor M7

| Aktor | Peran |
|---|---|
| Mahasiswa | Menyusun, upload, revisi, submit dokumen TA |
| Dosen Pembimbing | Review, komentar, approve/reject bagian |
| Admin Prodi | Monitoring status TA dan administrasi dokumen |
| Kaprodi | Monitoring kesiapan mahasiswa dan status dokumen |
| Admin Fakultas/TU | Mengelola repositori sesuai permission |
| BAAK | Melihat dokumen final jika dibutuhkan untuk proses akademik |
| LPM/Gugus Mutu | Mengakses repository sesuai kebutuhan mutu/akreditasi |
| M1 Service | Membaca status eligibility dan dokumen final |
| M3 Service | Membaca progress dan keterlambatan |
| M6 Service | Membaca metadata topik TA yang diizinkan |

---

# 5. Panel dan Menu

## 5.1 Mahasiswa Panel

```text
Tugas Akhir
├── Ringkasan TA
├── Judul & Metadata
├── Pembimbing
├── Dokumen
│   ├── Halaman Awal
│   ├── Bab 1
│   ├── Bab 2
│   ├── Bab 3
│   ├── Bab 4
│   ├── Bab 5
│   ├── Daftar Pustaka
│   └── Lampiran
├── Komentar Pembimbing
├── Riwayat Revisi
├── Progress
├── Finalisasi
└── Dokumen Final
```

## 5.2 Dosen Panel

```text
Bimbingan TA
├── Mahasiswa Bimbingan
├── Dokumen Menunggu Review
├── Review Bab
├── Komentar
├── Riwayat Revisi
├── Approval
└── Progress Mahasiswa
```

## 5.3 Admin Panel

```text
Dokumen TA
├── Daftar Mahasiswa TA
├── Status Dokumen
├── Status Approval
├── Progress
├── Dokumen Final
├── Repository
├── Metadata
└── Laporan
```

## 5.4 Pimpinan Panel

```text
Monitoring TA
├── Progress per Prodi
├── Mahasiswa Terlambat
├── Dokumen Belum Final
├── Approval Pending
└── Repository Summary
```

---

# 6. Dependensi Master Data

M7 menggunakan:

```text
Mahasiswa
Dosen
Program Studi
Semester
Tahun Akademik
```

M7 tidak menduplikasi data tersebut.

---

# 7. Struktur Data Utama

Kelompok data M7:

```text
TA
Dokumen
Bagian
Versi
Komentar
Review
Approval
Progress
Final File
Repository
History
```

---

# 8. tugas_akhir

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint/uuid | PK |
| mahasiswa_id | FK | Pemilik TA |
| prodi_id | FK | Prodi |
| pembimbing_1_id | FK nullable | Pembimbing utama |
| pembimbing_2_id | FK nullable | Pembimbing kedua |
| judul | varchar/text | Judul TA |
| judul_en | varchar/text nullable | Judul Inggris |
| topik | varchar/text nullable | Topik utama |
| keywords | json/text nullable | Kata kunci |
| tahun_akademik_id | FK | Tahun akademik |
| semester_id | FK | Semester |
| status | enum | Status TA |
| progress_percent | decimal | Progress |
| final_document_id | FK nullable | Dokumen final |
| created_at | timestamp | Dibuat |
| updated_at | timestamp | Diubah |

---

# 9. Status Tugas Akhir

Contoh:

```text
DRAFT
ACTIVE
IN_REVIEW
REVISION
READY_FOR_FINALIZATION
FINALIZED
ARCHIVED
```

M7 tidak menggunakan satu boolean seperti:

```text
is_done = true
```

untuk keseluruhan proses.

---

# 10. ta_sections

Tabel definisi bagian dokumen.

```text
ta_sections
├── id
├── code
├── name
├── sequence
├── required
├── template_type
├── status
└── timestamps
```

Contoh:

```text
FRONT_MATTER
BAB_1
BAB_2
BAB_3
BAB_4
BAB_5
DAFTAR_PUSTAKA
LAMPIRAN
```

---

# 11. Struktur Default Dokumen

Contoh:

```text
Halaman Awal
├── Cover
├── Lembar Pengesahan
├── Pernyataan
├── Abstrak Indonesia
├── Abstract English
├── Kata Pengantar
├── Daftar Isi
├── Daftar Tabel
└── Daftar Gambar

Isi
├── Bab 1
├── Bab 2
├── Bab 3
├── Bab 4
└── Bab 5

Bagian Akhir
├── Daftar Pustaka
└── Lampiran
```

Struktur aktual dapat dikonfigurasi sesuai template fakultas.

---

# 12. ta_documents

```text
ta_documents
├── id
├── tugas_akhir_id
├── section_id
├── current_version_id
├── status
├── approved_at
├── approved_by
└── timestamps
```

Status:

```text
DRAFT
SUBMITTED
IN_REVIEW
REVISION_REQUIRED
APPROVED
FINAL
```

---

# 13. ta_document_versions

| Field | Keterangan |
|---|---|
| id | PK |
| ta_document_id | FK |
| version_number | Nomor versi |
| file_id | FK ke stored_files |
| submitted_by | User |
| submitted_at | Timestamp |
| change_summary | Ringkasan perubahan |
| status | draft/submitted/reviewed/superseded |
| checksum | Hash |
| created_at | Timestamp |

---

# 14. Versioning

Contoh:

```text
Bab 1
├── v1
├── v2
├── v3
└── v4 APPROVED
```

Versi lama:

```text
tidak dihapus
↓
disimpan sebagai history
↓
dapat dilihat sesuai permission
```

---

# 15. ta_reviews

```text
ta_reviews
├── id
├── ta_document_version_id
├── reviewer_id
├── review_status
├── summary
├── reviewed_at
└── timestamps
```

Status:

```text
PENDING
REVIEWED
REVISION_REQUIRED
APPROVED
```

---

# 16. ta_comments

```text
ta_comments
├── id
├── ta_document_version_id
├── reviewer_id
├── comment
├── page_reference nullable
├── section_reference nullable
├── status
├── resolved_by nullable
├── resolved_at nullable
└── timestamps
```

Status:

```text
OPEN
RESOLVED
CLOSED
```

---

# 17. ta_approvals

```text
ta_approvals
├── id
├── ta_document_id
├── approver_id
├── approval_type
├── status
├── note
├── approved_at
└── timestamps
```

---

# 18. ta_progress_logs

```text
ta_progress_logs
├── id
├── tugas_akhir_id
├── progress_type
├── old_status
├── new_status
├── progress_percent
├── note
├── actor_id
└── created_at
```

---

# 19. repository_items

```text
repository_items
├── id
├── tugas_akhir_id
├── final_file_id
├── title
├── abstract_id
├── abstract_en
├── keywords
├── author_name
├── nim
├── prodi_id
├── supervisor_names
├── year
├── access_level
├── status
├── published_at
└── timestamps
```

---

# 20. Repository Access Level

Contoh:

```text
PRIVATE
INTERNAL
PUBLIC_METADATA
PUBLIC_FULLTEXT
```

Default akses harus mengikuti kebijakan tenant.

---

# 21. Workflow TA

Flow utama:

```text
DRAFT
↓
ACTIVE
↓
IN_REVIEW
↓
REVISION
↓
READY_FOR_FINALIZATION
↓
FINALIZED
↓
ARCHIVED
```

---

# 22. Workflow Per Bab

```text
DRAFT
↓
SUBMITTED
↓
IN_REVIEW
↓
APPROVED
```

Jika perlu revisi:

```text
IN_REVIEW
↓
REVISION_REQUIRED
↓
DRAFT
↓
SUBMITTED
```

---

# 23. Aturan Transisi

| Dari | Ke | Aktor |
|---|---|---|
| DRAFT | SUBMITTED | Mahasiswa |
| SUBMITTED | IN_REVIEW | Sistem/Pembimbing |
| IN_REVIEW | APPROVED | Pembimbing |
| IN_REVIEW | REVISION_REQUIRED | Pembimbing |
| REVISION_REQUIRED | DRAFT | Mahasiswa/System |
| Semua Required Approved | READY_FOR_FINALIZATION | Sistem |
| READY_FOR_FINALIZATION | FINALIZED | Role berwenang/System |
| FINALIZED | ARCHIVED | Admin/System |

---

# 24. Business Rule Review

```text
M7-BR-001
Mahasiswa hanya dapat mengubah dokumen miliknya sendiri.

M7-BR-002
Dosen Pembimbing hanya dapat review mahasiswa bimbingannya.

M7-BR-003
Versi yang sudah disubmit tidak boleh dioverwrite tanpa membuat versi baru.

M7-BR-004
Versi APPROVED tetap dipertahankan sebagai histori.

M7-BR-005
Komentar harus terhubung ke versi dokumen tertentu.

M7-BR-006
Approval hanya dapat dilakukan oleh pembimbing/role yang berwenang.

M7-BR-007
Dokumen final hanya dapat dibuat ketika seluruh bagian wajib sudah memenuhi syarat.
```

---

# 25. Progress TA

Progress tidak hanya berdasarkan jumlah file.

Contoh komponen:

```text
Bab 1 approved
Bab 2 approved
Bab 3 approved
Bab 4 approved
Bab 5 approved
Daftar Pustaka complete
Final requirement complete
```

Progress dapat dihitung secara rule-based.

Contoh:

```text
5 bagian wajib
3 approved

progress =
3 / 5 × 100
= 60%
```

Bobot dapat dikonfigurasi jika setiap bagian tidak dianggap sama.

---

# 26. Status Progress

Contoh:

```text
0–24%    → EARLY
25–49%   → IN_PROGRESS
50–74%   → DEVELOPING
75–99%   → NEAR_COMPLETION
100%     → READY
```

Label bersifat konfiguratif.

---

# 27. Upload File

File service menangani:

```text
Upload
MIME validation
Size validation
Filename normalization
Checksum
Tenant path
Metadata
Version
Authorization
```

---

# 28. Tipe File

Default:

```text
DOCX
PDF
```

Opsional untuk lampiran:

```text
XLSX
CSV
PNG
JPG
ZIP
```

Jenis aktual harus dapat dikonfigurasi per bagian.

---

# 29. Struktur Storage

Contoh:

```text
tenants/
└── fasilkom/
    └── ta/
        └── 2026/
            └── 202601001/
                ├── bab_1/
                │   ├── v1.docx
                │   ├── v2.docx
                │   └── v3.docx
                ├── bab_2/
                ├── lampiran/
                └── final/
                    ├── thesis_final.docx
                    └── thesis_final.pdf
```

---

# 30. File Ownership

File tidak boleh diakses hanya karena user mengetahui path.

Access flow:

```text
User request file
↓
Resolve Tenant
↓
Authenticate
↓
Check Permission
↓
Check Relationship / Ownership
↓
Generate authorized response
```

---

# 31. Compile Dokumen

Ketika semua bagian siap:

```text
Bab approved
↓
System collect current approved version
↓
Compile
↓
Generate DOCX
↓
Generate PDF
↓
Save final file
↓
Checksum
↓
Audit
```

---

# 32. Tool Document Generation

Sesuai stack SIFAK:

```text
PHPWord
LibreOffice Headless
DomPDF
Browsershot
```

Penggunaan dapat disesuaikan dengan kebutuhan format.

Contoh:

```text
DOCX assembly
→ PHPWord

DOCX → PDF
→ LibreOffice Headless

HTML template → PDF
→ DomPDF/Browsershot
```

---

# 33. Queue untuk Compilation

Compilation sebaiknya menggunakan Queue jika file besar.

Flow:

```text
User Finalize
↓
Create Compile Job
↓
Status COMPILING
↓
Queue Worker
↓
Generate
↓
Status READY
```

Jika gagal:

```text
COMPILING
↓
FAILED
↓
RETRY
```

---

# 34. Finalization Checklist

Sebelum finalisasi:

```text
[✓] Judul tersedia
[✓] Pembimbing tersedia
[✓] Semua required section approved
[✓] Tidak ada review wajib unresolved
[✓] Metadata lengkap
[✓] File required tersedia
```

Jika ada yang gagal:

```text
Finalization blocked
```

---

# 35. Eligibility untuk M1

M7 menyediakan status:

```text
TA_DOCUMENT_READY
```

M1 dapat menggunakan:

```text
tugas_akhir.status
required_sections_approved
final_document_exists
```

untuk validasi kesiapan sidang.

M7 tidak memutuskan sendiri apakah mahasiswa pasti boleh sidang karena M1 masih dapat memiliki syarat lain.

---

# 36. Output Eligibility

Contoh internal response:

```text
student_id: 123
ta_status: READY_FOR_FINALIZATION
document_ready: true
final_document_available: true
pending_sections: []
```

---

# 37. Integrasi M1

Flow:

```text
M7 Document Ready
↓
M1 menerima status
↓
Mahasiswa daftar sidang
↓
M1 validasi syarat lain
↓
Sidang dijadwalkan
```

Pasca sidang:

```text
M1 menghasilkan revisi
↓
M7 membuka Revision Cycle
↓
Mahasiswa upload revisi
↓
Pembimbing review
↓
Final Approved
↓
Repository updated
```

---

# 38. Revisi Pasca Sidang

M7 perlu membedakan:

```text
PRE_SIDANG_REVISION
POST_SIDANG_REVISION
```

Field dapat ditambahkan pada review/revision cycle.

---

# 39. revision_cycles

```text
revision_cycles
├── id
├── tugas_akhir_id
├── source
├── started_at
├── deadline nullable
├── status
├── closed_at nullable
└── timestamps
```

Source:

```text
SUPERVISOR
SEMPRO
SIDANG_TA
ADMIN
```

---

# 40. Integrasi M3

M3 membaca:

```text
progress TA
jumlah section approved
section pending
review pending
deadline revisi
last activity
status finalisasi
```

Contoh alert:

```text
Tidak ada aktivitas TA 14 hari
↓
M3 Warning

Revisi lewat deadline
↓
M3 Alert
```

---

# 41. Integrasi M6

M6 dapat menggunakan metadata:

```text
Judul
Topik
Keywords
Bidang
Pembimbing
```

untuk profiling mahasiswa dan rekomendasi akademik.

Akses hanya terhadap metadata yang diizinkan.

---

# 42. Integrasi M5

M7 menggunakan M5 secara tidak langsung untuk:

```text
Pembimbing
Profil keahlian dosen
Availability konsultasi
```

M7 tidak menghitung ulang rekomendasi dosen.

---

# 43. Integrasi Shared Services

## Audit

Mencatat:

```text
file upload
version created
submit review
comment added
revision requested
approval
finalization
compile
repository publish
access level change
```

## Notification

Untuk:

```text
dokumen submitted
review available
revision requested
section approved
final document ready
deadline reminder
```

## Queue

Untuk:

```text
file processing
compile document
generate PDF
bulk notification
```

## Scheduler

Untuk:

```text
revision reminder
inactive TA reminder
repository maintenance
temporary file cleanup
```

---

# 44. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M7-FR-001 | Sistem dapat membuat record TA mahasiswa. |
| M7-FR-002 | Sistem dapat menyimpan judul TA. |
| M7-FR-003 | Sistem dapat menyimpan pembimbing. |
| M7-FR-004 | Sistem dapat membuat struktur bagian dokumen. |
| M7-FR-005 | Mahasiswa dapat upload dokumen per bagian. |
| M7-FR-006 | Sistem membuat version baru untuk update dokumen. |
| M7-FR-007 | Sistem menyimpan version history. |
| M7-FR-008 | Mahasiswa dapat submit versi ke pembimbing. |
| M7-FR-009 | Pembimbing dapat melihat versi submitted. |
| M7-FR-010 | Pembimbing dapat memberi komentar. |
| M7-FR-011 | Pembimbing dapat meminta revisi. |
| M7-FR-012 | Pembimbing dapat approve bagian. |
| M7-FR-013 | Sistem dapat menghitung progress. |
| M7-FR-014 | Sistem menampilkan status tiap bagian. |
| M7-FR-015 | Sistem mencegah overwrite versi submitted/approved. |
| M7-FR-016 | Sistem dapat menyimpan revision cycle. |
| M7-FR-017 | Sistem dapat membedakan revisi pre/post sidang. |
| M7-FR-018 | Sistem menentukan readiness dokumen. |
| M7-FR-019 | Sistem dapat melakukan finalization checklist. |
| M7-FR-020 | Sistem dapat compile dokumen final. |
| M7-FR-021 | Sistem dapat menghasilkan DOCX final. |
| M7-FR-022 | Sistem dapat menghasilkan PDF final. |
| M7-FR-023 | Sistem menyimpan checksum final file. |
| M7-FR-024 | Sistem dapat membuat repository item. |
| M7-FR-025 | Sistem dapat menyimpan metadata repository. |
| M7-FR-026 | Sistem dapat mengatur access level repository. |
| M7-FR-027 | M1 dapat membaca readiness dokumen. |
| M7-FR-028 | M3 dapat membaca progress TA. |
| M7-FR-029 | M6 dapat membaca metadata yang diizinkan. |
| M7-FR-030 | Semua akses file menggunakan authorization. |
| M7-FR-031 | Semua aktivitas penting diaudit. |
| M7-FR-032 | Semua proses tenant-aware. |

---

# 45. Business Rules M7

| ID | Rule |
|---|---|
| M7-BR-008 | Mahasiswa hanya mengelola TA miliknya. |
| M7-BR-009 | Pembimbing hanya review mahasiswa bimbingannya. |
| M7-BR-010 | Setiap upload baru terhadap dokumen yang sudah disubmit menghasilkan versi baru. |
| M7-BR-011 | Versi lama tidak dihapus ketika versi baru dibuat. |
| M7-BR-012 | Hanya versi current yang dapat disubmit. |
| M7-BR-013 | Approval dikaitkan pada dokumen/versi yang jelas. |
| M7-BR-014 | Dokumen final hanya dibuat jika semua bagian wajib valid. |
| M7-BR-015 | File final tidak boleh diubah langsung tanpa revision cycle. |
| M7-BR-016 | Repository hanya menerima final document. |
| M7-BR-017 | Tenant A tidak dapat membaca file tenant B. |
| M7-BR-018 | Access level repository mengikuti kebijakan tenant. |
| M7-BR-019 | Setiap perubahan final/repository harus diaudit. |
| M7-BR-020 | M7 hanya menyediakan readiness; keputusan sidang tetap M1. |

---

# 46. Permission M7

```text
view_own_ta
manage_own_ta
upload_own_ta_document
submit_ta_document
view_own_ta_history

view_supervised_ta
review_ta_document
comment_ta_document
request_ta_revision
approve_ta_document

view_ta_monitoring
view_ta_progress
manage_ta_metadata
manage_ta_repository

finalize_ta_document
compile_ta_document
publish_ta_repository
change_repository_access

view_final_ta_document
download_ta_document
view_ta_audit
```

---

# 47. Role Mapping

## Mahasiswa

```text
view_own_ta
manage_own_ta
upload_own_ta_document
submit_ta_document
view_own_ta_history
download_ta_document
```

## Dosen Pembimbing

```text
view_supervised_ta
review_ta_document
comment_ta_document
request_ta_revision
approve_ta_document
download_ta_document
```

## Admin Prodi

```text
view_ta_monitoring
view_ta_progress
manage_ta_metadata
view_final_ta_document
```

## Admin Fakultas/TU

```text
manage_ta_repository
publish_ta_repository
change_repository_access
```

## Kaprodi

```text
view_ta_monitoring
view_ta_progress
view_final_ta_document
```

## BAAK/LPM

```text
view_final_ta_document
view_repository_metadata
```

sesuai scope dan policy.

---

# 48. Policy / Data Scope

```text
Mahasiswa
→ TA miliknya sendiri.

Pembimbing
→ TA mahasiswa bimbingannya.

Admin Prodi
→ mahasiswa dalam prodi scope.

Kaprodi
→ prodi sendiri.

Admin Fakultas
→ tenant sendiri.

BAAK/LPM
→ sesuai scope yang ditetapkan.

Tenant
→ database tenant aktif.
```

---

# 49. Validasi Metadata TA

Minimal:

```text
Mahasiswa valid
Prodi valid
Judul required
Pembimbing valid
Tahun akademik valid
Semester valid
```

---

# 50. Validasi Upload

Minimal:

```text
MIME allowed
Extension allowed
Size allowed
Filename sanitized
Checksum generated
Tenant path valid
User authorized
```

---

# 51. Validation Status

Contoh:

```text
VALID
WARNING
INVALID
```

---

# 52. Search dan Filter

Admin:

```text
Search:
NIM
Nama
Judul
Pembimbing

Filter:
Prodi
Status TA
Progress
Pembimbing
Semester
Tahun Akademik
Finalized
Repository Status
```

---

# 53. Dashboard Mahasiswa

Widget:

```text
Progress TA
Bagian Approved
Bagian Pending
Komentar Open
Deadline Revisi
Last Activity
Finalization Status
```

---

# 54. Dashboard Dosen Pembimbing

Widget:

```text
Total Mahasiswa Bimbingan
Menunggu Review
Revision Required
Near Completion
Overdue Review
```

---

# 55. Dashboard Admin/Kaprodi

Widget:

```text
Total Mahasiswa TA
Draft
In Review
Revision
Ready
Finalized
Overdue
Repository Published
```

---

# 56. Repository Dashboard

```text
Total Final Documents
Published
Internal Only
Pending Metadata
Missing Final PDF
Missing Abstract
```

---

# 57. Audit Events M7

```text
TA_CREATED
TA_METADATA_UPDATED
TA_SUPERVISOR_ASSIGNED
TA_SECTION_CREATED

TA_VERSION_UPLOADED
TA_VERSION_SUBMITTED
TA_REVIEW_STARTED
TA_COMMENT_ADDED
TA_REVISION_REQUESTED
TA_SECTION_APPROVED

TA_PROGRESS_UPDATED
TA_READY_FOR_FINALIZATION
TA_FINALIZATION_STARTED
TA_FINAL_COMPILED
TA_FINALIZATION_FAILED

TA_REPOSITORY_CREATED
TA_REPOSITORY_PUBLISHED
TA_REPOSITORY_ACCESS_CHANGED
```

---

# 58. Internal Events M7

Event yang diterbitkan:

```text
M7.TA_CREATED
M7.TA_DOCUMENT_SUBMITTED
M7.TA_SECTION_APPROVED
M7.TA_REVISION_REQUESTED
M7.TA_PROGRESS_UPDATED
M7.TA_DOCUMENT_READY
M7.TA_FINALIZED
M7.REPOSITORY_PUBLISHED
```

Event yang diterima:

```text
M1.SEMPRO_REVISION_CREATED
M1.SIDANG_REVISION_CREATED
M1.SIDANG_COMPLETED
```

---

# 59. Service Layer

```text
TaService
TaMetadataService
TaDocumentService
TaVersionService
TaReviewService
TaApprovalService
TaProgressService
TaFinalizationService
TaCompilationService
TaRepositoryService
```

---

# 60. Action Layer

```text
CreateTaAction
UpdateTaMetadataAction
UploadTaVersionAction
SubmitTaDocumentAction
AddTaCommentAction
RequestTaRevisionAction
ApproveTaSectionAction
CalculateTaProgressAction
FinalizeTaAction
CompileTaDocumentAction
PublishRepositoryAction
ChangeRepositoryAccessAction
```

---

# 61. Filament Resource

```text
TugasAkhirResource
TaDocumentResource
TaRepositoryResource
```

Tidak semua interaksi harus berupa CRUD biasa.

---

# 62. Custom Pages

```text
TaMonitoringPage
TaReviewPage
TaProgressPage
TaFinalizationPage
RepositoryDashboardPage
```

---

# 63. Livewire Components

```text
TaProgressTracker
TaSectionCard
TaVersionHistory
TaReviewPanel
TaCommentThread
TaFinalizationChecklist
TaRepositoryMetadataForm
```

---

# 64. Queue Jobs

```text
CompileTaDocumentJob
ConvertTaToPdfJob
GenerateTaChecksumJob
SendTaNotificationJob
CleanupTemporaryTaFileJob
```

Semua job tenant-aware.

---

# 65. Scheduler

Contoh:

```text
RevisionDeadlineReminder
InactiveTaReminder
PendingReviewReminder
TemporaryFileCleanup
RepositoryHealthCheck
```

---

# 66. Notification

Contoh template:

```text
TA_DOCUMENT_SUBMITTED
TA_REVISION_REQUIRED
TA_SECTION_APPROVED
TA_READY_FOR_FINALIZATION
TA_FINAL_DOCUMENT_READY
TA_REVISION_DEADLINE
```

---

# 67. Example Notification

```text
Title:
Bab 3 Memerlukan Revisi

Message:
Pembimbing telah memberikan catatan revisi pada Bab 3.
Silakan membuka Dokumen TA untuk melihat detail komentar.
```

---

# 68. Error Code

```text
TA_NOT_FOUND
TA_ACCESS_DENIED
TA_SECTION_NOT_FOUND
TA_VERSION_NOT_FOUND
TA_INVALID_FILE
TA_FILE_TOO_LARGE
TA_INVALID_TRANSITION
TA_NOT_READY_FOR_FINALIZATION
TA_UNRESOLVED_REVIEW
TA_COMPILATION_FAILED
TA_FINAL_FILE_NOT_FOUND
TA_REPOSITORY_NOT_READY
CROSS_TENANT_ACCESS_DENIED
```

---

# 69. Tenant Isolation

Contoh:

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.tugas_akhir
→ tenants/fasilkom/ta/...

feb.sifakueu.test
→ sifak_tenant_feb.tugas_akhir
→ tenants/feb/ta/...
```

Database dan storage sama-sama terisolasi.

---

# 70. Security

M7 harus menguji:

```text
Tenant isolation
IDOR
Unauthorized download
Path traversal
MIME spoofing
Large file abuse
Role bypass
Policy bypass
Signed URL expiry jika digunakan
Cross-tenant file access
```

---

# 71. QA Test Scenario — Ownership

```text
M7-TC-001
Mahasiswa membuka TA miliknya
Expected:
allowed.

M7-TC-002
Mahasiswa membuka TA mahasiswa lain
Expected:
403.

M7-TC-003
Dosen pembimbing membuka TA mahasiswa bimbingannya
Expected:
allowed.

M7-TC-004
Dosen non-pembimbing membuka TA
Expected:
ditolak sesuai policy.
```

---

# 72. QA Test Scenario — Versioning

```text
M7-TC-010
Mahasiswa upload Bab 1 pertama
Expected:
v1 dibuat.

M7-TC-011
Mahasiswa upload revisi
Expected:
v2 dibuat dan v1 tetap ada.

M7-TC-012
Approved version dioverwrite langsung
Expected:
ditolak / versi baru wajib dibuat.
```

---

# 73. QA Test Scenario — Review

```text
M7-TC-020
Mahasiswa submit Bab 2
Expected:
status SUBMITTED.

M7-TC-021
Pembimbing request revision
Expected:
REVISION_REQUIRED.

M7-TC-022
Pembimbing approve
Expected:
APPROVED.

M7-TC-023
Role tanpa permission approve
Expected:
403.
```

---

# 74. QA Test Scenario — Finalization

```text
M7-TC-030
Semua required section approved
Expected:
READY_FOR_FINALIZATION.

M7-TC-031
Masih ada section pending
Expected:
finalization ditolak.

M7-TC-032
Compilation berhasil
Expected:
final DOCX/PDF tersedia.

M7-TC-033
Compilation gagal
Expected:
status FAILED dan dapat retry.
```

---

# 75. QA Test Scenario — Repository

```text
M7-TC-040
Final document tersedia
Expected:
repository item dapat dibuat.

M7-TC-041
Belum final
Expected:
publish ditolak.

M7-TC-042
Access level INTERNAL
Expected:
public user tidak mendapat fulltext.
```

---

# 76. QA Test Scenario — Integrasi M1

```text
M7-TC-050
Dokumen belum ready
Expected:
M1 menerima document_ready = false.

M7-TC-051
Dokumen final siap
Expected:
M1 menerima document_ready = true.

M7-TC-052
M1 membuat revisi pasca sidang
Expected:
revision cycle baru dibuat.
```

---

# 77. QA Test Scenario — Integrasi M3

```text
M7-TC-060
Tidak ada aktivitas TA dalam threshold
Expected:
M3 dapat menghasilkan warning.

M7-TC-061
Deadline revisi lewat
Expected:
M3 dapat menghasilkan alert.
```

---

# 78. QA Test Scenario — Tenant Isolation

```text
M7-TC-070
FEB mencoba akses metadata TA FASILKOM
Expected:
ditolak.

M7-TC-071
FEB mencoba download file FASILKOM
Expected:
ditolak.

M7-TC-072
Queue compile tenant FASILKOM
Expected:
hanya storage/database FASILKOM digunakan.
```

---

# 79. Acceptance Criteria M7

M7 dinyatakan siap apabila:

- [ ] record TA dapat dibuat;
- [ ] metadata judul dapat dikelola;
- [ ] pembimbing dapat dikaitkan;
- [ ] struktur bagian dokumen tersedia;
- [ ] mahasiswa dapat upload file;
- [ ] versioning berjalan;
- [ ] versi lama tetap tersimpan;
- [ ] mahasiswa dapat submit ke pembimbing;
- [ ] pembimbing dapat review;
- [ ] pembimbing dapat memberi komentar;
- [ ] pembimbing dapat request revision;
- [ ] pembimbing dapat approve;
- [ ] status per bagian tersedia;
- [ ] progress dapat dihitung;
- [ ] finalization checklist tersedia;
- [ ] compile DOCX dapat dijalankan;
- [ ] generate PDF dapat dijalankan;
- [ ] final document tersimpan;
- [ ] checksum tersimpan;
- [ ] repository item dapat dibuat;
- [ ] access level repository berjalan;
- [ ] M1 dapat membaca readiness;
- [ ] M3 dapat membaca progress;
- [ ] M6 dapat membaca metadata yang diizinkan;
- [ ] audit log berjalan;
- [ ] notification berjalan;
- [ ] queue tenant-aware;
- [ ] scheduler tenant-aware;
- [ ] authorization file berjalan;
- [ ] tenant isolation database lulus;
- [ ] tenant isolation storage lulus.

---

# 80. Definition of Done M7

M7 dianggap selesai apabila:

1. migration M7 selesai;
2. model dan relation selesai;
3. service layer tersedia;
4. action layer tersedia;
5. permission tersedia;
6. policy tersedia;
7. Mahasiswa Panel terintegrasi;
8. Dosen Panel terintegrasi;
9. Admin Panel terintegrasi;
10. Pimpinan monitoring tersedia;
11. upload berjalan;
12. file validation berjalan;
13. versioning berjalan;
14. review berjalan;
15. comment berjalan;
16. revision cycle berjalan;
17. approval berjalan;
18. progress calculation berjalan;
19. finalization checklist berjalan;
20. compilation berjalan;
21. final PDF/DOCX tersedia;
22. repository berjalan;
23. access control repository berjalan;
24. audit log berjalan;
25. notification berjalan;
26. Queue tenant-aware;
27. Scheduler tenant-aware;
28. integrasi M1 berjalan;
29. integrasi M3 berjalan;
30. integrasi M6 berjalan;
31. functional test lulus;
32. authorization test lulus;
33. file security test lulus;
34. tenant isolation test lulus;
35. dokumentasi internal tersedia.

---

# 81. Urutan Implementasi M7

Urutan yang disarankan:

```text
1. Tugas Akhir Record
↓
2. Metadata TA
↓
3. Section Definition
↓
4. Document Upload
↓
5. File Validation
↓
6. Versioning
↓
7. Submit Workflow
↓
8. Review
↓
9. Comment
↓
10. Revision
↓
11. Approval
↓
12. Progress Calculation
↓
13. Finalization Checklist
↓
14. Compilation
↓
15. PDF Generation
↓
16. Final Document
↓
17. Repository
↓
18. Access Level
↓
19. Integrasi M1
↓
20. Integrasi M3
↓
21. Integrasi M6
↓
22. QA
```

---

# 82. Sprint Rekomendasi

## Sprint M7-1 — TA Foundation

```text
Tugas Akhir
Metadata
Pembimbing
Section
Permission
Policy
Audit
```

Output:

```text
Record TA dan struktur dokumen tersedia.
```

## Sprint M7-2 — Document & Versioning

```text
Upload
Validation
Storage
Versioning
History
```

Output:

```text
Mahasiswa dapat mengelola versi dokumen.
```

## Sprint M7-3 — Review Workflow

```text
Submit
Review
Comment
Revision
Approval
Notification
```

Output:

```text
Bimbingan dokumen berjalan.
```

## Sprint M7-4 — Progress & Finalization

```text
Progress
Readiness
Checklist
Compile
PDF/DOCX
Queue
```

Output:

```text
Dokumen final dapat dibuat.
```

## Sprint M7-5 — Repository

```text
Metadata Repository
Final File
Access Level
Archive
```

Output:

```text
Dokumen final masuk repositori.
```

## Sprint M7-6 — Integration & QA

```text
M1 Integration
M3 Integration
M6 Integration
Authorization
Tenant Isolation
File Security
Regression
```

Output:

```text
M7 siap menjadi fondasi M1.
```

---

# 83. Output Akhir M7

Setelah M7 selesai:

```text
Mahasiswa memiliki workspace TA
+
Dokumen tersimpan per bagian
+
Semua perubahan memiliki versi
+
Pembimbing dapat review
+
Komentar dan revisi dapat dilacak
+
Approval tersimpan
+
Progress dapat dimonitor
+
Dokumen final dapat digenerate
+
Repository tersedia
+
M1 dapat membaca readiness sidang
+
M3 dapat memonitor progress TA
```

---

# 84. Hubungan dengan Roadmap Berikutnya

Setelah:

```text
M5 ✓
M4 ✓
M7 ✓
```

modul berikutnya adalah:

```text
M1 — Sidang Sempro & TA
```

M1 dapat menggunakan:

```text
Mahasiswa dari Master Data
Dosen dari M5
Jadwal/availability terkait dari M4/M5
Dokumen TA dari M7
Status readiness dari M7
```

---

# 85. Kesimpulan

M7 bukan hanya tempat upload file skripsi.

M7 terdiri dari empat lapisan:

```text
DOCUMENT LAYER
├── Section
├── Upload
├── Versioning
└── Storage

REVIEW LAYER
├── Submit
├── Comment
├── Revision
└── Approval

FINALIZATION LAYER
├── Progress
├── Readiness
├── Compile
└── Final PDF/DOCX

REPOSITORY LAYER
├── Metadata
├── Access Level
├── Archive
└── Integration
```

Dengan struktur ini, M7 menjadi fondasi yang stabil untuk M1 Sidang Sempro & TA, M3 Monitoring & Alert, serta M6 Profiling Mahasiswa tanpa menduplikasi business logic.

---

**SIFAK — M7 Manajemen Dokumen TA & Repositori Digital — Detailed Specification v1.0**
