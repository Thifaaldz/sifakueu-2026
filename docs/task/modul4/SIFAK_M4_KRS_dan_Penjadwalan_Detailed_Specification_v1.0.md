# M4 — KRS & PENJADWALAN
## Detailed Module Specification — Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Modul:** M4 — KRS & Penjadwalan  
**Tahap Implementasi:** Modul Bisnis Kedua setelah M5  
**Arsitektur:** Multi-Tenant, Database per Tenant, Modular Monolith  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Dependensi Utama:** Master Data, Auth/RBAC, Shared Services, M5 Profiling Dosen & Rekomendasi Pengajaran  
**Tujuan:** Menjabarkan kebutuhan, alur, data, business rule, workflow, penjadwalan, validasi KRS, plotting dosen, hak akses, integrasi, acceptance criteria, dan strategi implementasi M4 secara detail.

---

# 1. Ringkasan Modul

M4 merupakan modul yang mengelola dua proses utama:

```text
KRS
+
Penjadwalan Akademik
```

KRS digunakan untuk:

```text
Mahasiswa memilih mata kuliah
↓
Sistem memvalidasi aturan akademik
↓
Dosen PA melakukan approval
↓
KRS menjadi final
```

Penjadwalan digunakan untuk:

```text
Mata kuliah yang dibuka
↓
Jumlah peminat
↓
Kebutuhan kelas
↓
M5 memberikan rekomendasi dosen
↓
Admin melakukan plotting
↓
Sistem menyusun jadwal
↓
Sistem memeriksa bentrok
↓
Jadwal difinalisasi
```

M4 tidak berdiri sendiri.

M4 menggunakan:

```text
Master Data
→ Mahasiswa
→ Dosen
→ Mata Kuliah
→ Kurikulum
→ Semester
→ Tahun Akademik
→ Ruangan

M5
→ Keahlian dosen
→ Preferensi dosen
→ Beban dosen
→ Recommendation Engine
```

Output M4 digunakan oleh:

```text
M3 Monitoring & Alert
M5 Profiling Dosen
M6 Profiling Mahasiswa
M1 Sidang (untuk availability jadwal dosen pada konteks tertentu)
```

---

# 2. Tujuan M4

M4 bertujuan untuk:

1. mengelola penawaran mata kuliah per semester;
2. mengelola pengisian KRS mahasiswa;
3. memvalidasi batas SKS;
4. memvalidasi mata kuliah prasyarat;
5. memvalidasi kurikulum mahasiswa;
6. memvalidasi duplikasi mata kuliah;
7. memvalidasi bentrok jadwal;
8. mendukung approval KRS oleh Dosen PA;
9. menghitung jumlah peminat mata kuliah;
10. menentukan kebutuhan kelas;
11. mengintegrasikan rekomendasi dosen dari M5;
12. melakukan plotting dosen pengampu;
13. mengelola alokasi ruangan;
14. membentuk jadwal kuliah;
15. mencegah bentrok dosen, mahasiswa, kelas, dan ruangan;
16. mencatat perubahan jadwal;
17. mengirim notifikasi perubahan;
18. menyediakan dashboard akademik untuk Admin Prodi dan Kaprodi;
19. mendukung monitoring proses KRS;
20. menghasilkan data terstruktur yang dapat digunakan M3 dan M6.

---

# 3. Scope M4

## 3.1 In Scope

M4 mencakup:

```text
Periode KRS
Penawaran Mata Kuliah
Pengisian KRS
Validasi SKS
Validasi Prasyarat
Validasi Kurikulum
Validasi Bentrok
Approval Dosen PA
Revisi KRS
Finalisasi KRS
Perhitungan Peminat
Pembentukan Kelas
Rekomendasi Dosen dari M5
Plotting Dosen
Penjadwalan Kuliah
Alokasi Ruang
Pengecekan Konflik
Perubahan Jadwal
Riwayat Jadwal
Notifikasi
Dashboard Akademik
Audit Log
```

---

## 3.2 Out of Scope

Untuk M4 versi awal:

- pembayaran UKT;
- integrasi ke sistem keuangan;
- absensi perkuliahan penuh;
- LMS;
- penilaian mata kuliah;
- payroll dosen;
- penjadwalan ujian UTS/UAS jika belum dimasukkan BRD;
- optimasi jadwal menggunakan AI kompleks;
- auto-decision tanpa approval manusia.

---

# 4. Aktor M4

| Aktor | Peran |
|---|---|
| Mahasiswa | Mengisi dan mengajukan KRS |
| Dosen PA | Melihat, menyetujui, menolak, atau meminta revisi KRS |
| Admin Prodi | Menyiapkan penawaran MK, kelas, plotting dosen, jadwal |
| Admin Fakultas/Tenant | Mengelola data ruang/periode jika diberikan permission |
| Kaprodi | Monitoring, approval tertentu, validasi plotting |
| Dosen Pengampu | Melihat jadwal mengajar |
| M5 Service | Memberikan rekomendasi dosen berdasarkan kompetensi dan beban |
| M3 Service | Menggunakan data KRS/jadwal untuk monitoring |
| M6 Service | Menggunakan histori akademik dan mata kuliah untuk profiling mahasiswa |

---

# 5. Panel dan Menu

## 5.1 Mahasiswa Panel

Route:

```text
{tenant}.sifakueu.test/mahasiswa
```

Menu:

```text
KRS
├── Periode Aktif
├── Pilih Mata Kuliah
├── Ringkasan KRS
├── Status Approval
├── Revisi KRS
└── Riwayat KRS

Jadwal Kuliah
├── Jadwal Mingguan
├── Detail Mata Kuliah
└── Perubahan Jadwal
```

---

## 5.2 Dosen Panel

Untuk Dosen PA:

```text
Mahasiswa PA
├── KRS Menunggu Approval
├── Riwayat KRS
├── Catatan Akademik
└── Monitoring Mahasiswa
```

Untuk Dosen Pengampu:

```text
Jadwal Mengajar
├── Jadwal Mingguan
├── Mata Kuliah
└── Perubahan Jadwal
```

---

## 5.3 Admin Panel

```text
KRS & Penjadwalan
├── Periode KRS
├── Penawaran Mata Kuliah
├── Kelas Kuliah
├── KRS Mahasiswa
├── Approval Monitoring
├── Peminat Mata Kuliah
├── Plotting Dosen
├── Rekomendasi Dosen
├── Penjadwalan
├── Ruangan
├── Konflik Jadwal
├── Perubahan Jadwal
└── Laporan
```

---

## 5.4 Pimpinan Panel

```text
Dashboard Akademik
├── KRS
├── Peminat MK
├── Kelas
├── Beban Dosen
├── Konflik Jadwal
├── Mata Kuliah tanpa Dosen
└── Monitoring Penjadwalan
```

---

# 6. Dependensi Master Data

M4 membutuhkan data:

```text
Mahasiswa
Dosen
Program Studi
Mata Kuliah
Mata Kuliah Prasyarat
Kurikulum
Kurikulum Mata Kuliah
Semester
Tahun Akademik
Ruangan
```

Data tersebut tidak diduplikasi di M4.

---

# 7. Struktur Data Utama

M4 memiliki beberapa kelompok data:

```text
Periode
Penawaran
KRS
Kelas
Plotting
Jadwal
Conflict
History
```

---

# 8. periode_krs

| Field | Tipe | Keterangan |
|---|---|---|
| id | bigint/uuid | PK |
| semester_id | FK | Semester |
| prodi_id | FK nullable | Scope prodi |
| tanggal_mulai | datetime | Mulai pengisian |
| tanggal_selesai | datetime | Akhir pengisian |
| tanggal_revisi_mulai | datetime nullable | Mulai revisi |
| tanggal_revisi_selesai | datetime nullable | Akhir revisi |
| status | enum | draft/open/closed |
| created_by | FK | Admin |
| created_at | timestamp | Dibuat |
| updated_at | timestamp | Diubah |

---

# 9. penawaran_mata_kuliah

```text
penawaran_mata_kuliah
├── id
├── semester_id
├── prodi_id
├── mata_kuliah_id
├── kurikulum_id
├── kuota_default
├── minimal_peserta
├── maksimal_peserta
├── target_jumlah_kelas
├── status
├── created_by
└── timestamps
```

Status:

```text
DRAFT
OPEN
CLOSED
CANCELLED
```

---

# 10. krs

```text
krs
├── id
├── mahasiswa_id
├── semester_id
├── tahun_akademik_id
├── dosen_pa_id
├── total_sks
├── status
├── submitted_at
├── approved_at
├── approved_by
├── note
└── timestamps
```

Status:

```text
DRAFT
SUBMITTED
WAITING_PA
REVISION_REQUIRED
APPROVED
FINAL
CANCELLED
```

---

# 11. krs_detail

| Field | Keterangan |
|---|---|
| id | PK |
| krs_id | FK KRS |
| mata_kuliah_id | MK dipilih |
| penawaran_id | Penawaran |
| kelas_id | Kelas setelah plotting |
| sks | SKS saat transaksi |
| status | selected/approved/dropped |
| validation_status | valid/invalid/warning |
| validation_note | Keterangan |
| created_at | Timestamp |
| updated_at | Timestamp |

---

# 12. krs_validation_results

Tabel ini dapat digunakan untuk menyimpan hasil validasi detail.

```text
krs_validation_results
├── id
├── krs_id
├── krs_detail_id nullable
├── validation_code
├── severity
├── passed
├── message
├── metadata_json
└── created_at
```

Severity:

```text
INFO
WARNING
ERROR
```

---

# 13. kelas_kuliah

```text
kelas_kuliah
├── id
├── penawaran_id
├── kode_kelas
├── kapasitas
├── jumlah_peserta
├── status
├── created_by
└── timestamps
```

Contoh:

```text
SI-2026-DM-A
SI-2026-DM-B
```

---

# 14. plotting_dosen

```text
plotting_dosen
├── id
├── kelas_kuliah_id
├── dosen_id
├── rekomendasi_id nullable
├── role_pengampu
├── sks_beban
├── status
├── selected_by
├── justification nullable
└── timestamps
```

Role:

```text
utama
team_teaching
assistant
```

---

# 15. jadwal_kuliah

| Field | Keterangan |
|---|---|
| id | PK |
| kelas_kuliah_id | FK |
| dosen_id | FK |
| ruangan_id | FK |
| hari | Senin–Sabtu |
| jam_mulai | Time |
| jam_selesai | Time |
| minggu_mulai | Optional |
| minggu_selesai | Optional |
| mode | onsite/online/hybrid |
| status | draft/final/rescheduled/cancelled |
| created_by | User |
| updated_by | User |
| timestamps | Audit timestamps |

---

# 16. jadwal_history

```text
jadwal_history
├── id
├── jadwal_id
├── old_hari
├── old_jam_mulai
├── old_jam_selesai
├── old_ruangan_id
├── new_hari
├── new_jam_mulai
├── new_jam_selesai
├── new_ruangan_id
├── reason
├── changed_by
└── created_at
```

---

# 17. jadwal_conflicts

```text
jadwal_conflicts
├── id
├── jadwal_id
├── conflict_type
├── conflict_with_id
├── severity
├── message
├── resolved
├── resolved_by
├── resolved_at
└── timestamps
```

Jenis:

```text
DOSEN_CONFLICT
RUANG_CONFLICT
KELAS_CONFLICT
MAHASISWA_CONFLICT
CAPACITY_CONFLICT
```

---

# 18. Workflow KRS

Flow utama:

```text
DRAFT
↓
SUBMITTED
↓
WAITING_PA
↓
APPROVED
↓
FINAL
```

Jika perlu revisi:

```text
WAITING_PA
↓
REVISION_REQUIRED
↓
DRAFT
```

Jika periode selesai atau dibatalkan:

```text
DRAFT/SUBMITTED
↓
CANCELLED
```

---

# 19. Aturan Transisi KRS

| Dari | Ke | Aktor |
|---|---|---|
| DRAFT | SUBMITTED | Mahasiswa |
| SUBMITTED | WAITING_PA | Sistem |
| WAITING_PA | APPROVED | Dosen PA |
| WAITING_PA | REVISION_REQUIRED | Dosen PA |
| REVISION_REQUIRED | DRAFT | Mahasiswa/System |
| APPROVED | FINAL | Sistem/Admin sesuai rule |

---

# 20. Validasi KRS

Sebelum KRS dapat diajukan, sistem memvalidasi:

```text
Status Mahasiswa
Periode KRS
Kurikulum
Mata Kuliah Aktif
Prasyarat
Batas SKS
Duplikasi MK
MK sudah lulus
Bentrok jadwal
Kuota
Status akademik khusus
```

---

# 21. Validasi Status Mahasiswa

Mahasiswa harus berada pada status yang diperbolehkan.

Contoh:

```text
ACTIVE
→ boleh

CUTI
→ tidak boleh

LULUS
→ tidak boleh

NONAKTIF
→ tidak boleh
```

Aturan aktual mengikuti kebijakan akademik tenant.

---

# 22. Validasi Periode KRS

KRS hanya dapat diubah pada periode:

```text
OPEN
```

Jika:

```text
CLOSED
```

maka:

```text
Mahasiswa tidak dapat submit/update
```

kecuali ada permission khusus Admin.

---

# 23. Validasi Kurikulum

Sistem menentukan kurikulum mahasiswa berdasarkan:

```text
prodi
angkatan
kurikulum aktif/assigned
```

Mata kuliah yang dipilih harus:

```text
terdaftar dalam kurikulum
atau
diizinkan melalui aturan ekuivalensi/khusus
```

---

# 24. Validasi Batas SKS

Batas SKS dapat ditentukan dari kebijakan akademik.

Contoh aturan konfiguratif:

```text
IP semester sebelumnya
→ menentukan maksimal SKS
```

Contoh ilustrasi:

```text
IPS >= 3.00
→ maksimal 24 SKS

IPS 2.50–2.99
→ maksimal 21 SKS

IPS 2.00–2.49
→ maksimal 18 SKS

IPS < 2.00
→ maksimal 15 SKS
```

Nilai tersebut tidak boleh di-hardcode jika kebijakan universitas dapat berubah.

Gunakan konfigurasi:

```text
academic_rules.max_sks
```

---

# 25. Validasi Mata Kuliah Prasyarat

Contoh:

```text
Algoritma Lanjut
memerlukan
Algoritma Dasar >= C
```

Flow:

```text
Mahasiswa memilih MK
↓
Cari prasyarat
↓
Periksa histori nilai
↓
Lulus?
   Ya → lanjut
   Tidak → error
```

---

# 26. Validasi Duplikasi

Sistem menolak:

```text
MK sama dipilih dua kali
```

Sistem juga dapat memberi warning:

```text
MK sudah pernah lulus
```

jika pengambilan ulang memerlukan aturan khusus.

---

# 27. Validasi Bentrok Mahasiswa

Setelah jadwal kelas tersedia:

```text
MK A
Senin 08:00–10:00

MK B
Senin 09:30–11:30
```

Maka:

```text
CONFLICT
```

Sistem tidak boleh membiarkan finalisasi jika conflict severity = ERROR.

---

# 28. Approval Dosen PA

Dosen PA melihat:

```text
Mahasiswa
IPS/IPK
Total SKS
Daftar MK
Prasyarat
Warning
History
```

Action:

```text
Approve
Request Revision
Reject
Add Note
```

Dosen PA hanya dapat memproses mahasiswa yang menjadi scope-nya.

---

# 29. Workflow Penawaran Mata Kuliah

```text
DRAFT
↓
OPEN
↓
CLOSED
↓
CLASS_FORMATION
↓
PLOTTING
↓
SCHEDULING
↓
FINAL
```

---

# 30. Perhitungan Peminat

Setelah KRS terkumpul:

```text
jumlah_peminat =
jumlah mahasiswa yang memilih penawaran MK
```

Output:

```text
Data Mining
Peminat: 92
Kuota kelas: 35
```

Maka sistem dapat merekomendasikan:

```text
ceil(92 / 35)
= 3 kelas
```

---

# 31. Pembentukan Kelas

Contoh:

```text
Peminat = 92
Kapasitas = 35
Kebutuhan Kelas = 3
```

Sistem dapat membuat draft:

```text
A = 31
B = 31
C = 30
```

atau sesuai policy.

Admin tetap dapat menyesuaikan.

---

# 32. Business Rule Pembentukan Kelas

```text
M4-BR-001
Jumlah kelas harus mempertimbangkan jumlah peminat dan kapasitas.

M4-BR-002
Kelas di bawah minimal peserta dapat diberi status warning.

M4-BR-003
Kelas tidak boleh melebihi kapasitas final tanpa override berwenang.

M4-BR-004
Pembagian mahasiswa harus tetap menjaga tidak terjadi duplikasi assignment.
```

---

# 33. Integrasi M5 untuk Rekomendasi Pengampu

Flow:

```text
Kelas membutuhkan dosen
↓
M4 kirim request ke M5
↓
M5 membaca:
- rumpun
- keahlian
- riwayat mengajar
- publikasi
- sertifikasi
- preferensi
- beban
↓
M5 hasilkan Top-N
↓
M4 menampilkan kandidat
```

---

# 34. Contoh Rekomendasi

```text
Mata Kuliah:
Data Mining

Kelas:
A

Top Kandidat:
1. Dosen A — 91
2. Dosen B — 87
3. Dosen C — 78
```

Admin/Kaprodi memilih kandidat.

---

# 35. Plotting Dosen

Plotting terdiri dari:

```text
Kelas
↓
Kandidat M5
↓
Review Beban
↓
Review Konflik
↓
Select Dosen
↓
Save Plotting
```

---

# 36. Rule Plotting Dosen

```text
M4-BR-005
Dosen harus ACTIVE.

M4-BR-006
Dosen tidak boleh mengalami jadwal overlap.

M4-BR-007
Dosen overload mendapat warning atau pembatasan sesuai kebijakan.

M4-BR-008
Jika dipilih kandidat di bawah threshold M5, justification wajib.

M4-BR-009
Plotting final harus disetujui role berwenang sesuai workflow.

M4-BR-010
Satu kelas dapat memiliki lebih dari satu dosen jika team teaching diperbolehkan.
```

---

# 37. Jadwal Kuliah

Input penjadwalan:

```text
Kelas
Dosen
Ruangan
Hari
Jam
Durasi
Mode
Kapasitas
```

Sebelum disimpan final, sistem menjalankan conflict detection.

---

# 38. Conflict Detection

Minimal memeriksa:

```text
Dosen
Ruangan
Kelas
Mahasiswa
```

---

# 39. Konflik Dosen

Tidak boleh:

```text
Dosen A
Senin 08:00–10:00
MK X

Dosen A
Senin 09:00–11:00
MK Y
```

Formula interval overlap:

```text
start_A < end_B
AND
start_B < end_A
```

Jika true:

```text
CONFLICT
```

---

# 40. Konflik Ruangan

Ruangan yang sama tidak boleh dipakai oleh dua kelas pada interval yang overlap.

---

# 41. Konflik Kelas

Kelas yang sama tidak boleh memiliki dua jadwal aktif pada waktu yang sama.

---

# 42. Konflik Mahasiswa

Untuk kombinasi MK yang memiliki peserta sama, sistem harus dapat mendeteksi potensi bentrok setelah assignment kelas tersedia.

Untuk dataset besar, validasi dapat dijalankan:

```text
synchronous untuk satu perubahan
+
queue untuk batch validation
```

---

# 43. Kapasitas Ruangan

Validasi:

```text
kapasitas_ruang >= jumlah_peserta
```

Jika tidak:

```text
CAPACITY_CONFLICT
```

---

# 44. Mode Online/Hybrid

Jika:

```text
mode = online
```

maka ruangan fisik dapat nullable sesuai rule.

Jika:

```text
mode = hybrid
```

maka konfigurasi dapat menentukan ruang wajib atau tidak.

---

# 45. Jadwal Draft dan Final

Status:

```text
DRAFT
↓
VALIDATED
↓
FINAL
```

Jika ditemukan conflict:

```text
DRAFT
↓
CONFLICT
↓
RESOLVED
↓
VALIDATED
```

---

# 46. Perubahan Jadwal

Setelah jadwal final, perubahan tidak boleh overwrite tanpa histori.

Flow:

```text
Final Schedule
↓
Request Change
↓
Reason
↓
Conflict Check
↓
Update
↓
Save History
↓
Notification
```

---

# 47. Notifikasi Perubahan Jadwal

Penerima:

```text
Mahasiswa kelas
Dosen pengampu
Admin terkait
```

Channel:

```text
In-App
Email
WhatsApp opsional
```

---

# 48. Kalender Jadwal

Mahasiswa:

```text
Weekly View
Daily View
```

Dosen:

```text
Jadwal Mengajar
+
Jadwal Konsultasi M5
```

Admin:

```text
By Ruangan
By Dosen
By Prodi
By Hari
```

---

# 49. Functional Requirements Detail

| ID | Requirement |
|---|---|
| M4-FR-001 | Admin dapat membuat periode KRS. |
| M4-FR-002 | Admin dapat membuka/menutup periode KRS. |
| M4-FR-003 | Admin dapat membuat penawaran mata kuliah. |
| M4-FR-004 | Mahasiswa dapat melihat penawaran sesuai prodi/kurikulum. |
| M4-FR-005 | Mahasiswa dapat membuat KRS draft. |
| M4-FR-006 | Mahasiswa dapat menambah/menghapus MK sebelum submit. |
| M4-FR-007 | Sistem memvalidasi status mahasiswa. |
| M4-FR-008 | Sistem memvalidasi batas SKS. |
| M4-FR-009 | Sistem memvalidasi prasyarat MK. |
| M4-FR-010 | Sistem memvalidasi duplikasi MK. |
| M4-FR-011 | Sistem memvalidasi kurikulum. |
| M4-FR-012 | Sistem dapat memvalidasi bentrok jadwal. |
| M4-FR-013 | Mahasiswa dapat submit KRS. |
| M4-FR-014 | Dosen PA dapat melihat KRS mahasiswa PA. |
| M4-FR-015 | Dosen PA dapat approve KRS. |
| M4-FR-016 | Dosen PA dapat meminta revisi. |
| M4-FR-017 | Sistem menyimpan catatan approval/revisi. |
| M4-FR-018 | Sistem dapat memfinalisasi KRS. |
| M4-FR-019 | Sistem menghitung jumlah peminat MK. |
| M4-FR-020 | Sistem membantu menentukan kebutuhan kelas. |
| M4-FR-021 | Admin dapat membuat kelas kuliah. |
| M4-FR-022 | M4 dapat meminta rekomendasi dosen dari M5. |
| M4-FR-023 | Sistem menampilkan Top-N rekomendasi pengampu. |
| M4-FR-024 | Admin dapat memilih dosen pengampu. |
| M4-FR-025 | Sistem menyimpan justifikasi kandidat di bawah threshold. |
| M4-FR-026 | Sistem dapat menghitung beban dari plotting ke M5. |
| M4-FR-027 | Admin dapat membuat jadwal kuliah. |
| M4-FR-028 | Sistem memvalidasi konflik dosen. |
| M4-FR-029 | Sistem memvalidasi konflik ruangan. |
| M4-FR-030 | Sistem memvalidasi konflik kelas. |
| M4-FR-031 | Sistem dapat mendeteksi konflik mahasiswa. |
| M4-FR-032 | Sistem memvalidasi kapasitas ruangan. |
| M4-FR-033 | Admin dapat memfinalisasi jadwal. |
| M4-FR-034 | Sistem menyimpan histori perubahan jadwal. |
| M4-FR-035 | Sistem mengirim notifikasi perubahan jadwal. |
| M4-FR-036 | Mahasiswa dapat melihat jadwalnya. |
| M4-FR-037 | Dosen dapat melihat jadwal mengajarnya. |
| M4-FR-038 | Kaprodi dapat melihat dashboard KRS/jadwal. |
| M4-FR-039 | Sistem mencatat audit aktivitas penting. |
| M4-FR-040 | Semua proses berjalan dalam tenant context. |

---

# 50. Business Rules M4

| ID | Rule |
|---|---|
| M4-BR-011 | KRS hanya dapat dibuat pada periode yang terbuka. |
| M4-BR-012 | Mahasiswa nonaktif tidak dapat mengajukan KRS. |
| M4-BR-013 | Mata kuliah harus berasal dari penawaran tenant aktif. |
| M4-BR-014 | Total SKS tidak boleh melebihi batas yang diizinkan. |
| M4-BR-015 | Prasyarat wajib terpenuhi sebelum MK dapat difinalisasi. |
| M4-BR-016 | Mata kuliah yang sama tidak boleh ada dua kali dalam satu KRS. |
| M4-BR-017 | KRS final tidak dapat diubah mahasiswa tanpa workflow revisi resmi. |
| M4-BR-018 | Dosen PA hanya memproses mahasiswa PA yang menjadi scope-nya. |
| M4-BR-019 | Kelas final tidak boleh melebihi kapasitas tanpa override resmi. |
| M4-BR-020 | Plotting dosen harus menggunakan dosen aktif. |
| M4-BR-021 | Jadwal final tidak boleh memiliki unresolved ERROR conflict. |
| M4-BR-022 | Perubahan jadwal final wajib menyimpan alasan. |
| M4-BR-023 | Perubahan jadwal final wajib dicatat dalam history dan audit log. |
| M4-BR-024 | Tenant A tidak dapat melihat KRS/jadwal Tenant B. |
| M4-BR-025 | Rekomendasi M5 tidak menjadi keputusan otomatis final. |

---

# 51. Permission M4

```text
view_krs
create_own_krs
update_own_krs
submit_own_krs
view_own_krs

view_pa_students_krs
approve_krs
request_krs_revision

manage_krs_period
manage_course_offering
view_krs_monitoring

view_course_demand
manage_class
manage_lecturer_plotting
view_lecturer_recommendation
accept_lecturer_recommendation

view_schedule
manage_schedule
finalize_schedule
reschedule_class

view_schedule_conflict
resolve_schedule_conflict

view_academic_dashboard
export_krs
export_schedule
```

---

# 52. Role Mapping

## Mahasiswa

```text
view_own_krs
create_own_krs
update_own_krs
submit_own_krs
view_schedule
```

## Dosen PA

```text
view_pa_students_krs
approve_krs
request_krs_revision
view_schedule
```

## Dosen Pengampu

```text
view_schedule
```

## Admin Prodi

```text
manage_krs_period
manage_course_offering
view_krs_monitoring
view_course_demand
manage_class
manage_lecturer_plotting
view_lecturer_recommendation
manage_schedule
view_schedule_conflict
resolve_schedule_conflict
export_krs
export_schedule
```

## Kaprodi

```text
view_academic_dashboard
view_krs_monitoring
view_course_demand
view_lecturer_recommendation
accept_lecturer_recommendation
finalize_schedule
```

---

# 53. Policy / Data Scope

Contoh:

```text
Mahasiswa
→ hanya KRS miliknya.

Dosen PA
→ hanya mahasiswa PA.

Admin Prodi
→ hanya prodi yang menjadi scope.

Dosen
→ hanya jadwal mengajarnya.

Kaprodi
→ data prodi sendiri.

Tenant
→ hanya database tenant aktif.
```

---

# 54. Integrasi dengan M5

M4 menerima:

```text
Top-N Dosen
Skor
Breakdown
Status Beban
Preferensi
Threshold
```

M4 mengirim kembali:

```text
Plotting diterima
SKS pengajaran
Semester
Kelas
```

agar M5 dapat memperbarui beban dosen.

---

# 55. Event M4 → M5

```text
M4.LECTURER_RECOMMENDATION_REQUESTED
M4.LECTURER_ASSIGNED
M4.SCHEDULE_FINALIZED
M4.SCHEDULE_CHANGED
```

---

# 56. Integrasi dengan M3

M3 dapat menggunakan:

```text
KRS belum submit
KRS belum approved
Jumlah SKS
Perubahan jadwal
Riwayat semester
```

Contoh:

```text
periode KRS hampir tutup
+
mahasiswa belum submit
↓
M3 Alert
```

---

# 57. Integrasi dengan M6

M6 menggunakan:

```text
Mata Kuliah diambil
Histori KRS
Kurikulum
Progress SKS
```

untuk profiling mahasiswa.

---

# 58. Integrasi Shared Services

## Audit Log

Wajib mencatat:

```text
KRS submitted
KRS approved
KRS revision requested
Class created
Lecturer assigned
Schedule created
Schedule finalized
Schedule changed
Conflict overridden
```

## Notification

Dipakai untuk:

```text
KRS approved
KRS revision
Schedule published
Schedule changed
```

## Queue

Dipakai untuk:

```text
bulk conflict detection
generate schedule report
bulk notification
large export
```

## Scheduler

Dipakai untuk:

```text
KRS deadline reminder
auto close period jika dikonfigurasi
schedule reminder
```

---

# 59. Audit Event M4

```text
KRS_CREATED
KRS_UPDATED
KRS_SUBMITTED
KRS_APPROVED
KRS_REVISION_REQUESTED
KRS_FINALIZED

COURSE_OFFERING_CREATED
COURSE_OFFERING_UPDATED

CLASS_CREATED
CLASS_UPDATED

LECTURER_RECOMMENDATION_REQUESTED
LECTURER_ASSIGNED
LOW_SCORE_JUSTIFICATION_ADDED

SCHEDULE_CREATED
SCHEDULE_CONFLICT_DETECTED
SCHEDULE_CONFLICT_RESOLVED
SCHEDULE_FINALIZED
SCHEDULE_CHANGED
```

---

# 60. Internal Event M4

Event yang diterbitkan:

```text
M4.KRS_SUBMITTED
M4.KRS_APPROVED
M4.KRS_FINALIZED
M4.COURSE_DEMAND_UPDATED
M4.CLASS_CREATED
M4.LECTURER_ASSIGNED
M4.SCHEDULE_FINALIZED
M4.SCHEDULE_CHANGED
```

Event yang diterima:

```text
M5.RECOMMENDATION_GENERATED
M5.WORKLOAD_UPDATED
```

---

# 61. Service Layer

Rekomendasi service:

```text
KrsService
KrsValidationService
AcademicRuleService
CourseOfferingService
CourseDemandService
ClassFormationService
LecturerPlottingService
ScheduleService
ScheduleConflictService
ScheduleChangeService
```

---

# 62. Action Layer

Contoh:

```text
CreateKrsAction
SubmitKrsAction
ApproveKrsAction
RequestKrsRevisionAction

OpenKrsPeriodAction
CloseKrsPeriodAction

CreateCourseOfferingAction
GenerateClassDraftAction

RequestLecturerRecommendationAction
AssignLecturerAction

CreateScheduleAction
ValidateScheduleAction
ResolveConflictAction
FinalizeScheduleAction
RescheduleClassAction
```

---

# 63. Filament Resource

```text
KrsPeriodResource
CourseOfferingResource
KrsResource
ClassResource
LecturerPlottingResource
ScheduleResource
RoomResource
```

---

# 64. Custom Page

```text
KrsMonitoringPage
CourseDemandPage
LecturerRecommendationPage
ScheduleBoardPage
ConflictDashboardPage
AcademicDashboardPage
```

---

# 65. Livewire Components

```text
KrsCoursePicker
KrsSummary
KrsValidationPanel
LecturerRecommendationTable
ScheduleCalendar
ConflictIndicator
RoomAvailabilityWidget
CourseDemandChart
```

---

# 66. Search dan Filter

## KRS

Filter:

```text
Prodi
Semester
Status
Dosen PA
Angkatan
```

Search:

```text
NIM
Nama
```

---

## Jadwal

Filter:

```text
Hari
Prodi
Dosen
Ruangan
Mata Kuliah
Kelas
Status
Conflict
```

---

# 67. Dashboard KRS

Widget:

```text
Total Mahasiswa
Belum Isi KRS
Draft
Menunggu PA
Revision
Approved
Final
```

---

# 68. Dashboard Penjadwalan

Widget:

```text
Total Penawaran MK
Total Kelas
Kelas Tanpa Dosen
Kelas Tanpa Ruang
Conflict Aktif
Dosen Overload
Jadwal Final
```

---

# 69. Dashboard Peminat

Contoh:

```text
Data Mining
Peminat 92
Kelas 3

Pemrograman Web
Peminat 67
Kelas 2

Enterprise Architecture
Peminat 28
Kelas 1
```

---

# 70. Penjadwalan Semi-Otomatis

Untuk versi awal, pendekatan yang aman adalah:

```text
System-assisted scheduling
```

bukan full automatic optimization.

Flow:

```text
Admin pilih kelas
↓
Sistem tampilkan:
- dosen available
- ruang available
- slot available
- conflict warning
↓
Admin pilih
↓
Sistem validasi
↓
Save
```

---

# 71. Auto-Suggestion Slot

Sistem dapat menghasilkan slot yang tersedia.

Input:

```text
Dosen
Durasi
Kapasitas
Mode
Hari aktif
Jam operasional
```

Output:

```text
Top available slots
```

Contoh:

```text
1. Senin 08:00 — R501
2. Selasa 10:00 — R402
3. Kamis 13:00 — LAB2
```

---

# 72. Constraint Penjadwalan

Hard constraint:

```text
Dosen tidak bentrok
Ruangan tidak bentrok
Kelas tidak bentrok
Kapasitas cukup
Jam valid
```

Soft constraint:

```text
Preferensi dosen
Distribusi kelas
Jam ideal
Minim jeda
```

Hard constraint wajib dipenuhi.

Soft constraint dapat menjadi skor/rekomendasi.

---

# 73. Skor Slot Opsional

Contoh:

```text
Slot Score =
preferensi_waktu
+
kecocokan_ruang
+
distribusi_beban
-
penalti_gap
```

Versi awal tidak harus menggunakan formula kompleks.

---

# 74. Tenant Isolation

Contoh:

```text
fasilkom.sifakueu.test
→ sifak_tenant_fasilkom.krs
→ sifak_tenant_fasilkom.jadwal_kuliah

feb.sifakueu.test
→ sifak_tenant_feb.krs
→ sifak_tenant_feb.jadwal_kuliah
```

Tidak boleh ada cross-tenant query operasional.

---

# 75. Queue Job

```text
RunBulkKrsValidationJob
RunScheduleConflictScanJob
GenerateScheduleReportJob
SendScheduleChangeNotificationJob
```

Semua membawa:

```text
tenant_id
```

---

# 76. Scheduler Job

Contoh:

```text
KrsDeadlineReminder
AutoCloseKrsPeriod
ScheduleReminder
ConflictHealthCheck
```

---

# 77. Configuration

Contoh:

```text
academic.max_sks_rules
academic.minimum_grade_prerequisite
academic.krs_revision_enabled
academic.class_minimum_size
academic.class_default_capacity
academic.schedule.operational_start
academic.schedule.operational_end
academic.schedule.days
```

---

# 78. Error Code

```text
KRS_PERIOD_CLOSED
STUDENT_NOT_ACTIVE
MAX_SKS_EXCEEDED
PREREQUISITE_NOT_MET
COURSE_DUPLICATE
COURSE_ALREADY_PASSED
KRS_NOT_EDITABLE
UNAUTHORIZED_PA

LECTURER_NOT_ACTIVE
LECTURER_OVERLOAD
LOW_RECOMMENDATION_SCORE

SCHEDULE_CONFLICT
ROOM_CONFLICT
LECTURER_CONFLICT
STUDENT_CONFLICT
ROOM_CAPACITY_EXCEEDED
```

---

# 79. QA Test Scenario — KRS

```text
M4-TC-001
Mahasiswa aktif membuat KRS saat periode open
Expected:
berhasil.

M4-TC-002
Mahasiswa nonaktif mencoba membuat KRS
Expected:
ditolak.

M4-TC-003
Mahasiswa memilih SKS melebihi batas
Expected:
ditolak/warning sesuai rule.

M4-TC-004
Prasyarat belum lulus
Expected:
ditolak.

M4-TC-005
MK duplicate
Expected:
ditolak.

M4-TC-006
Mahasiswa Tenant FEB akses KRS FASILKOM
Expected:
ditolak.
```

---

# 80. QA Test Scenario — Approval

```text
M4-TC-010
Dosen PA approve mahasiswa PA
Expected:
berhasil.

M4-TC-011
Dosen PA approve mahasiswa bukan scope
Expected:
403.

M4-TC-012
Dosen PA meminta revisi
Expected:
status REVISION_REQUIRED.
```

---

# 81. QA Test Scenario — Class Formation

```text
M4-TC-020
92 peminat, kapasitas 35
Expected:
sistem menyarankan 3 kelas.

M4-TC-021
kelas di bawah minimum peserta
Expected:
warning.

M4-TC-022
jumlah peserta > kapasitas final
Expected:
warning/error.
```

---

# 82. QA Test Scenario — M5 Integration

```text
M4-TC-030
M4 request rekomendasi dosen
Expected:
Top-N diterima.

M4-TC-031
dosen overload dipilih
Expected:
warning/penalti.

M4-TC-032
kandidat skor di bawah threshold dipilih
Expected:
justification required.
```

---

# 83. QA Test Scenario — Jadwal

```text
M4-TC-040
Dosen punya dua kelas overlap
Expected:
DOSEN_CONFLICT.

M4-TC-041
Ruangan dipakai dua kelas overlap
Expected:
ROOM_CONFLICT.

M4-TC-042
Kapasitas ruang lebih kecil dari peserta
Expected:
CAPACITY_CONFLICT.

M4-TC-043
Tidak ada conflict
Expected:
jadwal dapat divalidasi.
```

---

# 84. QA Test Scenario — Perubahan Jadwal

```text
M4-TC-050
Admin mengubah jadwal final
Expected:
alasan wajib.

M4-TC-051
Jadwal berubah
Expected:
history tersimpan.

M4-TC-052
Jadwal berubah
Expected:
notifikasi dikirim.
```

---

# 85. QA Test Scenario — Tenant Isolation

```text
M4-TC-060
FASILKOM mencoba membaca KRS FEB
Expected:
ditolak.

M4-TC-061
Job FEB berjalan setelah job FASILKOM
Expected:
tenant context tidak bocor.

M4-TC-062
URL tenant dimanipulasi
Expected:
data tenant lain tidak dapat diakses.
```

---

# 86. Acceptance Criteria M4

M4 dinyatakan siap apabila:

- [ ] periode KRS dapat dibuat;
- [ ] periode dapat dibuka/ditutup;
- [ ] penawaran mata kuliah dapat dibuat;
- [ ] mahasiswa melihat MK sesuai scope;
- [ ] mahasiswa dapat membuat KRS;
- [ ] batas SKS tervalidasi;
- [ ] prasyarat tervalidasi;
- [ ] kurikulum tervalidasi;
- [ ] duplikasi MK ditolak;
- [ ] KRS dapat disubmit;
- [ ] Dosen PA dapat approve/revisi;
- [ ] KRS dapat difinalisasi;
- [ ] jumlah peminat dapat dihitung;
- [ ] kebutuhan kelas dapat dihitung;
- [ ] kelas dapat dibuat;
- [ ] M5 dapat memberikan rekomendasi dosen;
- [ ] plotting dosen berjalan;
- [ ] kandidat di bawah threshold memerlukan justifikasi;
- [ ] jadwal dapat dibuat;
- [ ] konflik dosen terdeteksi;
- [ ] konflik ruang terdeteksi;
- [ ] konflik mahasiswa dapat diperiksa;
- [ ] kapasitas ruang tervalidasi;
- [ ] jadwal dapat difinalisasi;
- [ ] perubahan jadwal memiliki history;
- [ ] notifikasi perubahan berjalan;
- [ ] dashboard KRS tersedia;
- [ ] dashboard penjadwalan tersedia;
- [ ] audit log berjalan;
- [ ] tenant isolation teruji;
- [ ] integrasi M5 berjalan;
- [ ] data dapat digunakan M3;
- [ ] data dapat digunakan M6.

---

# 87. Definition of Done M4

M4 dianggap selesai apabila:

1. migration M4 selesai;
2. model dan relasi selesai;
3. service layer selesai;
4. action layer selesai;
5. permission tersedia;
6. policy tersedia;
7. Filament Resource tersedia;
8. Mahasiswa Panel terintegrasi;
9. Dosen PA Panel terintegrasi;
10. Admin Panel terintegrasi;
11. Pimpinan Panel terintegrasi;
12. KRS workflow berjalan;
13. KRS validation berjalan;
14. approval PA berjalan;
15. course demand berjalan;
16. class formation berjalan;
17. M5 recommendation terintegrasi;
18. plotting dosen berjalan;
19. schedule conflict detection berjalan;
20. schedule finalization berjalan;
21. schedule history berjalan;
22. notification berjalan;
23. audit log berjalan;
24. Queue tenant-aware;
25. Scheduler tenant-aware;
26. tenant isolation test lulus;
27. functional test lulus;
28. authorization test lulus;
29. integration M5 test lulus;
30. dokumentasi internal tersedia.

---

# 88. Urutan Implementasi M4

Urutan yang disarankan:

```text
1. Periode KRS
↓
2. Penawaran Mata Kuliah
↓
3. KRS Draft
↓
4. KRS Validation
↓
5. Approval Dosen PA
↓
6. Finalisasi KRS
↓
7. Course Demand
↓
8. Class Formation
↓
9. Integrasi M5 Recommendation
↓
10. Plotting Dosen
↓
11. Schedule Data Model
↓
12. Conflict Detection
↓
13. Room Allocation
↓
14. Schedule Board
↓
15. Finalisasi Jadwal
↓
16. Schedule Change Workflow
↓
17. Notification
↓
18. Dashboard
↓
19. Integrasi M3
↓
20. Integrasi M6
↓
21. QA
```

---

# 89. Sprint Rekomendasi

## Sprint M4-1 — KRS Foundation

```text
Periode KRS
Penawaran MK
KRS Draft
Permission
Policy
Audit
```

Output:

```text
Mahasiswa dapat memilih MK.
```

---

## Sprint M4-2 — KRS Validation & Approval

```text
SKS Validation
Prerequisite
Curriculum
Duplicate
Dosen PA Approval
Revision
```

Output:

```text
KRS dapat difinalisasi.
```

---

## Sprint M4-3 — Demand & Class

```text
Course Demand
Class Formation
Capacity
Enrollment Allocation
```

Output:

```text
Kebutuhan kelas terbentuk.
```

---

## Sprint M4-4 — M5 Integration & Plotting

```text
Request Recommendation
Top-N Candidate
Workload
Threshold
Justification
Lecturer Assignment
```

Output:

```text
Dosen pengampu dapat diplot.
```

---

## Sprint M4-5 — Scheduling

```text
Schedule
Room
Dosen Conflict
Room Conflict
Class Conflict
Student Conflict
```

Output:

```text
Draft jadwal bebas hard conflict.
```

---

## Sprint M4-6 — Finalization & Integration

```text
Final Schedule
Reschedule
History
Notification
Dashboard
M3 Integration
M6 Integration
QA
```

Output:

```text
M4 siap digunakan sebagai fondasi akademik.
```

---

# 90. Output Akhir M4

Setelah M4 selesai:

```text
Mahasiswa dapat mengisi KRS
+
Dosen PA dapat approve KRS
+
Peminat MK dapat dihitung
+
Kelas dapat dibentuk
+
M5 dapat merekomendasikan pengampu
+
Admin dapat plotting dosen
+
Jadwal dapat dibuat
+
Conflict dapat dicegah
+
Ruangan dapat dialokasikan
+
Mahasiswa dan dosen memperoleh jadwal
+
Perubahan jadwal dapat dilacak
```

---

# 91. Hubungan M4 dengan Roadmap Berikutnya

Setelah M4 stabil:

```text
M5
↓
M4
↓
M7
```

M7 akan menggunakan fondasi akademik yang sudah tersedia:

```text
Mahasiswa
Dosen
Prodi
Semester
Tahun Akademik
```

dan setelah M7 selesai, implementasi dapat dilanjutkan ke:

```text
M1 — Sidang Sempro & TA
```

---

# 92. Kesimpulan

M4 tidak hanya berisi form KRS dan tabel jadwal.

M4 terdiri dari empat lapisan utama:

```text
KRS LAYER
├── Penawaran
├── Pengisian
├── Validasi
└── Approval

CLASS LAYER
├── Peminat
├── Pembentukan Kelas
└── Kapasitas

LECTURER LAYER
├── M5 Recommendation
├── Plotting
└── Workload Feedback

SCHEDULING LAYER
├── Slot
├── Ruangan
├── Conflict Detection
├── Finalisasi
└── Perubahan Jadwal
```

Dengan struktur tersebut, M4 menjadi fondasi operasional akademik yang stabil dan dapat digunakan oleh M3, M5, M6, serta modul lanjutan tanpa menduplikasi business logic.

---

**SIFAK — M4 KRS & Penjadwalan — Detailed Specification v1.0**
