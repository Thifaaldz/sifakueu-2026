# M1 — SIDANG SEMINAR PROPOSAL & TUGAS AKHIR
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M1 — Sidang Seminar Proposal & Tugas Akhir  
**Tahap Implementasi:** Modul Bisnis Keempat setelah M5, M4, dan M7  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, M5 Profiling Dosen, M4 KRS & Penjadwalan, M7 Manajemen Dokumen TA  
**Tujuan:** Menjabarkan kebutuhan, alur, data, verifikasi berkas, penetapan pembimbing/penguji, penjadwalan, pelaksanaan, penilaian, revisi, berita acara, monitoring, hak akses, integrasi, acceptance criteria, dan strategi implementasi M1 secara detail.

---

# 1. Ringkasan Modul

M1 mengelola proses Seminar Proposal dan Sidang Tugas Akhir mahasiswa dari pendaftaran sampai proses dinyatakan selesai.

M1 mencakup dua jenis proses:

```text
SEMINAR PROPOSAL
+
SIDANG TUGAS AKHIR
```

Alur umum:

```text
Mahasiswa memenuhi syarat
↓
Daftar Sempro / Sidang TA
↓
Upload / tarik kelengkapan berkas
↓
Validasi otomatis
↓
Verifikasi Admin
↓
Penetapan Pembimbing / Penguji
↓
Rekomendasi kandidat dari M5
↓
Plotting dosen
↓
Pencarian slot
↓
Penjadwalan
↓
Notifikasi
↓
Pelaksanaan
↓
Input nilai
↓
Keputusan sidang
↓
Revisi
↓
Validasi revisi
↓
Berita Acara / Dokumen hasil
↓
Selesai
```

M1 tidak menduplikasi dokumen TA yang sudah dikelola M7.

M1 membaca status kesiapan dokumen dari M7.

---

# 2. Tujuan M1

M1 bertujuan untuk:

1. mengelola pendaftaran Sempro;
2. mengelola pendaftaran Sidang TA;
3. mengelola persyaratan dan kelengkapan berkas;
4. melakukan validasi kesiapan mahasiswa;
5. memudahkan admin melakukan verifikasi;
6. menghubungkan mahasiswa dengan pembimbing;
7. menghasilkan rekomendasi penguji dari M5;
8. mendukung plotting dosen pembimbing/penguji;
9. mencegah konflik jadwal;
10. menentukan ruangan dan waktu sidang;
11. mengirim notifikasi jadwal;
12. menyediakan monitoring mahasiswa yang sedang mengikuti proses sidang;
13. mengelola pelaksanaan sidang;
14. mengelola penilaian per penguji;
15. menghitung nilai akhir;
16. menyimpan keputusan sidang;
17. mengelola revisi setelah sidang;
18. mengintegrasikan revisi ke M7;
19. menghasilkan berita acara;
20. menghasilkan dokumen hasil sidang;
21. menyediakan dashboard Admin, Kaprodi, dan Pimpinan;
22. menyimpan histori proses;
23. menjaga tenant isolation;
24. mendukung audit dan traceability.

---

# 3. Scope M1

## 3.1 In Scope

```text
Pendaftaran Sempro
Pendaftaran Sidang TA
Persyaratan Sidang
Kelengkapan Berkas
Validasi Otomatis
Verifikasi Admin
Penetapan Pembimbing
Rekomendasi Penguji
Plotting Penguji
Cek Beban Dosen
Cek Availability
Jadwal Sidang
Ruangan
Notifikasi
Monitoring Peserta
Pelaksanaan Sidang
Penilaian
Catatan Penguji
Keputusan Sidang
Revisi
Deadline Revisi
Validasi Revisi
Berita Acara
Dokumen Hasil Sidang
Riwayat Sidang
Dashboard Monitoring
Audit Log
```

## 3.2 Out of Scope

Untuk M1 versi awal:

- pembayaran biaya sidang;
- transaksi keuangan;
- tanda tangan digital tersertifikasi jika layanan eksternal belum tersedia;
- video conference internal;
- plagiarism checking;
- AI sebagai pengambil keputusan kelulusan;
- publikasi repository penuh karena menjadi scope M7;
- nomor surat administrasi umum karena menjadi scope M2.

---

# 4. Jenis Sidang

M1 minimal mendukung:

```text
SEMPRO
SIDANG_TA
```

Sistem harus dirancang agar jenis sidang dapat dikembangkan tanpa membuat ulang seluruh workflow.

Contoh enum:

```text
SEMPRO
SIDANG_TA
```

---

# 5. Aktor M1

| Aktor | Peran |
|---|---|
| Mahasiswa | Mendaftar, melihat status, jadwal, hasil, dan revisi |
| Dosen Pembimbing | Melihat mahasiswa bimbingan, hadir pada sidang jika diperlukan, validasi revisi sesuai aturan |
| Dosen Penguji | Melihat jadwal, dokumen, input nilai, catatan dan revisi |
| Admin Prodi | Verifikasi berkas, plotting, penjadwalan, monitoring |
| Admin Fakultas/TU | Mendukung administrasi sidang sesuai permission |
| Kaprodi | Validasi/approval proses tertentu, monitoring, keputusan/override sesuai kebijakan |
| Dekan/WD | Monitoring agregat |
| M5 Service | Memberikan rekomendasi kandidat dosen |
| M4 Service | Menyediakan referensi availability/jadwal akademik |
| M7 Service | Menyediakan kesiapan dokumen dan menerima revisi pasca sidang |
| M3 Service | Menggunakan status sidang untuk monitoring dan alert |

---

# 6. Panel dan Menu

## 6.1 Mahasiswa Panel

```text
Sidang
├── Ringkasan
├── Seminar Proposal
│   ├── Persyaratan
│   ├── Pendaftaran
│   ├── Status
│   ├── Jadwal
│   ├── Hasil
│   └── Revisi
│
├── Sidang Tugas Akhir
│   ├── Persyaratan
│   ├── Pendaftaran
│   ├── Status
│   ├── Jadwal
│   ├── Hasil
│   └── Revisi
│
└── Riwayat Sidang
```

---

## 6.2 Dosen Panel

### Dosen Pembimbing

```text
Sidang Mahasiswa Bimbingan
├── Jadwal
├── Detail Mahasiswa
├── Dokumen
├── Hasil
└── Revisi
```

### Dosen Penguji

```text
Jadwal Menguji
├── Hari Ini
├── Mendatang
└── Riwayat

Penilaian
├── Detail Sidang
├── Dokumen
├── Rubrik
├── Nilai
├── Catatan
└── Revisi
```

---

## 6.3 Admin Panel

```text
Sidang
├── Dashboard
├── Pendaftaran
│   ├── Sempro
│   └── Sidang TA
├── Verifikasi Berkas
├── Mahasiswa Siap Sidang
├── Plotting Dosen
├── Rekomendasi Penguji
├── Jadwal
├── Ruangan
├── Monitoring Pelaksanaan
├── Penilaian
├── Revisi
├── Berita Acara
├── Riwayat
└── Laporan
```

---

## 6.4 Pimpinan Panel

```text
Monitoring Sidang
├── Pendaftaran
├── Kesiapan
├── Jadwal
├── Beban Penguji
├── Hasil
├── Revisi Pending
├── Kelulusan
└── Laporan
```

---

# 7. Dependensi Data

M1 menggunakan:

```text
Master Data
├── Mahasiswa
├── Dosen
├── Program Studi
├── Semester
├── Tahun Akademik
└── Ruangan

M5
├── Rumpun Ilmu
├── Keahlian
├── Beban Dosen
├── Recommendation Engine
└── Availability / Konsultasi

M4
├── Jadwal Dosen
└── Jadwal Ruangan bila relevan

M7
├── Tugas Akhir
├── Dokumen
├── Readiness
└── Final Document
```

---

# 8. Struktur Data Utama

Kelompok data M1:

```text
Jenis Sidang
Persyaratan
Pendaftaran
Berkas
Verifikasi
Dosen Sidang
Jadwal
Pelaksanaan
Rubrik
Nilai
Keputusan
Revisi
Berita Acara
History
```

---

# 9. sidang_types

```text
sidang_types
├── id
├── code
├── name
├── description
├── active
└── timestamps
```

Contoh:

```text
SEMPRO
SIDANG_TA
```

---

# 10. sidang_requirements

Menyimpan syarat per jenis sidang.

| Field | Keterangan |
|---|---|
| id | PK |
| sidang_type_id | Jenis sidang |
| code | Kode syarat |
| name | Nama syarat |
| requirement_type | system/file/manual |
| required | Wajib/tidak |
| source_module | M1/M4/M7/master |
| validation_rule | Rule opsional |
| sequence | Urutan |
| active | Status |
| timestamps | Audit |

Contoh:

```text
SEMPRO
├── mahasiswa aktif
├── SKS minimum
├── dokumen proposal siap
├── approval pembimbing
└── berkas administrasi lengkap

SIDANG_TA
├── mahasiswa aktif
├── dokumen TA ready
├── pembimbing valid
├── syarat akademik terpenuhi
└── berkas administrasi lengkap
```

---

# 11. sidang_registrations

```text
sidang_registrations
├── id
├── sidang_type_id
├── mahasiswa_id
├── tugas_akhir_id nullable
├── semester_id
├── tahun_akademik_id
├── registration_number
├── status
├── submitted_at
├── verified_at
├── verified_by
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
READY_FOR_PLOTTING
SCHEDULED
COMPLETED
CANCELLED
```

---

# 12. sidang_requirement_results

```text
sidang_requirement_results
├── id
├── registration_id
├── requirement_id
├── status
├── source_reference_type nullable
├── source_reference_id nullable
├── note nullable
├── checked_by nullable
├── checked_at nullable
└── timestamps
```

Status:

```text
PENDING
VALID
INVALID
WAIVED
```

---

# 13. sidang_files

Untuk file yang benar-benar milik proses M1.

```text
sidang_files
├── id
├── registration_id
├── requirement_id nullable
├── stored_file_id
├── file_type
├── status
├── uploaded_by
└── timestamps
```

Catatan:

```text
Dokumen TA utama
→ tetap dari M7

File administratif sidang
→ dapat disimpan M1
```

---

# 14. sidang_verifications

```text
sidang_verifications
├── id
├── registration_id
├── verifier_id
├── verification_type
├── status
├── note
├── verified_at
└── timestamps
```

---

# 15. sidang_assignments

Menyimpan dosen yang terlibat.

```text
sidang_assignments
├── id
├── registration_id
├── dosen_id
├── role
├── recommendation_id nullable
├── status
├── assigned_by
├── assigned_at
├── justification nullable
└── timestamps
```

Role:

```text
PEMBIMBING_1
PEMBIMBING_2
PENGUJI_1
PENGUJI_2
KETUA_SIDANG
SEKRETARIS
```

Role aktual dapat dikonfigurasi sesuai kebijakan fakultas.

---

# 16. sidang_schedules

```text
sidang_schedules
├── id
├── registration_id
├── tanggal
├── jam_mulai
├── jam_selesai
├── ruangan_id nullable
├── meeting_url nullable
├── mode
├── status
├── created_by
├── finalized_by nullable
└── timestamps
```

Mode:

```text
ONSITE
ONLINE
HYBRID
```

Status:

```text
DRAFT
CONFLICT
VALIDATED
FINAL
RESCHEDULED
CANCELLED
COMPLETED
```

---

# 17. sidang_schedule_histories

```text
sidang_schedule_histories
├── id
├── sidang_schedule_id
├── old_date
├── old_start
├── old_end
├── old_room_id
├── new_date
├── new_start
├── new_end
├── new_room_id
├── reason
├── changed_by
└── created_at
```

---

# 18. sidang_rubrics

```text
sidang_rubrics
├── id
├── sidang_type_id
├── code
├── name
├── weight
├── min_score
├── max_score
├── sequence
├── active
└── timestamps
```

Contoh:

```text
Penguasaan Materi
Metodologi
Kualitas Dokumen
Presentasi
Tanya Jawab
```

---

# 19. sidang_scores

```text
sidang_scores
├── id
├── registration_id
├── examiner_id
├── rubric_id
├── score
├── note nullable
├── submitted_at
└── timestamps
```

---

# 20. sidang_examiner_summaries

```text
sidang_examiner_summaries
├── id
├── registration_id
├── examiner_id
├── total_score
├── recommendation
├── general_note
├── finalized_at
└── timestamps
```

---

# 21. sidang_results

```text
sidang_results
├── id
├── registration_id
├── final_score
├── final_grade nullable
├── decision
├── decision_note nullable
├── decided_by nullable
├── decided_at
└── timestamps
```

Decision contoh:

```text
LULUS
LULUS_DENGAN_REVISI
MENGULANG
TIDAK_LULUS
DITUNDA
```

Rule aktual mengikuti kebijakan akademik.

---

# 22. sidang_revisions

```text
sidang_revisions
├── id
├── registration_id
├── examiner_id nullable
├── description
├── category nullable
├── deadline nullable
├── status
├── resolved_at nullable
├── validated_by nullable
└── timestamps
```

Status:

```text
OPEN
IN_PROGRESS
SUBMITTED
VALIDATED
REJECTED
CLOSED
```

---

# 23. sidang_minutes

Berita acara.

```text
sidang_minutes
├── id
├── registration_id
├── document_file_id nullable
├── generated_at
├── generated_by
├── status
└── timestamps
```

---

# 24. Workflow Pendaftaran

```text
DRAFT
↓
SUBMITTED
↓
UNDER_VERIFICATION
↓
VERIFIED
↓
READY_FOR_PLOTTING
↓
SCHEDULED
↓
COMPLETED
```

Jika kurang:

```text
UNDER_VERIFICATION
↓
REVISION_REQUIRED
↓
SUBMITTED
```

Jika dibatalkan:

```text
ANY VALID PRE-COMPLETION STATE
↓
CANCELLED
```

---

# 25. Workflow Verifikasi

```text
Registration Submitted
↓
Run Automatic Checks
↓
Admin Review
↓
All Requirements Valid?
    ├── Yes → VERIFIED
    └── No  → REVISION_REQUIRED
```

---

# 26. Automatic Requirement Validation

Sistem dapat memeriksa:

```text
Status mahasiswa
SKS minimum
Status akademik
Semester
Dokumen M7
Pembimbing
Approval dokumen
Berkas wajib
```

Rule aktual harus configurable.

---

# 27. Verifikasi Berkas

Admin melihat checklist:

```text
[✓] Status mahasiswa aktif
[✓] SKS minimum
[✓] Dokumen proposal/TA siap
[✓] Approval pembimbing
[✓] File administrasi
[ ] Persyaratan lain
```

Admin dapat:

```text
Verify
Reject / Revision Required
Add Note
Waive requirement jika punya permission
```

---

# 28. Waiver

Untuk kondisi khusus:

```text
requirement = WAIVED
```

Wajib menyimpan:

```text
reason
actor
timestamp
```

dan masuk audit.

---

# 29. Kesiapan Mahasiswa

M1 dapat menghitung status kesiapan:

```text
NOT_READY
PARTIALLY_READY
READY
```

Contoh:

```text
All mandatory requirements VALID/WAIVED
→ READY
```

---

# 30. Monitoring Kesiapan

Admin dashboard:

```text
Total Pendaftar
Belum Lengkap
Menunggu Verifikasi
Siap Plotting
Terjadwal
Sedang Revisi
Selesai
```

---

# 31. Integrasi M5 — Rekomendasi Penguji

Flow:

```text
Registration VERIFIED
↓
M1 kirim konteks topik TA
↓
M5 baca:
- rumpun ilmu
- keahlian
- beban
- riwayat
- availability
↓
Top-N kandidat penguji
↓
Admin/Kaprodi review
↓
Assign
```

---

# 32. Filter Kandidat Penguji

Minimal filter:

```text
Dosen ACTIVE
Bukan mahasiswa
Sesuai role
Tidak melanggar konflik kepentingan
Tidak overload jika rule melarang
Tidak bentrok jadwal
Bukan pembimbing jika kebijakan melarang
Tenant sama
```

---

# 33. Threshold Rekomendasi

Jika kandidat:

```text
score < threshold
```

dan tetap dipilih:

```text
justification wajib
```

Justifikasi diaudit.

---

# 34. Conflict of Interest

M1 harus dapat mendukung rule:

```text
Pembimbing tidak boleh menjadi penguji tertentu
Penguji tidak boleh duplicate
Penguji harus berbeda user
```

Aturan aktual disesuaikan kebijakan.

---

# 35. Penjadwalan Sidang

Input:

```text
Registration
Dosen assignment
Ruangan
Tanggal
Jam
Durasi
Mode
```

Sebelum final:

```text
Check dosen conflict
Check room conflict
Check academic schedule
Check participant conflict
```

---

# 36. Integrasi M4

M1 dapat membaca jadwal M4 untuk:

```text
Dosen
Ruangan
Mahasiswa
```

agar tidak terjadi bentrok dengan jadwal kuliah.

---

# 37. Hard Constraint Sidang

```text
Semua penguji tersedia
Pembimbing tersedia jika wajib
Ruangan tersedia
Mahasiswa tersedia
Tidak overlap
Durasi valid
Assignment lengkap
```

Hard constraint harus lolos sebelum status FINAL.

---

# 38. Soft Constraint Sidang

Contoh:

```text
Preferensi waktu dosen
Jam kerja ideal
Minim jeda
Distribusi beban sidang
```

Soft constraint dapat menghasilkan warning/skor.

---

# 39. Conflict Detection

Interval overlap:

```text
start_A < end_B
AND
start_B < end_A
```

Jenis konflik:

```text
EXAMINER_CONFLICT
SUPERVISOR_CONFLICT
ROOM_CONFLICT
STUDENT_CONFLICT
ACADEMIC_SCHEDULE_CONFLICT
```

---

# 40. Penjadwalan Semi-Otomatis

Versi awal:

```text
Admin memilih pendaftar
↓
Sistem tampilkan kandidat slot
↓
Sistem menunjukkan availability dosen
↓
Sistem menunjukkan ruang available
↓
Admin pilih
↓
Validate
↓
Finalize
```

Tidak harus langsung menggunakan optimization engine kompleks.

---

# 41. Auto-Suggestion Slot

Input:

```text
required participants
duration
active rooms
date range
working hours
M4 schedules
```

Output:

```text
Top available slots
```

---

# 42. Finalisasi Jadwal

Sebelum final:

```text
[✓] Registration verified
[✓] Penguji lengkap
[✓] Pembimbing sesuai rule
[✓] Tidak ada hard conflict
[✓] Ruang tersedia
[✓] Durasi valid
```

---

# 43. Notifikasi Jadwal

Penerima:

```text
Mahasiswa
Pembimbing
Penguji
Admin terkait
```

Channel:

```text
In-App
Email
WhatsApp opsional
```

---

# 44. Reschedule

Jadwal FINAL tidak boleh overwrite langsung.

Flow:

```text
Final Schedule
↓
Request Reschedule
↓
Reason
↓
Conflict Check
↓
Update
↓
Save History
↓
Notify all participants
```

---

# 45. Monitoring Pelaksanaan

Admin dapat melihat:

```text
Hari Ini
├── Belum Mulai
├── Berlangsung
├── Selesai
├── Ditunda
└── Bermasalah
```

---

# 46. Status Pelaksanaan

```text
SCHEDULED
READY
IN_PROGRESS
COMPLETED
POSTPONED
CANCELLED
```

---

# 47. Mulai Sidang

Action:

```text
Start Session
```

Sistem mencatat:

```text
started_at
started_by
```

---

# 48. Selesai Sidang

Action:

```text
Complete Session
```

Sistem memeriksa:

```text
nilai penguji
catatan
keputusan
```

sesuai requirement.

---

# 49. Penilaian

Setiap penguji hanya mengisi nilainya sendiri.

Flow:

```text
Penguji membuka sidang
↓
Rubrik tampil
↓
Isi skor
↓
Tambah catatan
↓
Submit
↓
Lock / Finalize
```

---

# 50. Validasi Nilai

```text
score >= min_score
score <= max_score
```

Semua item wajib harus terisi sebelum finalisasi penilai.

---

# 51. Perhitungan Nilai

Contoh:

```text
Nilai Penguji =
Σ(score_rubric × weight)
```

Jika lebih dari satu penguji:

```text
Final Score =
weighted/average score
```

Formula harus configurable.

---

# 52. Nilai Pembimbing

Jika kebijakan memasukkan nilai pembimbing:

```text
Final Score =
penguji component
+
pembimbing component
```

Bobot harus diletakkan pada config/database, bukan hardcoded.

---

# 53. Keputusan Sidang

Keputusan dapat berasal dari:

```text
nilai akhir
+
rule akademik
+
keputusan role berwenang
```

Sistem boleh memberikan suggested decision, tetapi final approval tetap human-in-the-loop.

---

# 54. Hasil Sidang

Mahasiswa dapat melihat setelah hasil dipublish:

```text
Status
Nilai akhir jika diizinkan
Keputusan
Catatan
Daftar Revisi
Deadline
```

---

# 55. Revisi Pasca Sidang

Flow:

```text
Sidang selesai
↓
Penguji membuat revisi
↓
M1 simpan revision items
↓
M7 membuka revision cycle
↓
Mahasiswa revisi dokumen
↓
Mahasiswa submit
↓
Dosen validasi
↓
Semua revisi selesai
↓
M1 Closed
```

---

# 56. Integrasi M7 untuk Revisi

Event:

```text
M1.SIDANG_REVISION_CREATED
↓
M7 Revision Cycle
```

M7 dapat mengirim:

```text
M7.REVISION_SUBMITTED
M7.REVISION_APPROVED
```

M1 memperbarui status item revisi.

---

# 57. Deadline Revisi

Setiap revision dapat:

```text
deadline = date
```

Scheduler memeriksa:

```text
OPEN revision
+
today > deadline
↓
OVERDUE
↓
Notification / M3 Alert
```

---

# 58. Berita Acara

Setelah sidang selesai:

```text
Registration
Schedule
Participants
Scores
Decision
Notes
↓
Generate Berita Acara
↓
PDF
```

---

# 59. Data Berita Acara

Contoh:

```text
Nomor/ID sidang
Nama mahasiswa
NIM
Judul
Tanggal
Waktu
Ruangan
Pembimbing
Penguji
Nilai
Keputusan
Catatan
```

Jika nomor dokumen formal memerlukan M2, M1 dapat memanggil layanan M2 pada tahap integrasi.

---

# 60. Document Generation

Dapat menggunakan:

```text
DomPDF
Browsershot
PHPWord
LibreOffice Headless
```

Dokumen:

```text
Berita Acara
Lembar Nilai
Daftar Hadir
Hasil Sidang
```

---

# 61. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M1-FR-001 | Sistem dapat mengelola jenis sidang. |
| M1-FR-002 | Sistem dapat mengelola persyaratan per jenis sidang. |
| M1-FR-003 | Mahasiswa dapat melihat persyaratan sidang. |
| M1-FR-004 | Mahasiswa dapat membuat pendaftaran Sempro. |
| M1-FR-005 | Mahasiswa dapat membuat pendaftaran Sidang TA. |
| M1-FR-006 | Sistem dapat menjalankan validasi otomatis persyaratan. |
| M1-FR-007 | Sistem dapat membaca readiness dokumen dari M7. |
| M1-FR-008 | Admin dapat memverifikasi kelengkapan. |
| M1-FR-009 | Admin dapat meminta perbaikan berkas. |
| M1-FR-010 | Sistem menyimpan checklist requirement. |
| M1-FR-011 | Sistem dapat menentukan readiness mahasiswa. |
| M1-FR-012 | Admin dapat melihat mahasiswa siap sidang. |
| M1-FR-013 | M1 dapat meminta rekomendasi penguji dari M5. |
| M1-FR-014 | Sistem menampilkan Top-N penguji. |
| M1-FR-015 | Admin/Kaprodi dapat memilih penguji. |
| M1-FR-016 | Kandidat di bawah threshold membutuhkan justifikasi. |
| M1-FR-017 | Sistem memvalidasi conflict of interest. |
| M1-FR-018 | Sistem dapat membuat draft jadwal. |
| M1-FR-019 | Sistem dapat membaca jadwal M4 untuk conflict check. |
| M1-FR-020 | Sistem memvalidasi konflik dosen. |
| M1-FR-021 | Sistem memvalidasi konflik ruangan. |
| M1-FR-022 | Sistem memvalidasi konflik mahasiswa. |
| M1-FR-023 | Admin dapat memfinalisasi jadwal. |
| M1-FR-024 | Sistem mengirim notifikasi jadwal. |
| M1-FR-025 | Sistem menyimpan histori reschedule. |
| M1-FR-026 | Admin dapat memonitor sidang hari berjalan. |
| M1-FR-027 | Penguji dapat melihat dokumen terkait sidang. |
| M1-FR-028 | Penguji dapat input nilai per rubrik. |
| M1-FR-029 | Penguji dapat input catatan. |
| M1-FR-030 | Penguji dapat membuat revisi. |
| M1-FR-031 | Sistem menghitung nilai akhir. |
| M1-FR-032 | Sistem menyimpan keputusan sidang. |
| M1-FR-033 | Mahasiswa dapat melihat hasil setelah dipublish. |
| M1-FR-034 | Sistem dapat mengelola revision cycle pasca sidang. |
| M1-FR-035 | M1 dapat terintegrasi dengan M7 untuk revisi. |
| M1-FR-036 | Sistem dapat memonitor deadline revisi. |
| M1-FR-037 | Sistem dapat membuat berita acara. |
| M1-FR-038 | Sistem dapat menghasilkan dokumen hasil sidang. |
| M1-FR-039 | Semua aktivitas penting dicatat audit log. |
| M1-FR-040 | Semua proses berjalan dalam tenant context. |

---

# 62. Business Rules M1

| ID | Rule |
|---|---|
| M1-BR-001 | Mahasiswa hanya dapat mendaftar sidang untuk dirinya sendiri. |
| M1-BR-002 | Pendaftaran hanya dapat disubmit jika minimum requirement terpenuhi. |
| M1-BR-003 | Dokumen TA utama berasal dari M7 dan tidak diduplikasi. |
| M1-BR-004 | Admin hanya memverifikasi mahasiswa pada scope-nya. |
| M1-BR-005 | Sidang tidak dapat dijadwalkan sebelum status VERIFIED/READY_FOR_PLOTTING. |
| M1-BR-006 | Penguji harus merupakan dosen aktif. |
| M1-BR-007 | Assignment tidak boleh melanggar conflict of interest. |
| M1-BR-008 | Pemilihan kandidat di bawah threshold wajib justification. |
| M1-BR-009 | Jadwal final tidak boleh memiliki hard conflict. |
| M1-BR-010 | Jadwal final yang berubah wajib menyimpan history. |
| M1-BR-011 | Penguji hanya dapat menginput nilai miliknya. |
| M1-BR-012 | Nilai yang sudah finalized tidak dapat diubah tanpa workflow resmi. |
| M1-BR-013 | Keputusan final harus mengikuti rule dan role berwenang. |
| M1-BR-014 | Revisi pasca sidang harus dilacak sampai selesai. |
| M1-BR-015 | Tenant A tidak dapat mengakses sidang Tenant B. |
| M1-BR-016 | Semua override/waiver wajib memiliki alasan dan audit. |
| M1-BR-017 | Sistem tidak boleh mengambil keputusan kelulusan final tanpa mekanisme human approval. |

---

# 63. Permission M1

```text
view_own_sidang
create_own_sidang_registration
update_own_sidang_registration
submit_own_sidang_registration
view_own_sidang_result

view_supervised_sidang
view_assigned_sidang
view_sidang_document

manage_sidang_requirements
verify_sidang_registration
waive_sidang_requirement

view_examiner_recommendation
request_examiner_recommendation
assign_examiner
approve_examiner_assignment

manage_sidang_schedule
finalize_sidang_schedule
reschedule_sidang

start_sidang
complete_sidang
monitor_sidang

input_sidang_score
finalize_own_sidang_score
input_sidang_note
create_sidang_revision

publish_sidang_result
manage_sidang_revision
validate_sidang_revision

generate_sidang_minutes
view_sidang_report
export_sidang_report
```

---

# 64. Role Mapping

## Mahasiswa

```text
view_own_sidang
create_own_sidang_registration
update_own_sidang_registration
submit_own_sidang_registration
view_own_sidang_result
```

## Dosen Pembimbing

```text
view_supervised_sidang
view_sidang_document
validate_sidang_revision
```

## Dosen Penguji

```text
view_assigned_sidang
view_sidang_document
input_sidang_score
finalize_own_sidang_score
input_sidang_note
create_sidang_revision
```

## Admin Prodi

```text
manage_sidang_requirements
verify_sidang_registration
request_examiner_recommendation
assign_examiner
manage_sidang_schedule
reschedule_sidang
monitor_sidang
generate_sidang_minutes
view_sidang_report
```

## Kaprodi

```text
view_examiner_recommendation
approve_examiner_assignment
finalize_sidang_schedule
publish_sidang_result
view_sidang_report
```

---

# 65. Policy / Data Scope

```text
Mahasiswa
→ hanya proses miliknya.

Dosen Pembimbing
→ mahasiswa bimbingannya.

Dosen Penguji
→ sidang yang ditugaskan.

Admin Prodi
→ mahasiswa prodi scope.

Kaprodi
→ prodi sendiri.

Tenant
→ database tenant aktif.
```

---

# 66. Dashboard Mahasiswa

Widget:

```text
Status Sempro
Status Sidang TA
Kelengkapan
Jadwal Terdekat
Hasil
Revisi Open
Deadline Revisi
```

---

# 67. Dashboard Admin

Widget:

```text
Pendaftar Baru
Menunggu Verifikasi
Berkas Tidak Lengkap
Siap Plotting
Belum Ada Penguji
Belum Terjadwal
Sidang Hari Ini
Revisi Pending
Selesai
```

---

# 68. Dashboard Kaprodi

Widget:

```text
Total Pendaftar
Ready
Scheduled
Completed
Pass Rate
Revision Pending
Examiner Load
Schedule Conflict
```

---

# 69. Dashboard Dosen Penguji

Widget:

```text
Sidang Hari Ini
Sidang Mendatang
Belum Dinilai
Revisi Open
Riwayat Menguji
```

---

# 70. Search dan Filter

## Pendaftaran

```text
Search:
NIM
Nama
Judul

Filter:
Jenis Sidang
Prodi
Semester
Status
Kelengkapan
Pembimbing
Penguji
```

## Jadwal

```text
Tanggal
Dosen
Ruangan
Prodi
Jenis Sidang
Status
Conflict
```

---

# 71. Integrasi Shared Services

## Audit Log

Mencatat:

```text
registration submitted
requirement verified
requirement waived
examiner assigned
schedule finalized
reschedule
score submitted
result published
revision created
revision validated
minutes generated
```

## Notification

Digunakan untuk:

```text
berkas perlu revisi
registration verified
assignment penguji
jadwal final
reschedule
hasil publish
revisi dibuat
deadline revisi
revisi approved
```

## Queue

Digunakan untuk:

```text
bulk recommendation
bulk conflict scan
document generation
mass notification
report generation
```

## Scheduler

Digunakan untuk:

```text
sidang reminder
revision deadline reminder
unverified registration reminder
schedule health check
```

---

# 72. Audit Events M1

```text
SIDANG_REGISTRATION_CREATED
SIDANG_REGISTRATION_SUBMITTED
SIDANG_REQUIREMENT_VALIDATED
SIDANG_REQUIREMENT_WAIVED
SIDANG_REGISTRATION_VERIFIED
SIDANG_REGISTRATION_REVISION_REQUESTED

SIDANG_EXAMINER_RECOMMENDATION_REQUESTED
SIDANG_EXAMINER_ASSIGNED
SIDANG_LOW_SCORE_JUSTIFICATION_ADDED

SIDANG_SCHEDULE_CREATED
SIDANG_CONFLICT_DETECTED
SIDANG_SCHEDULE_FINALIZED
SIDANG_RESCHEDULED

SIDANG_STARTED
SIDANG_SCORE_SUBMITTED
SIDANG_RESULT_FINALIZED
SIDANG_RESULT_PUBLISHED

SIDANG_REVISION_CREATED
SIDANG_REVISION_VALIDATED

SIDANG_MINUTES_GENERATED
SIDANG_COMPLETED
```

---

# 73. Internal Events M1

Event yang diterbitkan:

```text
M1.REGISTRATION_SUBMITTED
M1.REGISTRATION_VERIFIED
M1.EXAMINER_ASSIGNED
M1.SCHEDULE_FINALIZED
M1.SCHEDULE_CHANGED
M1.SIDANG_STARTED
M1.SIDANG_COMPLETED
M1.SIDANG_REVISION_CREATED
M1.SIDANG_RESULT_PUBLISHED
```

Event yang diterima:

```text
M5.RECOMMENDATION_GENERATED
M5.WORKLOAD_UPDATED

M7.TA_DOCUMENT_READY
M7.REVISION_SUBMITTED
M7.REVISION_APPROVED

M4.SCHEDULE_CHANGED
```

---

# 74. Service Layer

```text
SidangRegistrationService
SidangRequirementService
SidangVerificationService
SidangReadinessService
SidangAssignmentService
SidangRecommendationService
SidangScheduleService
SidangConflictService
SidangExecutionService
SidangScoringService
SidangResultService
SidangRevisionService
SidangDocumentService
SidangMonitoringService
```

---

# 75. Action Layer

```text
CreateSidangRegistrationAction
SubmitSidangRegistrationAction
ValidateSidangRequirementsAction
VerifySidangRegistrationAction
WaiveSidangRequirementAction

RequestExaminerRecommendationAction
AssignExaminerAction

CreateSidangScheduleAction
ValidateSidangScheduleAction
FinalizeSidangScheduleAction
RescheduleSidangAction

StartSidangAction
SubmitExaminerScoreAction
FinalizeSidangResultAction
PublishSidangResultAction

CreateSidangRevisionAction
ValidateSidangRevisionAction

GenerateSidangMinutesAction
```

---

# 76. Filament Resource

```text
SidangTypeResource
SidangRequirementResource
SidangRegistrationResource
SidangScheduleResource
SidangRubricResource
SidangResultResource
SidangRevisionResource
```

---

# 77. Custom Pages

```text
SidangDashboardPage
SidangVerificationPage
SidangReadinessPage
ExaminerRecommendationPage
SidangScheduleBoardPage
SidangMonitoringPage
SidangScoringPage
SidangRevisionMonitoringPage
```

---

# 78. Livewire Components

```text
SidangRequirementChecklist
SidangReadinessIndicator
ExaminerRecommendationTable
SidangScheduleCalendar
SidangConflictIndicator
SidangRubricForm
SidangScoreSummary
SidangRevisionTracker
```

---

# 79. Queue Jobs

```text
ValidateSidangRequirementsJob
GenerateExaminerRecommendationJob
RunSidangConflictScanJob
GenerateSidangMinutesJob
SendSidangNotificationJob
GenerateSidangReportJob
```

Semua job wajib tenant-aware.

---

# 80. Scheduler

Contoh:

```text
SidangReminder
RevisionDeadlineReminder
PendingVerificationReminder
UnscoredSidangReminder
SidangScheduleHealthCheck
```

---

# 81. Configuration

Contoh:

```text
sidang.sempro.duration_minutes
sidang.ta.duration_minutes

sidang.requirements
sidang.score_weights
sidang.pass_threshold
sidang.revision_default_days

sidang.examiner.count
sidang.examiner.min_matching_score

sidang.schedule.operational_hours
```

Rule akademik tidak boleh seluruhnya di-hardcode.

---

# 82. Error Code

```text
SIDANG_REGISTRATION_NOT_FOUND
SIDANG_NOT_ELIGIBLE
SIDANG_REQUIREMENT_INCOMPLETE
SIDANG_DOCUMENT_NOT_READY
SIDANG_UNAUTHORIZED_VERIFIER

SIDANG_EXAMINER_NOT_ELIGIBLE
SIDANG_EXAMINER_CONFLICT
SIDANG_CONFLICT_OF_INTEREST

SIDANG_SCHEDULE_CONFLICT
SIDANG_ROOM_CONFLICT
SIDANG_PARTICIPANT_CONFLICT

SIDANG_SCORE_INCOMPLETE
SIDANG_SCORE_ALREADY_FINALIZED

SIDANG_REVISION_NOT_FOUND
SIDANG_REVISION_OVERDUE

CROSS_TENANT_ACCESS_DENIED
```

---

# 83. Tenant Isolation

Contoh:

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.sidang_registrations
→ sifak_tenant_fasilkom.sidang_scores

feb.sifakueu.test
→ sifak_tenant_feb.sidang_registrations
→ sifak_tenant_feb.sidang_scores
```

Tidak ada cross-tenant query operasional.

---

# 84. Security

M1 harus menguji:

```text
Tenant isolation
Role bypass
Permission bypass
IDOR
Score manipulation
Mass assignment
Unauthorized result publishing
Unauthorized waiver
Schedule manipulation
File access
SQL injection
XSS
CSRF
```

---

# 85. QA Test Scenario — Pendaftaran

```text
M1-TC-001
Mahasiswa eligible daftar Sempro
Expected:
berhasil.

M1-TC-002
Dokumen belum ready
Expected:
submit ditolak / requirement invalid.

M1-TC-003
Mahasiswa mencoba daftar milik mahasiswa lain
Expected:
403.

M1-TC-004
Tenant FEB akses registration FASILKOM
Expected:
ditolak.
```

---

# 86. QA Test Scenario — Verifikasi

```text
M1-TC-010
Semua requirement valid
Expected:
status VERIFIED.

M1-TC-011
Satu required item invalid
Expected:
REVISION_REQUIRED.

M1-TC-012
Admin waive requirement
Expected:
alasan wajib dan audit tersimpan.
```

---

# 87. QA Test Scenario — Penguji

```text
M1-TC-020
M1 meminta kandidat
Expected:
Top-N dari M5.

M1-TC-021
Dosen nonaktif
Expected:
tidak dapat dipilih.

M1-TC-022
Pembimbing dipilih sebagai penguji ketika policy melarang
Expected:
ditolak.

M1-TC-023
Score kandidat di bawah threshold
Expected:
justification required.
```

---

# 88. QA Test Scenario — Jadwal

```text
M1-TC-030
Semua peserta available
Expected:
jadwal dapat divalidasi.

M1-TC-031
Penguji overlap dengan kelas M4
Expected:
conflict.

M1-TC-032
Ruangan overlap
Expected:
ROOM_CONFLICT.

M1-TC-033
Reschedule final schedule
Expected:
reason + history + notification.
```

---

# 89. QA Test Scenario — Penilaian

```text
M1-TC-040
Penguji menginput semua rubrik
Expected:
berhasil.

M1-TC-041
Skor di luar range
Expected:
ditolak.

M1-TC-042
Penguji mengubah nilai penguji lain
Expected:
403.

M1-TC-043
Nilai sudah finalized lalu diubah langsung
Expected:
ditolak.
```

---

# 90. QA Test Scenario — Revisi

```text
M1-TC-050
Sidang menghasilkan revisi
Expected:
revision item dibuat.

M1-TC-051
M7 revision approved
Expected:
M1 revision dapat ditutup.

M1-TC-052
Deadline lewat
Expected:
status overdue / alert.
```

---

# 91. QA Test Scenario — Berita Acara

```text
M1-TC-060
Sidang complete
Expected:
berita acara dapat digenerate.

M1-TC-061
Data nilai belum lengkap
Expected:
generation diblok atau diberi warning sesuai rule.
```

---

# 92. QA Test Scenario — Tenant Isolation

```text
M1-TC-070
FASILKOM akses jadwal FEB
Expected:
ditolak.

M1-TC-071
Queue FASILKOM generate BA
Expected:
menggunakan DB/storage FASILKOM.

M1-TC-072
URL ID registration dimanipulasi
Expected:
tidak memperoleh data tenant lain.
```

---

# 93. Acceptance Criteria M1

M1 dinyatakan siap apabila:

- [ ] jenis sidang dapat dikonfigurasi;
- [ ] persyaratan dapat didefinisikan;
- [ ] mahasiswa dapat melihat checklist;
- [ ] mahasiswa dapat mendaftar Sempro;
- [ ] mahasiswa dapat mendaftar Sidang TA;
- [ ] readiness M7 dapat dibaca;
- [ ] validasi otomatis berjalan;
- [ ] admin dapat verifikasi;
- [ ] revisi berkas dapat diminta;
- [ ] waiver dapat dilakukan dengan permission;
- [ ] readiness mahasiswa dapat dihitung;
- [ ] M5 dapat memberikan kandidat penguji;
- [ ] conflict of interest dapat dicek;
- [ ] plotting penguji berjalan;
- [ ] jadwal dapat dibuat;
- [ ] konflik dosen terdeteksi;
- [ ] konflik ruang terdeteksi;
- [ ] konflik mahasiswa terdeteksi;
- [ ] integrasi M4 untuk conflict berjalan;
- [ ] jadwal dapat difinalisasi;
- [ ] reschedule memiliki history;
- [ ] notifikasi jadwal berjalan;
- [ ] monitoring sidang berjalan;
- [ ] penguji dapat menginput nilai;
- [ ] rubrik dapat dikonfigurasi;
- [ ] nilai akhir dapat dihitung;
- [ ] keputusan dapat disimpan;
- [ ] hasil dapat dipublish;
- [ ] revisi pasca sidang dapat dibuat;
- [ ] integrasi M7 revision cycle berjalan;
- [ ] deadline revisi dapat dimonitor;
- [ ] berita acara dapat digenerate;
- [ ] audit log berjalan;
- [ ] queue tenant-aware;
- [ ] scheduler tenant-aware;
- [ ] tenant isolation lulus;
- [ ] authorization test lulus.

---

# 94. Definition of Done M1

M1 dianggap selesai apabila:

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
11. requirement engine berjalan;
12. M7 readiness integration berjalan;
13. verifikasi berjalan;
14. M5 recommendation integration berjalan;
15. examiner assignment berjalan;
16. M4 schedule conflict integration berjalan;
17. penjadwalan berjalan;
18. reschedule berjalan;
19. notification berjalan;
20. monitoring berjalan;
21. rubric scoring berjalan;
22. score finalization berjalan;
23. decision workflow berjalan;
24. revision workflow berjalan;
25. M7 post-sidang revision integration berjalan;
26. berita acara berjalan;
27. audit log berjalan;
28. Queue tenant-aware;
29. Scheduler tenant-aware;
30. functional test lulus;
31. authorization test lulus;
32. integration test lulus;
33. tenant isolation test lulus;
34. security test utama lulus;
35. dokumentasi internal tersedia.

---

# 95. Urutan Implementasi M1

Urutan yang disarankan:

```text
1. Sidang Type
↓
2. Requirement Definition
↓
3. Registration
↓
4. Requirement Validation
↓
5. M7 Readiness Integration
↓
6. Admin Verification
↓
7. Readiness Dashboard
↓
8. M5 Examiner Recommendation
↓
9. Examiner Assignment
↓
10. Conflict of Interest
↓
11. Schedule Model
↓
12. M4 Schedule Integration
↓
13. Conflict Detection
↓
14. Schedule Board
↓
15. Finalization
↓
16. Notification
↓
17. Execution Monitoring
↓
18. Rubric
↓
19. Scoring
↓
20. Decision
↓
21. Revision
↓
22. M7 Revision Integration
↓
23. Berita Acara
↓
24. Dashboard & Reports
↓
25. QA
```

---

# 96. Sprint Rekomendasi

## Sprint M1-1 — Registration & Requirement

```text
Sidang Type
Requirement
Registration
Checklist
Permission
Policy
Audit
```

Output:

```text
Mahasiswa dapat melakukan pendaftaran.
```

## Sprint M1-2 — Verification & Readiness

```text
Automatic Validation
M7 Readiness
Admin Verification
Revision Requirement
Waiver
Dashboard
```

Output:

```text
Mahasiswa READY dapat diketahui.
```

## Sprint M1-3 — Examiner Assignment

```text
M5 Recommendation
Top-N
Conflict of Interest
Workload
Threshold
Justification
Assignment
```

Output:

```text
Penguji dapat ditetapkan.
```

## Sprint M1-4 — Scheduling

```text
Schedule
M4 Conflict
Room
Availability
Reschedule
Notification
```

Output:

```text
Sidang dapat dijadwalkan tanpa hard conflict.
```

## Sprint M1-5 — Execution & Scoring

```text
Monitoring
Start/Complete
Rubric
Score
Decision
Publish Result
```

Output:

```text
Pelaksanaan dan penilaian berjalan.
```

## Sprint M1-6 — Revision & Documents

```text
Revision
Deadline
M7 Revision Cycle
Validation
Berita Acara
Final Documents
```

Output:

```text
Proses sidang sampai revisi selesai.
```

## Sprint M1-7 — Integration & QA

```text
M3 Integration
Reports
Security
Authorization
Tenant Isolation
Regression
```

Output:

```text
M1 siap untuk integrasi penuh SIFAK.
```

---

# 97. Output Akhir M1

Setelah M1 selesai:

```text
Mahasiswa dapat daftar Sempro/TA
+
Berkas dapat diverifikasi
+
Kesiapan dapat dipantau
+
Penguji dapat direkomendasikan
+
Penguji dapat diplot
+
Jadwal dapat dibuat
+
Bentrok dapat dicegah
+
Sidang dapat dimonitor
+
Nilai dapat diinput
+
Keputusan dapat disimpan
+
Revisi dapat ditelusuri
+
Berita acara dapat dibuat
+
M7 dapat menerima revisi pasca sidang
```

---

# 98. Hubungan dengan Roadmap Berikutnya

Setelah:

```text
M5 ✓
M4 ✓
M7 ✓
M1 ✓
```

modul berikutnya adalah:

```text
M6 — Profiling Mahasiswa & Rekomendasi Profil Lulusan
```

M6 dapat memanfaatkan data:

```text
KRS dari M4
Riwayat akademik
Topik TA dari M7
Status Sempro/Sidang dari M1
```

Setelah M6:

```text
M3 — Monitoring & Alert
```

kemudian:

```text
M2 — Surat Menyurat
```

---

# 99. Kesimpulan

M1 bukan hanya halaman pendaftaran dan jadwal sidang.

M1 terdiri dari lima lapisan:

```text
ELIGIBILITY LAYER
├── Requirement
├── Readiness
├── Verification
└── Waiver

ASSIGNMENT LAYER
├── Recommendation
├── Penguji
├── Pembimbing
└── Conflict of Interest

SCHEDULING LAYER
├── Slot
├── Ruangan
├── Conflict Detection
└── Reschedule

EXECUTION LAYER
├── Monitoring
├── Rubric
├── Score
└── Decision

POST-SIDANG LAYER
├── Revision
├── M7 Integration
├── Berita Acara
└── Completion
```

Dengan struktur ini, M1 terintegrasi dengan M5, M4, dan M7 serta menjadi sumber data penting untuk M3 tanpa menduplikasi business logic.

---

**SIFAK — M1 Sidang Seminar Proposal & Tugas Akhir — Detailed Specification v1.0**
