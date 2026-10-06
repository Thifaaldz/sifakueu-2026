# M5 — PROFILING DOSEN & REKOMENDASI PENGAJARAN
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M5 — Profiling Dosen & Rekomendasi Pengajaran  
**Tahap Implementasi:** Modul Bisnis Pertama setelah Foundation, Master Data, dan Shared Services  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Audit Log, Notification, Queue, Scheduler, File Service  
**Tujuan:** Menjabarkan kebutuhan produk, alur, data, rule, UI, service, permission, integrasi, algoritma, acceptance criteria, serta strategi implementasi M5 secara detail.

---

# 1. Ringkasan Modul

M5 adalah modul yang membangun profil dosen secara terstruktur dan mengolah data tersebut menjadi dasar rekomendasi pengajaran, pembimbing, penguji, serta pemetaan kompetensi dosen.

M5 menjadi salah satu fondasi penting SIFAK karena output-nya digunakan oleh:

```text
M4 KRS & Penjadwalan
→ rekomendasi dosen pengampu
→ perhitungan beban
→ plotting dosen

M1 Sidang Sempro & TA
→ rekomendasi pembimbing/penguji
→ validasi kesesuaian bidang
→ informasi ketersediaan dosen

M6 Profiling Mahasiswa
→ rekomendasi calon pembimbing
→ rekomendasi topik TA
```

M5 tidak menggantikan keputusan manusia. Sistem hanya memberikan rekomendasi, sedangkan keputusan akhir tetap dilakukan oleh pihak berwenang.

---

# 2. Tujuan M5

M5 bertujuan untuk:

1. menyimpan profil dosen secara lengkap;
2. memetakan dosen ke rumpun ilmu dan KBK;
3. menyimpan bidang keahlian spesifik dosen;
4. menyimpan pendidikan, sertifikasi, publikasi, dan pengalaman industri;
5. menyimpan histori mata kuliah yang pernah diajar;
6. menyimpan preferensi dosen terhadap mata kuliah;
7. menghitung beban dosen;
8. menghitung matriks kesesuaian dosen terhadap mata kuliah;
9. menghasilkan rekomendasi Top-N dosen pengampu;
10. membantu M1 menentukan calon pembimbing/penguji;
11. menyediakan jadwal konsultasi dosen;
12. menyimpan dan menampilkan lokasi/ketersediaan dosen sesuai aturan privasi;
13. menyediakan dashboard KBK untuk melihat kekuatan dan gap bidang ilmu;
14. mendukung evaluasi serta histori rekomendasi;
15. menyediakan data yang dapat digunakan kembali oleh M4, M1, dan M6.

---

# 3. Scope M5

## 3.1 In Scope

M5 mencakup:

```text
Profil Dosen
Rumpun Ilmu
KBK
Keahlian Dosen
Pendidikan
Sertifikasi
Publikasi
Pengalaman Industri
Riwayat Mengajar
Preferensi Mata Kuliah
Beban Dosen
Matriks Kesesuaian Dosen–MK
Rekomendasi Dosen Pengampu
Rekomendasi Calon Pembimbing/Penguji
Jadwal Konsultasi
Lokasi / Ketersediaan Dosen
Dashboard KBK
Gap Kompetensi
Histori Rekomendasi
Audit Perubahan Profil
```

## 3.2 Out of Scope

Untuk M5 versi awal:

- penilaian kinerja dosen formal universitas;
- payroll/honorarium;
- absensi kepegawaian penuh;
- HRIS;
- rekrutmen pegawai;
- scoring otomatis sebagai keputusan final tanpa approval manusia;
- rekomendasi dosen lintas tenant;
- akses publik ke lokasi dosen.

---

# 4. Kebutuhan Fungsional dari BRD

Kebutuhan M5 tetap mempertahankan definisi BRD:

| ID | Kebutuhan |
|---|---|
| FR5.1 | Profil dosen memuat NIDN, jabatan akademik, pendidikan, rumpun ilmu, KBK, bidang keahlian spesifik, sertifikasi, publikasi, pengalaman industri. |
| FR5.2 | Sistem menyediakan matriks kesesuaian dosen ↔ mata kuliah dengan skor 0–100 berdasarkan rumpun, riwayat mengajar, publikasi terkait, dan pelatihan/sertifikasi. |
| FR5.3 | Sistem menghasilkan rekomendasi otomatis dosen pengampu per mata kuliah dalam bentuk Top-N kandidat. |
| FR5.4 | Sistem menghitung beban dosen: SKS mengajar, bimbingan, penguji, penelitian untuk mencegah overload. |
| FR5.5 | Preferensi dosen terhadap mata kuliah yang ingin diampu digunakan sebagai input rekomendasi. |
| FR5.6 | Riwayat dan evaluasi digunakan untuk memperbaiki rekomendasi. |
| FR5.7 | Sistem mendukung histori/alumni tracking dosen jika dibutuhkan. |
| FR5.8 | Sistem menyediakan Dashboard KBK untuk melihat peta kekuatan dan gap rumpun ilmu fakultas. |
| FR5.9 | Sistem mengelola lokasi dan jadwal dosen: jadwal mengajar, jam konsultasi, dan lokasi/keberadaan dosen di kampus melalui geolocation agar mahasiswa terkait dapat melihat ketersediaan untuk bimbingan. |

---

# 5. Aktor M5

| Aktor | Peran pada M5 |
|---|---|
| Dosen | Mengelola profil sendiri, preferensi, jadwal konsultasi, keahlian tertentu |
| Admin Prodi | Menginput/memperbarui data dosen sesuai scope prodi |
| Admin Tenant/Fakultas | Mengelola data dasar dosen tenant |
| Kaprodi | Melihat profil, rekomendasi, beban, validasi plotting |
| KBK | Validasi rumpun ilmu, keahlian, kesesuaian bidang |
| Dekan/WD | Melihat dashboard agregat fakultas |
| Mahasiswa | Melihat jadwal konsultasi/lokasi dosen yang berkaitan sesuai policy |
| M4 Service | Menggunakan rekomendasi pengampu dan beban dosen |
| M1 Service | Menggunakan rekomendasi pembimbing/penguji dan availability |
| M6 Service | Menggunakan profil dosen untuk rekomendasi pembimbing/topik |
| Super Admin | Tidak mengelola profil akademik dosen secara default |

---

# 6. Panel dan Menu

## 6.1 Dosen Panel

Route:

```text
{tenant}.sifakueu.test/dosen
```

Menu M5:

```text
Profil Saya
├── Data Dasar
├── Pendidikan
├── Keahlian
├── Rumpun Ilmu
├── KBK
├── Sertifikasi
├── Publikasi
├── Pengalaman Industri
└── Riwayat Mengajar

Preferensi Pengajaran
Beban Saya
Jadwal Konsultasi
Lokasi / Kehadiran
Rekomendasi MK Saya
Riwayat Rekomendasi
```

## 6.2 Admin Panel

```text
Profiling Dosen
├── Daftar Dosen
├── Profil Lengkap
├── Rumpun Ilmu
├── KBK
├── Keahlian
├── Sertifikasi
├── Publikasi
├── Riwayat Mengajar
├── Preferensi
├── Beban Dosen
├── Matriks Kesesuaian
└── Rekomendasi Pengampu
```

## 6.3 Pimpinan / KBK Panel

```text
Dashboard KBK
├── Sebaran Dosen
├── Rumpun Ilmu
├── Kekuatan Bidang
├── Gap Kompetensi
├── Beban Dosen
├── Rekomendasi Pengampu
└── Validasi Kesesuaian
```

---

# 7. Struktur Data Utama

## 7.1 dosen

Data dasar berasal dari Master Data.

```text
dosen
├── id
├── user_id
├── nidn
├── nama
├── email
├── prodi_id
├── jabatan_akademik
├── pendidikan_terakhir
├── rumpun_ilmu_id
├── kbk_id
├── status_dosen
├── created_at
└── updated_at
```

---

# 8. dosen_profil

Tabel detail profil M5:

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint/uuid | PK |
| dosen_id | FK | Dosen |
| ringkasan_profil | text | Ringkasan akademik |
| fokus_keahlian | text | Deskripsi fokus |
| pengalaman_industri_ringkas | text | Ringkasan pengalaman |
| status_profil | enum | draft/verified |
| verified_by | FK nullable | Validator |
| verified_at | timestamp nullable | Waktu validasi |
| created_at | timestamp | Dibuat |
| updated_at | timestamp | Diubah |

---

# 9. dosen_keahlian

```text
dosen_keahlian
├── id
├── dosen_id
├── keahlian_id
├── level
├── sumber_bukti
├── status_validasi
├── validated_by
├── validated_at
└── timestamps
```

Level contoh:

```text
basic
intermediate
advanced
expert
```

Keahlian tidak harus menjadi skor final sendirian. Ia menjadi salah satu data pendukung.

---

# 10. keahlian

Master keahlian:

```text
keahlian
├── id
├── kode
├── nama
├── deskripsi
├── rumpun_ilmu_id
├── status
└── timestamps
```

Contoh:

```text
Data Mining
Software Engineering
Cyber Security
UI/UX
Machine Learning
Enterprise Architecture
Database
Networking
```

---

# 11. dosen_pendidikan

| Field | Keterangan |
|---|---|
| id | PK |
| dosen_id | FK |
| jenjang | S1/S2/S3 |
| institusi | Nama institusi |
| program_studi | Bidang |
| tahun_lulus | Tahun |
| bidang | Fokus bidang |
| dokumen_path | Bukti opsional |

---

# 12. dosen_sertifikasi

```text
dosen_sertifikasi
├── id
├── dosen_id
├── nama_sertifikasi
├── penerbit
├── bidang
├── nomor_sertifikat
├── tanggal_terbit
├── tanggal_berakhir
├── file_path
├── status_validasi
└── timestamps
```

---

# 13. dosen_publikasi

```text
dosen_publikasi
├── id
├── dosen_id
├── judul
├── tahun
├── jenis
├── jurnal_penerbit
├── doi_url
├── bidang
├── kata_kunci
├── sumber
└── timestamps
```

Sumber dapat berupa:

```text
manual
SINTA
Google Scholar
repository internal
```

Integrasi eksternal dapat ditambahkan pada fase lanjutan.

---

# 14. dosen_pengalaman_industri

```text
dosen_pengalaman_industri
├── id
├── dosen_id
├── instansi
├── posisi
├── bidang
├── tanggal_mulai
├── tanggal_selesai
├── deskripsi
└── timestamps
```

---

# 15. riwayat_mengajar

```text
riwayat_mengajar
├── id
├── dosen_id
├── mata_kuliah_id
├── semester_id
├── tahun_akademik_id
├── sks
├── kelas
├── evaluasi_rata_rata
├── jumlah_mahasiswa
├── status
└── timestamps
```

---

# 16. dosen_preferensi_mk

```text
dosen_preferensi_mk
├── id
├── dosen_id
├── mata_kuliah_id
├── tingkat_preferensi
├── catatan
├── semester_id
└── timestamps
```

Tingkat:

```text
1 = rendah
2 = sedang
3 = tinggi
```

Atau dapat dipetakan ke skor 0–100 pada service rekomendasi.

---

# 17. beban_dosen

```text
beban_dosen
├── id
├── dosen_id
├── semester_id
├── sks_mengajar
├── jumlah_bimbingan
├── jumlah_penguji
├── beban_penelitian
├── skor_beban
├── status_beban
└── timestamps
```

Status:

```text
LOW
NORMAL
HIGH
OVERLOAD
```

---

# 18. matriks_kesesuaian

```text
matriks_kesesuaian
├── id
├── dosen_id
├── mata_kuliah_id
├── semester_id
├── skor_rumpun
├── skor_riwayat
├── skor_publikasi
├── skor_sertifikasi
├── skor_preferensi
├── penalti_overload
├── skor_final
├── generated_at
└── timestamps
```

---

# 19. rekomendasi_pengampu

```text
rekomendasi_pengampu
├── id
├── mata_kuliah_id
├── semester_id
├── dosen_id
├── ranking
├── skor
├── alasan_ringkas
├── status
├── generated_at
└── timestamps
```

Status:

```text
generated
reviewed
accepted
rejected
superseded
```

---

# 20. jadwal_konsultasi

```text
jadwal_konsultasi
├── id
├── dosen_id
├── hari
├── jam_mulai
├── jam_selesai
├── ruang
├── tipe
├── status
└── timestamps
```

Tipe:

```text
onsite
online
hybrid
```

---

# 21. dosen_lokasi

```text
dosen_lokasi
├── id
├── dosen_id
├── latitude
├── longitude
├── accuracy
├── status_kehadiran
├── captured_at
└── timestamps
```

Lokasi hanya boleh diakses berdasarkan policy.

---

# 22. Formula Kesesuaian Dosen–Mata Kuliah

Formula dari rancangan SIFAK:

```text
Skor =
(0.35 × kesesuaian_rumpun)
+
(0.25 × riwayat_mengajar_sukses)
+
(0.20 × publikasi_terkait)
+
(0.10 × sertifikasi/pelatihan)
+
(0.10 × preferensi_dosen)
-
penalti_overload
```

Semua komponen dasar dinormalisasi ke skala:

```text
0–100
```

---

# 23. Komponen Skor

## 23.1 Kesesuaian Rumpun — 35%

Contoh:

```text
100
→ rumpun dosen sama langsung dengan rumpun MK

80
→ sangat dekat

60
→ beririsan

30
→ hanya sedikit relevansi

0
→ tidak relevan
```

Nilai ini sebaiknya berasal dari mapping yang dapat divalidasi KBK, bukan hanya string matching.

---

## 23.2 Riwayat Mengajar — 25%

Dapat memperhitungkan:

```text
pernah mengajar MK yang sama
jumlah semester mengajar
hasil evaluasi
konsistensi
```

Contoh sederhana:

```text
Skor Riwayat =
kombinasi pengalaman + evaluasi
```

---

## 23.3 Publikasi Terkait — 20%

Dapat memperhitungkan:

```text
kata kunci publikasi
bidang publikasi
jumlah publikasi relevan
recency
```

Versi awal tidak perlu AI kompleks.

Dapat menggunakan mapping/tag:

```text
mata kuliah
→ topik
→ keahlian
→ publikasi terkait
```

---

## 23.4 Sertifikasi/Pelatihan — 10%

Sertifikat dianggap relevan apabila bidangnya sesuai dengan mata kuliah atau rumpun.

---

## 23.5 Preferensi Dosen — 10%

Dosen dapat menyatakan:

```text
tinggi
sedang
rendah
```

yang kemudian dinormalisasi.

Contoh:

```text
tinggi = 100
sedang = 60
rendah = 20
```

---

# 24. Penalti Overload

Beban dosen harus memengaruhi ranking.

Contoh konsep:

```text
NORMAL
→ penalti 0

HIGH
→ penalti 10

OVERLOAD
→ penalti 25
```

Nilai final harus tetap:

```text
0–100
```

Contoh:

```text
max(0, min(100, skor_awal - penalti))
```

Threshold aktual harus dapat dikonfigurasi.

---

# 25. Contoh Perhitungan

Misalnya:

```text
Dosen A

rumpun       = 100
riwayat      = 80
publikasi    = 70
sertifikasi  = 80
preferensi   = 100
overload     = 10
```

Maka:

```text
0.35(100) = 35
0.25(80)  = 20
0.20(70)  = 14
0.10(80)  = 8
0.10(100) = 10

Subtotal = 87

Skor Akhir =
87 - 10
= 77
```

Hasil:

```text
Dosen A
Skor = 77
```

---

# 26. Top-N Recommendation

Sistem menghasilkan:

```text
Top 3
atau
Top 5
```

Contoh:

| Rank | Dosen | Skor | Status Beban | Alasan |
|---:|---|---:|---|---|
| 1 | Dosen A | 91 | Normal | Rumpun cocok, pernah mengajar |
| 2 | Dosen B | 86 | Normal | Publikasi sangat relevan |
| 3 | Dosen C | 82 | High | Kompetensi sesuai tetapi beban tinggi |

---

# 27. Human-in-the-Loop

Rekomendasi bukan keputusan final.

Flow:

```text
Sistem generate rekomendasi
↓
Admin/Kaprodi melihat Top-N
↓
Review
↓
Accept salah satu kandidat
atau
Pilih kandidat lain
↓
Jika kandidat di bawah threshold
→ wajib alasan/justifikasi
```

---

# 28. Threshold dan Justifikasi

Sesuai rule BRD, jika skor kesesuaian di bawah threshold maka perlu justifikasi.

Contoh:

```text
threshold = 70
```

Jika:

```text
skor = 62
```

maka:

```text
Admin memilih dosen
↓
Sistem meminta:
"Alasan pemilihan dosen di bawah threshold"
```

Justifikasi disimpan dan diaudit.

---

# 29. Workflow Profil Dosen

```text
DRAFT
↓
DATA_COMPLETED
↓
SUBMITTED
↓
VERIFIED
```

Jika perlu perbaikan:

```text
SUBMITTED
↓
REVISION_REQUIRED
↓
SUBMITTED
```

---

# 30. Workflow Rekomendasi Pengampu

```text
DATA READY
↓
CALCULATE
↓
GENERATED
↓
REVIEWED
↓
ACCEPTED
```

atau:

```text
REVIEWED
↓
REJECTED
↓
REGENERATE / MANUAL SELECT
```

---

# 31. Workflow Lokasi dan Konsultasi

```text
Dosen menentukan jadwal konsultasi
↓
Jadwal aktif
↓
Dosen hadir / presensi
↓
Location updated
↓
Mahasiswa terkait melihat availability
```

Mahasiswa tidak mendapat akses ke seluruh histori lokasi dosen.

---

# 32. Integrasi dengan M4

M4 membutuhkan M5 untuk:

```text
Mata Kuliah
↓
Request rekomendasi dosen
↓
M5 hitung matriks kesesuaian
↓
Top-N
↓
M4 menerima kandidat
↓
Admin memilih
↓
Plotting
↓
Jadwal kuliah
```

Event contoh:

```text
M4.DOSEN_RECOMMENDATION_REQUESTED
```

Output:

```text
M5.DOSEN_RECOMMENDATION_GENERATED
```

---

# 33. Integrasi dengan M1

M1 menggunakan:

```text
rumpun ilmu
keahlian
beban
availability
```

untuk membantu calon pembimbing/penguji.

Flow:

```text
Mahasiswa daftar sidang
↓
M1 meminta kandidat
↓
M5 filter dosen relevan
↓
M5 periksa beban
↓
M5 periksa bidang
↓
M1 menerima Top-N
↓
Admin menetapkan
```

---

# 34. Integrasi dengan M6

M6 menggunakan M5 untuk:

```text
Topik TA mahasiswa
↓
Bidang topik
↓
Cari dosen sesuai rumpun/keahlian
↓
Rekomendasi calon pembimbing
```

---

# 35. Integrasi dengan Shared Services

## Audit Log

Mencatat:

```text
profil berubah
keahlian berubah
KBK berubah
rumpun berubah
preferensi berubah
recommendation generated
recommendation accepted
justification submitted
jadwal konsultasi berubah
```

## Notification

Contoh:

```text
profil perlu verifikasi
recommendation accepted
jadwal konsultasi berubah
```

## Queue

Digunakan untuk:

```text
recalculate matching
bulk import publication
generate recommendation
```

## Scheduler

Digunakan untuk:

```text
recalculate beban
expire outdated location
refresh recommendation jika diperlukan
```

## File Service

Digunakan untuk:

```text
sertifikat
dokumen pendidikan
bukti keahlian
```

---

# 36. Permission M5

Rekomendasi permission:

```text
view_dosen_profile
view_own_dosen_profile
update_own_dosen_profile

create_dosen_profile
update_dosen_profile
verify_dosen_profile

view_dosen_keahlian
update_own_keahlian
validate_dosen_keahlian

view_rumpun
manage_rumpun
validate_rumpun

view_kbk
manage_kbk

view_publication
manage_own_publication

view_certification
manage_own_certification

view_teaching_history

view_own_preference
manage_own_preference

view_dosen_workload
view_own_workload

generate_dosen_recommendation
view_dosen_recommendation
review_dosen_recommendation
accept_dosen_recommendation

view_consultation_schedule
manage_own_consultation_schedule

view_related_dosen_location
update_own_location

view_kbk_dashboard
view_competency_gap
```

---

# 37. Role Mapping

## Dosen

```text
view_own_dosen_profile
update_own_dosen_profile
manage_own_publication
manage_own_certification
manage_own_preference
view_own_workload
manage_own_consultation_schedule
update_own_location
```

## Admin Prodi

```text
view_dosen_profile
create_dosen_profile
update_dosen_profile
view_dosen_workload
generate_dosen_recommendation
view_dosen_recommendation
```

## KBK

```text
view_dosen_profile
validate_dosen_keahlian
validate_rumpun
view_dosen_recommendation
review_dosen_recommendation
view_kbk_dashboard
view_competency_gap
```

## Kaprodi

```text
view_dosen_profile
view_dosen_workload
view_dosen_recommendation
review_dosen_recommendation
accept_dosen_recommendation
```

## Mahasiswa

```text
view_consultation_schedule
view_related_dosen_location
```

---

# 38. Policy / Data Scope

Permission tidak cukup.

Contoh:

```text
Mahasiswa
→ hanya boleh melihat lokasi dosen yang merupakan:
   pembimbing
   penguji
   atau terkait proses bimbingan resmi
```

Contoh:

```text
Admin Prodi
→ hanya dosen dalam scope prodi
```

Contoh:

```text
Dosen
→ hanya mengubah profil miliknya sendiri
```

---

# 39. Privacy Lokasi Dosen

Lokasi dosen adalah data sensitif operasional.

Aturan:

```text
1. Hanya lokasi terkait keberadaan kampus.
2. Tidak menampilkan tracking historis kepada mahasiswa.
3. Mahasiswa hanya melihat status availability atau lokasi yang relevan.
4. Akses harus berdasarkan relationship.
5. Data lokasi memiliki expiration.
6. Semua akses lokasi penting dapat diaudit.
```

Tampilan mahasiswa lebih baik:

```text
Available — Gedung A
Available — Ruang Dosen
Not Available
Online Consultation
```

daripada menampilkan koordinat mentah.

---

# 40. Dashboard Dosen

Widget:

```text
Profil Completion
Total SKS Mengajar
Jumlah Bimbingan
Jumlah Pengujian
Status Beban
Jadwal Konsultasi Hari Ini
Rekomendasi MK
```

---

# 41. Dashboard KBK

Widget:

```text
Total Dosen
Jumlah Dosen per Rumpun
Jumlah Dosen per Keahlian
Overload Dosen
Gap Kompetensi
MK tanpa kandidat kuat
Top Keahlian
```

Contoh:

```text
Data Science
8 dosen

Cyber Security
3 dosen

Enterprise Architecture
1 dosen

Gap:
Cloud Architecture
DevOps
```

---

# 42. Halaman Matriks Kesesuaian

Filter:

```text
Semester
Program Studi
Mata Kuliah
KBK
Rumpun
Status Beban
```

Tabel:

| Dosen | Rumpun | Riwayat | Publikasi | Sertifikasi | Preferensi | Penalti | Final |
|---|---:|---:|---:|---:|---:|---:|---:|
| A | 100 | 90 | 70 | 80 | 100 | 0 | 88 |
| B | 80 | 100 | 90 | 60 | 60 | 0 | 83 |
| C | 100 | 80 | 80 | 90 | 80 | 20 | 66 |

---

# 43. Halaman Rekomendasi Pengampu

```text
Mata Kuliah:
Data Mining

Top Candidates:
1. Dosen A — 91
2. Dosen B — 87
3. Dosen C — 78

[View Breakdown]
[Select]
[Reject Recommendation]
```

---

# 44. Explainability

Setiap rekomendasi harus dapat dijelaskan.

Contoh:

```text
Dosen A — 91

Rumpun               35/35
Riwayat Mengajar     23/25
Publikasi             17/20
Sertifikasi            8/10
Preferensi            10/10
Penalti                -2

Total                 91
```

Jangan hanya menampilkan:

```text
Score = 91
```

tanpa alasan.

---

# 45. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M5-FR-001 | Sistem dapat menampilkan daftar dosen tenant. |
| M5-FR-002 | Sistem dapat menampilkan profil lengkap dosen. |
| M5-FR-003 | Dosen dapat memperbarui bagian profil yang diizinkan. |
| M5-FR-004 | Admin dapat mengelola profil dosen dalam scope. |
| M5-FR-005 | Sistem dapat menyimpan pendidikan dosen. |
| M5-FR-006 | Sistem dapat menyimpan keahlian dosen. |
| M5-FR-007 | Sistem dapat menyimpan sertifikasi. |
| M5-FR-008 | Sistem dapat menyimpan publikasi. |
| M5-FR-009 | Sistem dapat menyimpan pengalaman industri. |
| M5-FR-010 | Sistem dapat menyimpan riwayat mengajar. |
| M5-FR-011 | Sistem dapat menyimpan preferensi MK. |
| M5-FR-012 | Sistem dapat menghitung beban dosen. |
| M5-FR-013 | Sistem dapat menentukan status overload. |
| M5-FR-014 | Sistem dapat menghitung komponen skor kesesuaian. |
| M5-FR-015 | Sistem dapat menghitung skor final. |
| M5-FR-016 | Sistem dapat membuat Top-N rekomendasi. |
| M5-FR-017 | Sistem menyimpan breakdown skor. |
| M5-FR-018 | Sistem menyimpan histori rekomendasi. |
| M5-FR-019 | Kaprodi/Admin dapat menerima rekomendasi. |
| M5-FR-020 | Sistem meminta justifikasi jika memilih kandidat di bawah threshold. |
| M5-FR-021 | Sistem dapat menyediakan data rekomendasi untuk M4. |
| M5-FR-022 | Sistem dapat menyediakan kandidat pembimbing/penguji untuk M1. |
| M5-FR-023 | Sistem dapat menyediakan kandidat pembimbing untuk M6. |
| M5-FR-024 | Dosen dapat mengelola jadwal konsultasi. |
| M5-FR-025 | Sistem dapat menyimpan lokasi keberadaan dosen sesuai aturan. |
| M5-FR-026 | Mahasiswa terkait dapat melihat availability dosen. |
| M5-FR-027 | Sistem menyediakan Dashboard KBK. |
| M5-FR-028 | Sistem menampilkan gap kompetensi/rumpun. |
| M5-FR-029 | Semua perubahan penting dicatat di audit. |
| M5-FR-030 | Semua query M5 berjalan dalam tenant context. |

---

# 46. Business Rules M5

| ID | Rule |
|---|---|
| M5-BR-001 | NIDN/NIDK dosen unik dalam tenant. |
| M5-BR-002 | Dosen nonaktif tidak dapat menjadi kandidat baru. |
| M5-BR-003 | Dosen tidak boleh direkomendasikan jika status tidak memenuhi syarat aktif. |
| M5-BR-004 | Rumpun dan KBK harus berasal dari tenant yang sama. |
| M5-BR-005 | Dosen dengan beban overload mendapat penalti. |
| M5-BR-006 | Rekomendasi hanya bersifat saran. |
| M5-BR-007 | Pilihan di bawah threshold wajib justifikasi. |
| M5-BR-008 | Justifikasi wajib diaudit. |
| M5-BR-009 | Mahasiswa tidak boleh melihat lokasi semua dosen. |
| M5-BR-010 | Lokasi hanya tersedia untuk hubungan akademik yang valid. |
| M5-BR-011 | Perubahan profil penting dapat memerlukan validasi Admin/KBK. |
| M5-BR-012 | Rekomendasi harus menyimpan breakdown skor. |
| M5-BR-013 | Rekomendasi lama tetap disimpan sebagai histori. |
| M5-BR-014 | Tenant A tidak boleh mengakses profil dosen Tenant B. |
| M5-BR-015 | Semua keputusan final tetap dilakukan manusia. |

---

# 47. Validasi

## Profil

```text
NIDN required
Nama required
Prodi valid
KBK valid
Rumpun valid
Email valid
```

## Preferensi

```text
MK harus aktif
MK harus berasal dari tenant sama
Tidak boleh duplicate preference aktif
```

## Jadwal Konsultasi

```text
jam selesai > jam mulai
hari valid
tidak duplicate exact slot
```

## Lokasi

```text
latitude valid
longitude valid
accuracy valid
timestamp valid
```

---

# 48. Search, Filter, Sort

Daftar dosen harus mendukung:

```text
Search:
Nama
NIDN
Keahlian

Filter:
Prodi
KBK
Rumpun
Jabatan
Status
Beban

Sort:
Nama
Beban
Skor
Jabatan
```

---

# 49. Import Data

M5 dapat mendukung import:

```text
Profil dosen
Keahlian
Publikasi
Sertifikasi
Riwayat mengajar
```

Format:

```text
CSV
XLSX
```

Import besar dijalankan lewat Queue.

---

# 50. Export

Export:

```text
Profil Dosen
Beban Dosen
Matriks Kesesuaian
Rekomendasi Pengampu
Gap Kompetensi
```

Format:

```text
XLSX
CSV
PDF ringkasan
```

---

# 51. Audit Event M5

Wajib audit:

```text
DOSEN_PROFILE_UPDATED
DOSEN_PROFILE_VERIFIED
EXPERTISE_ADDED
EXPERTISE_VALIDATED
RUMPUN_CHANGED
KBK_CHANGED
PREFERENCE_CHANGED
WORKLOAD_RECALCULATED
RECOMMENDATION_GENERATED
RECOMMENDATION_ACCEPTED
RECOMMENDATION_REJECTED
LOW_SCORE_JUSTIFICATION_ADDED
CONSULTATION_SCHEDULE_UPDATED
LOCATION_STATUS_UPDATED
```

---

# 52. Internal Events M5

Event yang dapat diterbitkan:

```text
M5.DOSEN_PROFILE_UPDATED
M5.DOSEN_PROFILE_VERIFIED
M5.WORKLOAD_UPDATED
M5.RECOMMENDATION_GENERATED
M5.RECOMMENDATION_ACCEPTED
M5.CONSULTATION_UPDATED
M5.DOSEN_LOCATION_UPDATED
```

Event yang dapat diterima:

```text
M4.RECOMMENDATION_REQUESTED
M4.SCHEDULE_FINALIZED

M1.EXAMINER_RECOMMENDATION_REQUESTED

M6.SUPERVISOR_RECOMMENDATION_REQUESTED
```

---

# 53. Service Layer

Rekomendasi service:

```text
DosenProfileService
DosenExpertiseService
DosenWorkloadService
DosenMatchingService
DosenRecommendationService
DosenConsultationService
DosenLocationService
KbkAnalyticsService
```

---

# 54. Action Layer

Contoh:

```text
UpdateDosenProfileAction
ValidateDosenExpertiseAction
CalculateDosenWorkloadAction
GenerateDosenRecommendationAction
AcceptDosenRecommendationAction
SaveLowScoreJustificationAction
UpdateConsultationScheduleAction
UpdateDosenLocationAction
```

---

# 55. Filament Resource / Page

Resource:

```text
DosenResource
KbkResource
RumpunIlmuResource
KeahlianResource
```

Custom page:

```text
DosenProfilePage
DosenWorkloadPage
MatchingMatrixPage
RecommendationPage
KbkDashboardPage
ConsultationSchedulePage
```

Tidak semua fungsi harus dipaksakan menjadi CRUD Resource.

---

# 56. Livewire Component

Komponen yang cocok:

```text
RecommendationBreakdown
WorkloadIndicator
ExpertiseTagEditor
ConsultationCalendar
AvailabilityWidget
MatchingMatrixTable
```

---

# 57. Tenant Isolation

Seluruh tabel M5 berada pada tenant database.

Contoh:

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.dosen
→ sifak_tenant_fasilkom.dosen_profil

feb.sifakueu.test
→ sifak_tenant_feb.dosen
→ sifak_tenant_feb.dosen_profil
```

Tidak ada query lintas tenant pada proses M5 biasa.

---

# 58. Queue Jobs

Contoh:

```text
RecalculateDosenMatchingJob
RecalculateDosenWorkloadJob
ImportDosenPublicationJob
GenerateRecommendationJob
```

Semua job wajib membawa:

```text
tenant_id
```

---

# 59. Scheduler

Contoh tugas:

```text
Recalculate workload
Expire old location
Refresh recommendation cache
Update status sertifikasi expired
```

---

# 60. Cache

Cache dapat digunakan untuk:

```text
Top recommendation
Dashboard KBK
Matching matrix summary
```

Key tenant-aware:

```text
tenant:fasilkom:m5:recommendation:mk:101
```

---

# 61. Non-Functional Requirements M5

## Security

- RBAC;
- policy;
- tenant isolation;
- file authorization;
- privacy geolocation.

## Performance

Target:

```text
Daftar profil:
< 3 detik pada kondisi normal

Recommendation:
dijalankan sync untuk dataset kecil
atau Queue untuk proses berat
```

## Auditability

Semua perubahan penting dapat dilacak.

## Explainability

Skor rekomendasi harus dapat dijelaskan.

## Maintainability

Bobot rekomendasi sebaiknya configurable.

---

# 62. Configuration

Contoh config:

```text
matching.weight.rumpun = 0.35
matching.weight.riwayat = 0.25
matching.weight.publikasi = 0.20
matching.weight.sertifikasi = 0.10
matching.weight.preferensi = 0.10

matching.threshold = 70

workload.high_threshold = ...
workload.overload_threshold = ...
```

Perubahan config penting diaudit.

---

# 63. QA Test Scenario — Profil

```text
M5-TC-001
Admin membuat profil dosen valid
Expected:
berhasil.

M5-TC-002
Duplicate NIDN
Expected:
ditolak.

M5-TC-003
Dosen mengubah profil dosen lain
Expected:
403.

M5-TC-004
FEB mengakses profil FASILKOM
Expected:
ditolak.
```

---

# 64. QA Test Scenario — Matching

```text
M5-TC-010
Generate skor kandidat valid
Expected:
skor 0–100.

M5-TC-011
Dosen overload
Expected:
penalti diterapkan.

M5-TC-012
Dosen nonaktif
Expected:
tidak masuk kandidat.

M5-TC-013
Tidak ada kandidat memenuhi threshold
Expected:
sistem tetap menampilkan kandidat terbaik
dengan warning/justifikasi.
```

---

# 65. QA Test Scenario — Human-in-the-Loop

```text
M5-TC-020
Admin pilih kandidat Top-1
Expected:
accepted.

M5-TC-021
Admin pilih kandidat skor < threshold
Expected:
wajib justifikasi.

M5-TC-022
Justifikasi kosong
Expected:
tidak dapat submit.
```

---

# 66. QA Test Scenario — Lokasi

```text
M5-TC-030
Mahasiswa melihat lokasi pembimbing
Expected:
allowed sesuai policy.

M5-TC-031
Mahasiswa melihat dosen tidak terkait
Expected:
403 / data tidak ditampilkan.

M5-TC-032
Lokasi expired
Expected:
status availability tidak dianggap current.
```

---

# 67. QA Test Scenario — Integrasi M4

```text
M5-TC-040
M4 meminta rekomendasi MK
Expected:
M5 mengembalikan Top-N.

M5-TC-041
M4 memilih kandidat
Expected:
status recommendation accepted.

M5-TC-042
Beban berubah setelah plotting
Expected:
recalculate tersedia untuk periode berikutnya.
```

---

# 68. QA Test Scenario — Integrasi M1

```text
M5-TC-050
M1 meminta kandidat penguji
Expected:
hanya dosen relevan/aktif.

M5-TC-051
Pembimbing sama dengan kandidat penguji
Expected:
M1/constraint layer menolak assignment final.
```

---

# 69. Acceptance Criteria M5

M5 dinyatakan siap apabila:

- [ ] profil dosen dapat dibuat;
- [ ] profil dosen dapat diperbarui sesuai permission;
- [ ] NIDN unik;
- [ ] pendidikan tersimpan;
- [ ] keahlian tersimpan;
- [ ] keahlian dapat divalidasi;
- [ ] rumpun ilmu tersimpan;
- [ ] KBK tersimpan;
- [ ] sertifikasi tersimpan;
- [ ] publikasi tersimpan;
- [ ] pengalaman industri tersimpan;
- [ ] riwayat mengajar tersimpan;
- [ ] preferensi MK dapat diisi;
- [ ] beban dosen dapat dihitung;
- [ ] status overload dapat ditentukan;
- [ ] matriks kesesuaian dapat dihitung;
- [ ] breakdown skor tersedia;
- [ ] Top-N recommendation tersedia;
- [ ] threshold dapat diterapkan;
- [ ] justifikasi kandidat di bawah threshold berjalan;
- [ ] histori rekomendasi tersimpan;
- [ ] Dashboard KBK tersedia;
- [ ] gap kompetensi dapat ditampilkan;
- [ ] jadwal konsultasi dapat dikelola;
- [ ] availability dapat ditampilkan;
- [ ] geolocation mengikuti policy;
- [ ] audit log berjalan;
- [ ] notification dapat digunakan;
- [ ] Queue tenant-aware;
- [ ] Scheduler tenant-aware;
- [ ] integrasi M4 dapat dilakukan;
- [ ] integrasi M1 dapat dilakukan;
- [ ] integrasi M6 dapat dilakukan;
- [ ] tenant isolation lulus pengujian.

---

# 70. Definition of Done M5

M5 dianggap selesai apabila:

1. migration selesai;
2. model dan relation selesai;
3. seeder/reference data selesai;
4. permission tersedia;
5. policy tersedia;
6. service layer tersedia;
7. action layer tersedia;
8. Filament Resource/Page tersedia;
9. Dosen Panel terintegrasi;
10. Admin Panel terintegrasi;
11. Pimpinan/KBK Panel terintegrasi;
12. formula matching tersedia;
13. explainability tersedia;
14. beban dosen tersedia;
15. rekomendasi Top-N tersedia;
16. threshold dan justifikasi tersedia;
17. audit log tersedia;
18. notification tersedia bila diperlukan;
19. queue dan scheduler tersedia;
20. geolocation privacy diterapkan;
21. functional test lulus;
22. authorization test lulus;
23. tenant isolation test lulus;
24. integration test dengan M4 interface lulus;
25. dokumentasi service/API internal tersedia.

---

# 71. Urutan Implementasi M5

Urutan pengerjaan yang disarankan:

```text
1. Model dasar Profil Dosen
↓
2. KBK + Rumpun Ilmu
↓
3. Keahlian
↓
4. Pendidikan
↓
5. Sertifikasi
↓
6. Publikasi
↓
7. Pengalaman Industri
↓
8. Riwayat Mengajar
↓
9. Preferensi MK
↓
10. Beban Dosen
↓
11. Matriks Kesesuaian
↓
12. Recommendation Engine
↓
13. Explainability
↓
14. Review + Human-in-the-Loop
↓
15. Dashboard KBK
↓
16. Jadwal Konsultasi
↓
17. Availability / Geolocation
↓
18. Integrasi M4
↓
19. Integrasi M1
↓
20. Integrasi M6
↓
21. QA
```

---

# 72. Sprint Rekomendasi

## Sprint M5-1 — Profile Foundation

```text
Dosen Profile
KBK
Rumpun
Keahlian
Permission
Policy
Audit
```

Output:

```text
Profil dosen lengkap dapat dikelola.
```

## Sprint M5-2 — Evidence & History

```text
Pendidikan
Sertifikasi
Publikasi
Pengalaman Industri
Riwayat Mengajar
Preferensi
```

Output:

```text
Data pendukung recommendation siap.
```

## Sprint M5-3 — Workload & Matching

```text
Beban Dosen
Matching Matrix
Formula
Top-N
Explainability
```

Output:

```text
Recommendation Engine v1 berjalan.
```

## Sprint M5-4 — Governance

```text
KBK Validation
Review Recommendation
Threshold
Justification
History
```

Output:

```text
Human-in-the-loop berjalan.
```

## Sprint M5-5 — Availability

```text
Jadwal Konsultasi
Location
Privacy Policy
Availability
```

Output:

```text
M5 siap dipakai M1.
```

## Sprint M5-6 — Integration

```text
M4 Interface
M1 Interface
M6 Interface
Queue
Scheduler
QA
```

Output:

```text
M5 production-ready untuk integrasi modul berikutnya.
```

---

# 73. Output Akhir M5

Setelah M5 selesai:

```text
Dosen memiliki profil kompetensi lengkap
+
Beban dosen dapat diketahui
+
MK dapat memperoleh kandidat dosen
+
Kandidat dapat diranking
+
Alasan ranking dapat dilihat
+
KBK dapat memvalidasi
+
Admin/Kaprodi tetap mengambil keputusan final
+
M4 dapat melakukan plotting dosen
+
M1 dapat mencari pembimbing/penguji
+
M6 dapat mencari calon pembimbing
+
Mahasiswa terkait dapat melihat availability dosen
```

---

# 74. Hubungan M5 dengan Roadmap Berikutnya

Setelah M5 stabil:

```text
M5
↓
M4 — KRS & Penjadwalan
```

Data berikut tidak perlu dibangun ulang di M4:

```text
Profil Dosen
Rumpun
KBK
Keahlian
Riwayat Mengajar
Preferensi
Beban
Recommendation Engine
```

M4 cukup menggunakan output M5.

---

# 75. Kesimpulan

M5 bukan hanya halaman CRUD profil dosen.

M5 terdiri dari tiga lapisan utama:

```text
PROFILE LAYER
├── Data Dosen
├── Keahlian
├── Pendidikan
├── Publikasi
└── Sertifikasi

ANALYTICS LAYER
├── Beban
├── Matching Matrix
├── Recommendation
└── Gap Kompetensi

OPERATIONAL LAYER
├── Jadwal Konsultasi
├── Availability
├── Location
└── Integration M1/M4/M6
```

Dengan struktur tersebut, M5 dapat menjadi fondasi yang stabil untuk implementasi M4, M1, dan M6 tanpa mengubah definisi tujuh modul utama pada BRD SIFAK.

---

**SIFAK — M5 Profiling Dosen & Rekomendasi Pengajaran — Detailed Specification v1.0**
