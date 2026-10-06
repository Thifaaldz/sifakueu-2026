# AKTOR, MENU, DAN HAK AKSES SIFAK
## Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis:** SIFAK v2.1 Multi-Tenant  
**Tujuan:** Mendefinisikan isi panel, menu, fungsi, dan hak akses setiap aktor pada SIFAK.  
**Prinsip Akses:** Tenant → Panel → Role → Permission → Policy/Data Scope

---

# 1. Struktur Akses Umum

SIFAK menggunakan lima panel utama:

| Panel | Aktor Utama |
|---|---|
| Super Admin Panel | Super Admin Platform |
| Mahasiswa Panel | Mahasiswa |
| Dosen Panel | Dosen, Dosen PA, Dosen Pembimbing, Dosen Penguji |
| Admin Panel | Admin Prodi, Admin Fakultas/TU, Admin Tenant |
| Pimpinan Panel | Kaprodi, Dekan/WD, LPM/Gugus Mutu, KBK, BAAK, Kepala Laboratorium |

Aktor tidak dibuat berdasarkan tenant. Semua tenant menggunakan panel yang sama, tetapi data yang tampil mengikuti tenant aktif.

Contoh:

```text
fasilkom.sifakueu.test/admin
→ Admin Panel
→ Database FASILKOM

feb.sifakueu.test/admin
→ Admin Panel yang sama
→ Database FEB
```

---

# 2. Super Admin Platform

## 2.1 Panel

```text
admin.sifakueu.test
```

## 2.2 Isi Menu

```text
Dashboard
Tenant Management
├── Semua Tenant
├── Tambah Fakultas
├── Tenant Pending
├── Tenant Suspended
└── Provisioning Failed

Domain & Subdomain
├── Domain Tenant
├── Reserved Subdomain
└── Domain Validation

Provisioning
├── Provisioning Queue
├── Provisioning History
├── Migration Status
└── Retry Provisioning

Tenant Health
├── Database Health
├── Storage Health
├── Queue Status
├── Scheduler Status
└── Last Error

Access Management
├── Super Admin
├── Platform Operator
└── Tenant Admin

Backup & Restore
├── Backup Tenant
├── Backup History
└── Restore Tenant

Logs & Audit
├── Platform Audit
├── Provisioning Log
├── Tenant Audit Summary
└── Error Log

System
├── Application Version
├── Schema Version
├── Queue Worker
├── Scheduler
└── Maintenance

Settings
├── Base Domain
├── Notification
├── Storage
└── Security
```

## 2.3 Hak Akses

### Boleh

- melihat seluruh tenant;
- membuat tenant baru;
- membuat subdomain tenant;
- menjalankan tenant provisioning;
- melihat nama database tenant;
- melihat status migration;
- melihat health tenant;
- suspend tenant;
- reactivate tenant;
- menjalankan backup;
- menjalankan restore;
- retry provisioning;
- melihat audit platform;
- mengelola akun Super Admin;
- mengelola konfigurasi global sistem.

### Tidak Boleh Secara Default

- melihat nilai mahasiswa;
- mengubah KRS mahasiswa;
- menginput nilai sidang;
- mengubah dokumen TA;
- membaca isi dokumen mahasiswa tanpa kebutuhan support;
- melakukan approval akademik tenant.

### Permission Utama

```text
view_tenants
create_tenant
update_tenant
suspend_tenant
reactivate_tenant
view_tenant_health
manage_domains
manage_reserved_domains
run_provisioning
retry_provisioning
view_provisioning_logs
run_backup
run_restore
view_platform_audit
manage_platform_users
manage_global_settings
```

---

# 3. Admin Tenant / Admin Fakultas Utama

Admin Tenant adalah admin awal yang dibuat ketika tenant selesai diprovisioning.

## 3.1 Panel

```text
{tenant}.sifakueu.test/admin
```

## 3.2 Isi Menu

```text
Dashboard
Profil Fakultas
Program Studi
Master Data
├── Mahasiswa
├── Dosen
├── Mata Kuliah
├── Ruangan
├── Kurikulum
├── Semester
└── Tahun Akademik

User & Role Tenant
├── User
├── Role
└── Permission Tenant

Monitoring
Laporan
Konfigurasi Tenant
```

## 3.3 Hak Akses

### Boleh

- mengelola data fakultas tenant sendiri;
- mengelola user tenant;
- mengelola master data;
- mengelola konfigurasi tenant;
- melihat laporan tenant;
- memberikan role sesuai kewenangan tenant.

### Tidak Boleh

- membuat tenant baru;
- mengakses tenant lain;
- mengubah base domain;
- melihat database tenant lain;
- mengelola Super Admin platform.

### Permission Utama

```text
manage_tenant_profile
manage_tenant_users
manage_tenant_roles
manage_master_data
view_tenant_reports
manage_tenant_settings
```

---

# 4. Mahasiswa

## 4.1 Panel

```text
{tenant}.sifakueu.test/mahasiswa
```

## 4.2 Isi Menu

```text
Dashboard
Profil Saya
KRS
Jadwal Kuliah
Sidang
├── Daftar Sempro
├── Daftar Sidang TA
├── Jadwal Sidang
├── Hasil
└── Revisi

Surat
├── Ajukan Surat
├── Status Surat
└── Riwayat Surat

Monitoring
├── Progress Akademik
├── Alert
└── Progress TA

Profil Lulusan
├── Skor CPL
├── Rekomendasi PLO
├── Rekomendasi MK
├── Rekomendasi Karier
└── Rekomendasi Topik TA

Bimbingan
├── Dosen Pembimbing
├── Jadwal Konsultasi
└── Ketersediaan Dosen

Dokumen TA
├── Judul
├── Daftar Isi
├── Daftar Pustaka
├── Bab 1
├── Bab 2
├── Bab 3
├── Bab 4
├── Bab 5
├── Log Revisi
└── File Final

Notifikasi
```

## 4.3 Hak Akses

### Boleh

- melihat dan mengubah profil sendiri;
- mengisi KRS;
- melihat status approval KRS;
- mendaftar sidang;
- mengunggah berkas sidang;
- melihat jadwal sidang;
- melihat hasil sidang;
- mengajukan surat;
- melihat alert milik sendiri;
- melihat skor CPL/PLO milik sendiri;
- melihat rekomendasi milik sendiri;
- mengelola dokumen TA milik sendiri;
- melihat komentar pembimbing;
- melihat dosen pembimbing/penguji yang berkaitan;
- melihat jadwal konsultasi dosen terkait.

### Tidak Boleh

- melihat data mahasiswa lain;
- approve KRS sendiri;
- menetapkan penguji;
- mengubah nilai;
- mengubah profil dosen;
- melihat lokasi semua dosen;
- mengakses dokumen TA mahasiswa lain.

### Permission Utama

```text
view_own_profile
update_own_profile
create_krs
view_own_krs
submit_sidang
view_own_sidang
create_surat
view_own_surat
view_own_alert
view_own_cpl
view_own_plo
view_own_recommendation
manage_own_ta
view_related_dosen_schedule
```

---

# 5. Dosen Umum

## 5.1 Panel

```text
{tenant}.sifakueu.test/dosen
```

## 5.2 Isi Menu

```text
Dashboard
Profil Saya
Jadwal Mengajar
Beban Dosen
Mahasiswa Bimbingan
Jadwal Konsultasi
Profil Keahlian
Riwayat Mengajar
Notifikasi
```

## 5.3 Hak Akses

### Boleh

- melihat profil sendiri;
- memperbarui data profil tertentu;
- melihat jadwal mengajar sendiri;
- melihat beban sendiri;
- mengelola jadwal konsultasi;
- melihat mahasiswa yang menjadi tanggung jawabnya;
- melihat rekomendasi mata kuliah yang relevan untuk dirinya.

### Tidak Boleh

- mengubah profil dosen lain;
- melihat data akademik seluruh mahasiswa;
- approve KRS jika bukan PA;
- input nilai sidang jika bukan penguji;
- approve dokumen TA jika bukan pembimbing.

### Permission Utama

```text
view_own_dosen_profile
update_own_dosen_profile
view_own_schedule
view_own_workload
manage_consultation_schedule
view_assigned_students
```

---

# 6. Dosen PA

Dosen PA menggunakan Dosen Panel yang sama, dengan permission tambahan.

## 6.1 Isi Menu Tambahan

```text
Mahasiswa PA
KRS Approval
Monitoring Akademik
Alert Mahasiswa PA
```

## 6.2 Hak Akses

### Boleh

- melihat mahasiswa PA yang menjadi tanggung jawabnya;
- melihat KRS mahasiswa PA;
- approve/reject KRS;
- memberikan catatan akademik;
- melihat alert mahasiswa PA;
- menindaklanjuti alert akademik.

### Tidak Boleh

- approve KRS mahasiswa di luar tanggung jawabnya;
- mengubah nilai mahasiswa;
- mengubah penjadwalan fakultas.

### Permission Utama

```text
view_pa_students
view_student_krs
approve_krs
reject_krs
view_student_monitoring
followup_student_alert
```

---

# 7. Dosen Pembimbing

## 7.1 Isi Menu Tambahan

```text
Mahasiswa Bimbingan
Dokumen TA
├── Review Bab
├── Komentar
├── Approve Bab
└── Log Revisi

Bimbingan
├── Jadwal Konsultasi
├── Riwayat Bimbingan
└── Progress TA
```

## 7.2 Hak Akses

### Boleh

- melihat mahasiswa bimbingannya;
- melihat dokumen TA mahasiswa bimbingan;
- memberikan komentar;
- approve/reject bab;
- melihat log revisi;
- melihat progres TA;
- mengatur jadwal konsultasi.

### Tidak Boleh

- membuka dokumen mahasiswa yang bukan bimbingannya;
- mengubah dokumen mahasiswa;
- menetapkan dirinya sendiri sebagai penguji.

### Permission Utama

```text
view_supervised_students
view_supervised_ta
review_ta
comment_ta
approve_ta_chapter
reject_ta_chapter
view_ta_revision_log
manage_consultation_schedule
```

---

# 8. Dosen Penguji

## 8.1 Isi Menu Tambahan

```text
Jadwal Menguji
Detail Sidang
Penilaian Sidang
Catatan/Revisi
Riwayat Menguji
```

## 8.2 Hak Akses

### Boleh

- melihat sidang yang ditugaskan kepadanya;
- melihat dokumen peserta sidang terkait;
- input nilai;
- memberikan catatan;
- memberikan revisi;
- melihat jadwal menguji.

### Tidak Boleh

- melihat sidang yang tidak ditugaskan;
- mengubah jadwal sendiri;
- mengubah data pembimbing;
- mengubah nilai penguji lain.

### Permission Utama

```text
view_assigned_sidang
view_sidang_document
input_sidang_score
input_sidang_note
input_sidang_revision
view_examiner_history
```

---

# 9. Admin Prodi

## 9.1 Panel

```text
{tenant}.sifakueu.test/admin
```

## 9.2 Isi Menu

```text
Dashboard Prodi
Mahasiswa
Dosen
Mata Kuliah
Kurikulum
KRS
Penjadwalan
Sidang
├── Pendaftaran
├── Verifikasi
├── Plotting Penguji
└── Jadwal

Profiling Dosen
Profiling Mahasiswa
Monitoring
Dokumen TA
Laporan Prodi
```

## 9.3 Hak Akses

### Boleh

- mengelola data mahasiswa prodi;
- mengelola data dosen prodi;
- mengelola mata kuliah;
- mengelola kurikulum;
- memverifikasi pendaftaran sidang;
- plotting penguji;
- mengelola jadwal sidang;
- mengelola jadwal kuliah;
- melihat monitoring mahasiswa prodi;
- melihat profiling dosen/mahasiswa;
- melihat status dokumen TA.

### Tidak Boleh

- mengakses data prodi lain tanpa izin;
- melakukan approval tingkat Dekan;
- mengelola tenant;
- mengubah konfigurasi platform.

### Permission Utama

```text
manage_prodi_students
manage_prodi_dosen
manage_mata_kuliah
manage_kurikulum
verify_sidang
assign_examiner
manage_sidang_schedule
manage_class_schedule
view_prodi_monitoring
view_dosen_profile
view_student_profile
view_ta_status
view_prodi_reports
```

---

# 10. Admin Fakultas / TU

## 10.1 Isi Menu

```text
Dashboard
Surat Menyurat
├── Pengajuan
├── Verifikasi
├── Nomor Surat
├── Template
└── Arsip

Sidang
Monitoring
Repositori TA
Master Data Fakultas
Laporan Fakultas
```

## 10.2 Hak Akses

### Boleh

- mengelola administrasi surat;
- verifikasi surat;
- generate nomor surat;
- mengelola template;
- mengelola arsip;
- melihat operasional sidang;
- melihat monitoring fakultas;
- mengelola repositori TA sesuai permission.

### Tidak Boleh

- approve KRS;
- mengubah nilai akademik;
- mengubah rekomendasi dosen;
- mengakses tenant lain.

### Permission Utama

```text
manage_surat
verify_surat
generate_nomor_surat
manage_surat_template
manage_surat_archive
view_sidang
view_faculty_monitoring
manage_ta_repository
view_faculty_reports
```

---

# 11. Kaprodi

## 11.1 Panel

```text
{tenant}.sifakueu.test/pimpinan
```

## 11.2 Isi Menu

```text
Dashboard Prodi
Monitoring Mahasiswa
Sidang
Profil Dosen
Profil Mahasiswa
CPL/PLO
Alert
Approval
Dokumen TA
Laporan Prodi
```

## 11.3 Hak Akses

### Boleh

- melihat dashboard prodi;
- melihat monitoring mahasiswa prodi;
- melihat dan menyetujui proses yang membutuhkan Kaprodi;
- validasi plotting dosen;
- validasi bidang/rumpun sesuai kewenangan;
- melihat CPL/PLO;
- melihat profiling mahasiswa/dosen;
- melihat laporan prodi;
- melihat status dokumen TA.

### Tidak Boleh

- mengubah data tenant lain;
- mengelola Super Admin;
- menghapus tenant;
- melihat data fakultas lain tanpa izin.

### Permission Utama

```text
view_prodi_dashboard
view_prodi_monitoring
approve_prodi_process
validate_dosen_plotting
validate_rumpun
view_cpl
view_plo
view_prodi_profiles
view_prodi_reports
```

---

# 12. Dekan / Wakil Dekan

## 12.1 Isi Menu

```text
Dashboard Fakultas
Monitoring Fakultas
Approval Surat
Profil Dosen
Profil Mahasiswa
CPL/PLO
Laporan Fakultas
Audit Akademik Ringkas
```

## 12.2 Hak Akses

### Boleh

- melihat dashboard tingkat fakultas;
- melihat monitoring seluruh prodi pada tenant;
- approval surat strategis;
- melihat data agregat dosen;
- melihat data agregat mahasiswa;
- melihat capaian CPL/PLO;
- melihat laporan fakultas.

### Tidak Boleh

- mengubah data operasional tanpa permission;
- mengakses tenant fakultas lain;
- membuat tenant baru.

### Permission Utama

```text
view_faculty_dashboard
view_faculty_monitoring
approve_strategic_surat
view_faculty_dosen
view_faculty_students
view_faculty_cpl_plo
view_faculty_reports
```

---

# 13. KBK

## 13.1 Isi Menu

```text
Dashboard KBK
Rumpun Ilmu
Keahlian Dosen
Profil Dosen
Matriks Kesesuaian
Rekomendasi Pengampu
Gap Kompetensi
```

## 13.2 Hak Akses

### Boleh

- melihat dosen sesuai scope KBK;
- validasi rumpun ilmu;
- validasi keahlian;
- melihat matriks kesesuaian;
- melihat rekomendasi dosen;
- memberikan persetujuan/justifikasi bidang sesuai aturan.

### Tidak Boleh

- mengubah nilai mahasiswa;
- approve KRS;
- mengelola tenant;
- mengubah data dosen di luar scope tanpa permission.

### Permission Utama

```text
view_kbk_dashboard
manage_rumpun
validate_dosen_expertise
view_dosen_matching
view_recommendation
approve_outside_rumpun
view_competency_gap
```

---

# 14. LPM / Gugus Mutu

## 14.1 Isi Menu

```text
Dashboard Mutu
CPL
PLO
Monitoring Capaian
Gap CPL
Profil Lulusan
Repositori TA
Laporan Akreditasi
Audit Data
```

## 14.2 Hak Akses

### Boleh

- melihat data CPL/PLO;
- melihat tren capaian;
- melihat gap;
- melihat data agregat mahasiswa;
- mengakses repositori TA sesuai kebutuhan akreditasi;
- menghasilkan laporan mutu/akreditasi.

### Tidak Boleh

- mengubah nilai;
- approve KRS;
- menetapkan penguji;
- mengubah dokumen TA;
- mengakses data tenant lain.

### Permission Utama

```text
view_quality_dashboard
view_cpl
view_plo
view_cpl_gap
view_graduate_profile
view_accreditation_repository
generate_quality_report
view_quality_audit
```

---

# 15. BAAK

## 15.1 Isi Menu

```text
Dashboard Akademik
Rekap Mahasiswa
Rekap Sidang
Monitoring
Yudisium
Dokumen TA
Laporan Akademik
```

## 15.2 Hak Akses

### Boleh

- melihat rekap akademik;
- melihat data sidang;
- melihat status kelulusan;
- melihat dokumen final yang dibutuhkan;
- melihat data yudisium;
- membuat laporan akademik sesuai scope.

### Tidak Boleh

- mengubah data tenant lain;
- mengelola dosen;
- mengubah rekomendasi;
- mengubah tenant.

### Permission Utama

```text
view_academic_recap
view_sidang_recap
view_graduation_status
view_final_ta_document
manage_yudisium
generate_academic_report
```

---

# 16. Kepala Laboratorium

## 16.1 Isi Menu

```text
Dashboard Laboratorium
Profil Dosen
Profil Mahasiswa
Kebutuhan Asisten
Topik Riset
Rekomendasi Mahasiswa
Laporan Laboratorium
```

## 16.2 Hak Akses

### Boleh

- melihat data dosen yang relevan;
- melihat profil mahasiswa yang relevan;
- melihat rekomendasi kompetensi;
- mengelola kebutuhan asisten;
- mengelola topik riset/laboratorium;
- melihat laporan laboratorium.

### Tidak Boleh

- melihat seluruh data mahasiswa tanpa scope;
- approve KRS;
- mengubah nilai;
- mengelola tenant.

### Permission Utama

```text
view_lab_dashboard
view_relevant_dosen
view_relevant_students
manage_assistant_requirement
manage_research_topic
view_lab_report
```

---

# 17. Alumni / Pengguna Lulusan

Aktor ini tidak harus memperoleh panel internal penuh. Akses dapat diberikan melalui form/portal terbatas.

## 17.1 Isi Akses

```text
Form Feedback Alumni
Form Feedback Pengguna Lulusan
Survey Kompetensi
Riwayat Feedback Sendiri
```

## 17.2 Hak Akses

### Boleh

- memberikan feedback;
- mengisi survey;
- melihat submission milik sendiri jika akun digunakan.

### Tidak Boleh

- mengakses data internal mahasiswa;
- melihat nilai;
- melihat KRS;
- melihat dokumen TA internal;
- mengakses panel administrasi.

### Permission Utama

```text
submit_alumni_feedback
submit_employer_feedback
view_own_feedback
```

---

# 18. Ringkasan Matrix Hak Akses

| Aktor | Panel | Scope Data | Level Akses |
|---|---|---|---|
| Super Admin | Super Admin | Platform | Tenant & Infrastruktur |
| Admin Tenant | Admin | Tenant | Administrasi Tenant |
| Mahasiswa | Mahasiswa | Diri sendiri | Operasional Pribadi |
| Dosen | Dosen | Diri sendiri/assignment | Akademik |
| Dosen PA | Dosen | Mahasiswa PA | Approval KRS |
| Dosen Pembimbing | Dosen | Mahasiswa bimbingan | Bimbingan TA |
| Dosen Penguji | Dosen | Sidang yang ditugaskan | Penilaian Sidang |
| Admin Prodi | Admin | Prodi | Operasional Prodi |
| Admin Fakultas/TU | Admin | Fakultas | Administrasi Fakultas |
| Kaprodi | Pimpinan | Prodi | Monitoring & Approval |
| Dekan/WD | Pimpinan | Fakultas | Monitoring & Approval |
| KBK | Pimpinan | KBK | Validasi Keahlian |
| LPM/Gugus Mutu | Pimpinan | Fakultas | Mutu & Akreditasi |
| BAAK | Pimpinan | Akademik Tenant | Rekap & Yudisium |
| Kepala Lab | Pimpinan | Laboratorium | Lab & Riset |
| Alumni/Pengguna Lulusan | Portal terbatas | Diri sendiri | Feedback |

---

# 19. Aturan Akses Penting

## 19.1 Tenant Isolation

Semua aktor tenant hanya dapat mengakses data tenant tempat aktor tersebut terdaftar.

```text
FASILKOM user
→ hanya FASILKOM

FEB user
→ hanya FEB
```

## 19.2 Panel Tidak Sama dengan Permission

Berada di panel yang sama tidak berarti memiliki akses yang sama.

Contoh:

```text
Dosen Panel
├── Dosen biasa
├── Dosen PA
├── Dosen Pembimbing
└── Dosen Penguji
```

Hak akses dibedakan berdasarkan permission dan policy.

## 19.3 Policy / Data Ownership

Contoh:

```text
Dosen Pembimbing
→ hanya review TA mahasiswa bimbingannya

Dosen Penguji
→ hanya input nilai sidang yang ditugaskan

Mahasiswa
→ hanya mengelola dokumen TA miliknya

Dosen PA
→ hanya approve KRS mahasiswa PA
```

## 19.4 Prinsip Least Privilege

Setiap aktor hanya memperoleh permission minimum yang dibutuhkan untuk menjalankan tugasnya.

---

# 20. Rekomendasi Implementasi Permission

Gunakan pola permission:

```text
<action>_<resource>
```

Contoh:

```text
view_mahasiswa
create_mahasiswa
update_mahasiswa
delete_mahasiswa

view_krs
create_krs
approve_krs

view_sidang
manage_sidang
input_sidang_score

view_ta
review_ta
approve_ta_chapter

view_monitoring
view_cpl
view_plo
```

Permission kemudian dikumpulkan ke dalam role.

---

# 21. Alur Authorization

```text
Request
↓
Resolve Tenant
↓
Tenant ACTIVE?
↓
Authenticate User
↓
User Member Tenant?
↓
Can Access Panel?
↓
Has Role?
↓
Has Permission?
↓
Pass Policy / Data Scope?
↓
Allow Action
```

Jika salah satu gagal:

```text
403 Forbidden
```

atau:

```text
Tenant Not Found / Tenant Suspended
```

sesuai kondisi.

---

# 22. Kesimpulan

SIFAK menggunakan lima panel utama:

```text
1 Super Admin Panel
4 Tenant Panel:
- Mahasiswa
- Dosen
- Admin
- Pimpinan
```

Jumlah panel tidak bertambah ketika tenant bertambah.

Setiap aktor menggunakan panel yang sesuai, sedangkan hak akses dikontrol oleh:

```text
Tenant
+
Role
+
Permission
+
Policy
+
Data Scope
```

Dengan desain ini, SIFAK tetap terstruktur ketika memiliki banyak tenant, banyak role, dan banyak pengguna tanpa harus membuat panel, resource, atau business logic baru untuk setiap fakultas.

---

**SIFAK — Actor & Access Control Specification v1.0**
