# MASTER DATA SPECIFICATION
## Sistem Informasi Fakultas Terintegrasi (SIFAK)

**Versi:** 1.0  
**Basis Dokumen:** SIFAK BRD/BPMN/SLR v2.1 Multi-Tenant  
**Tahap Implementasi:** Fase 2 — Master Data  
**Arsitektur:** Multi-Tenant, Database per Tenant  
**Tech Stack:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB  
**Tujuan:** Menetapkan struktur, relasi, aturan, hak akses, dan kebutuhan implementasi Master Data yang menjadi fondasi seluruh Modul M1–M7.

---

# 1. Tujuan Master Data

Master Data SIFAK berfungsi sebagai sumber data dasar yang digunakan bersama oleh seluruh modul.

Master Data utama terdiri dari:

```text
Fakultas
Program Studi
Mahasiswa
Dosen
Mata Kuliah
Kurikulum
Semester
Ruangan
Tahun Akademik
KBK
Rumpun Ilmu
```

Master Data harus selesai dan stabil sebelum implementasi penuh Modul M1–M7 agar:

- tidak terjadi duplikasi struktur data;
- setiap modul menggunakan referensi data yang sama;
- relasi mahasiswa, dosen, prodi, mata kuliah, dan kurikulum konsisten;
- proses KRS, penjadwalan, sidang, monitoring, profiling, dan dokumen TA dapat menggunakan data dasar yang valid;
- data setiap tenant tetap terisolasi;
- perubahan data dapat diaudit.

---

# 2. Posisi Master Data dalam Arsitektur SIFAK

```text
CENTRAL PLATFORM
│
├── Tenant Management
├── Domain / Subdomain
├── Provisioning
└── Platform Audit
        │
        ▼
TENANT
│
├── Master Data
│   ├── Fakultas
│   ├── Program Studi
│   ├── Mahasiswa
│   ├── Dosen
│   ├── Mata Kuliah
│   ├── Kurikulum
│   ├── Semester
│   ├── Ruangan
│   ├── Tahun Akademik
│   ├── KBK
│   └── Rumpun Ilmu
│
├── M1 Sidang Sempro & TA
├── M2 Surat Menyurat
├── M3 Monitoring & Alert
├── M4 KRS & Penjadwalan
├── M5 Profiling Dosen & Rekomendasi Pengajaran
├── M6 Profiling Mahasiswa & Rekomendasi Profil Lulusan
└── M7 Manajemen Dokumen TA & Repositori Digital
```

---

# 3. Prinsip Master Data

Master Data SIFAK mengikuti prinsip berikut:

1. **Tenant Isolation** — data master suatu fakultas hanya berada pada database tenant fakultas tersebut.
2. **Single Source of Truth** — Modul M1–M7 tidak membuat duplikasi data mahasiswa, dosen, mata kuliah, dan data dasar lainnya.
3. **Referential Integrity** — relasi antar-data menggunakan foreign key atau relasi yang tervalidasi.
4. **Auditability** — perubahan data penting harus dapat dilacak.
5. **Soft Delete untuk Data Kritis** — data yang sudah digunakan transaksi tidak langsung dihapus permanen.
6. **Status Aktif/Nonaktif** — data master dapat dinonaktifkan tanpa menghapus histori.
7. **Role-Based Access** — hak kelola Master Data dibatasi sesuai role dan scope.
8. **Tenant-Aware** — seluruh query, import, export, queue, dan audit harus berjalan dalam tenant aktif.

---

# 4. Scope Master Data

| Kode | Master Data | Fungsi Utama | Dipakai Oleh |
|---|---|---|---|
| MD01 | Fakultas | Identitas tenant/fakultas | Seluruh modul |
| MD02 | Program Studi | Struktur akademik per tenant | M1, M3, M4, M6, M7 |
| MD03 | Mahasiswa | Identitas dan data akademik mahasiswa | M1, M2, M3, M4, M6, M7 |
| MD04 | Dosen | Identitas dan data dasar dosen | M1, M4, M5, M7 |
| MD05 | Mata Kuliah | Data mata kuliah | M4, M5, M6 |
| MD06 | Kurikulum | Struktur kurikulum per prodi | M4, M6 |
| MD07 | Semester | Periode semester akademik | M1, M3, M4, M5, M6 |
| MD08 | Ruangan | Data ruang perkuliahan/sidang | M1, M4 |
| MD09 | Tahun Akademik | Periode tahun akademik | M1–M7 |
| MD10 | KBK | Kelompok Bidang Keahlian | M5 |
| MD11 | Rumpun Ilmu | Klasifikasi bidang/rumpun dosen | M1, M5, M6 |

---

# 5. Pembagian Database

## 5.1 Central Database

Central Database hanya menyimpan data platform:

```text
sifak_central
├── tenants
├── tenant_domains
├── super_admins
├── tenant_provisioning_logs
└── platform_audit_logs
```

Data akademik tidak disimpan pada Central Database.

## 5.2 Tenant Database

Setiap tenant memiliki database tersendiri.

```text
sifak_tenant_fasilkom
sifak_tenant_feb
sifak_tenant_fikes
```

Master Data berada di dalam database tenant:

```text
sifak_tenant_fasilkom
├── fakultas
├── program_studi
├── mahasiswa
├── dosen
├── mata_kuliah
├── kurikulum
├── semester
├── ruangan
├── tahun_akademik
├── kbk
└── rumpun_ilmu
```

---

# 6. Master Data Fakultas

## 6.1 Tujuan

Menyimpan identitas fakultas yang digunakan pada seluruh modul dalam tenant. Dalam arsitektur multi-tenant, satu tenant mewakili satu fakultas.

## 6.2 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| kode_fakultas | varchar(20) | Ya | Kode fakultas |
| nama_fakultas | varchar(150) | Ya | Nama lengkap fakultas |
| nama_singkat | varchar(50) | Ya | Nama singkat |
| alamat | text | Tidak | Alamat fakultas |
| email | varchar(150) | Tidak | Email resmi |
| telepon | varchar(30) | Tidak | Nomor telepon |
| logo_path | varchar(255) | Tidak | Logo tenant/fakultas |
| status | enum | Ya | active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 6.3 Business Rule

```text
MD-BR-001 Satu tenant hanya memiliki satu data fakultas utama.
MD-BR-002 Kode fakultas wajib unik di dalam tenant.
MD-BR-003 Fakultas tidak boleh dihapus permanen apabila sudah memiliki data akademik.
```

---

# 7. Master Data Program Studi

## 7.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| fakultas_id | FK | Ya | Relasi fakultas |
| kode_prodi | varchar(20) | Ya | Kode program studi |
| nama_prodi | varchar(150) | Ya | Nama program studi |
| jenjang | enum | Ya | D3 / D4 / S1 / S2 / S3 |
| kaprodi_dosen_id | FK nullable | Tidak | Ketua program studi |
| status | enum | Ya | active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 7.2 Business Rule

```text
MD-BR-004 Kode prodi wajib unik dalam tenant.
MD-BR-005 Program studi harus terhubung ke fakultas tenant aktif.
MD-BR-006 Kaprodi harus berasal dari data dosen aktif.
MD-BR-007 Prodi yang sudah digunakan mahasiswa tidak boleh dihapus permanen.
```

---

# 8. Master Data Mahasiswa

## 8.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| user_id | FK nullable | Tidak | Relasi akun login |
| prodi_id | FK | Ya | Program studi |
| nim | varchar(30) | Ya | Nomor Induk Mahasiswa |
| nama | varchar(150) | Ya | Nama mahasiswa |
| angkatan | year/int | Ya | Tahun masuk |
| semester_aktif | int | Ya | Semester berjalan |
| ipk | decimal(3,2) | Tidak | IPK terkini |
| sks_lulus | int | Tidak | SKS lulus |
| email | varchar(150) | Tidak | Email mahasiswa |
| no_hp | varchar(30) | Tidak | Nomor telepon |
| status_mahasiswa | enum | Ya | active / cuti / lulus / nonaktif |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 8.2 Data Tambahan M6

```text
minat_mahasiswa
organisasi_mahasiswa
sertifikasi_mahasiswa
portofolio_mahasiswa
mbkm_magang
```

## 8.3 Business Rule

```text
MD-BR-008 NIM wajib unik dalam tenant.
MD-BR-009 Mahasiswa wajib terhubung ke satu program studi.
MD-BR-010 IPK berada pada rentang valid sesuai aturan akademik.
MD-BR-011 SKS lulus tidak boleh bernilai negatif.
MD-BR-012 Mahasiswa yang sudah memiliki transaksi akademik tidak boleh dihapus permanen.
```

---

# 9. Master Data Dosen

## 9.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| user_id | FK nullable | Tidak | Relasi akun login |
| nidn | varchar(30) | Ya | NIDN/NIDK |
| nama | varchar(150) | Ya | Nama dosen |
| email | varchar(150) | Tidak | Email |
| prodi_id | FK nullable | Tidak | Homebase prodi |
| jabatan_akademik | varchar(100) | Tidak | Jabatan akademik |
| pendidikan_terakhir | enum | Tidak | S2 / S3 |
| rumpun_ilmu_id | FK nullable | Tidak | Rumpun ilmu utama |
| kbk_id | FK nullable | Tidak | KBK |
| status_dosen | enum | Ya | active / inactive / leave |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 9.2 Data Profiling M5

```text
dosen_keahlian
dosen_pendidikan
dosen_publikasi
dosen_sertifikasi
dosen_pengalaman_industri
dosen_preferensi_mk
beban_dosen
jadwal_konsultasi
dosen_lokasi
riwayat_mengajar
```

## 9.3 Business Rule

```text
MD-BR-013 NIDN/NIDK wajib unik dalam tenant.
MD-BR-014 Dosen aktif dapat ditetapkan sebagai pengampu, pembimbing, atau penguji.
MD-BR-015 Rumpun ilmu dan KBK harus berasal dari master data tenant yang sama.
MD-BR-016 Data dosen yang telah digunakan dalam histori akademik tidak boleh dihapus permanen.
```

---

# 10. Master Data Mata Kuliah

## 10.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| prodi_id | FK | Ya | Program studi pemilik |
| kode_mk | varchar(30) | Ya | Kode mata kuliah |
| nama_mk | varchar(150) | Ya | Nama mata kuliah |
| sks | int | Ya | Jumlah SKS |
| semester_rekomendasi | int | Tidak | Semester ideal |
| tipe_mk | enum | Ya | wajib / pilihan |
| status | enum | Ya | active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 10.2 Relasi Prasyarat

```text
mata_kuliah_prasyarat
├── mata_kuliah_id
├── prasyarat_mata_kuliah_id
└── minimal_nilai
```

## 10.3 Business Rule

```text
MD-BR-017 Kode mata kuliah wajib unik sesuai scope prodi/kurikulum.
MD-BR-018 SKS harus lebih besar dari 0.
MD-BR-019 Mata kuliah prasyarat tidak boleh mereferensikan dirinya sendiri.
MD-BR-020 Mata kuliah nonaktif tidak boleh ditawarkan pada periode baru.
```

---

# 11. Master Data Kurikulum

## 11.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| prodi_id | FK | Ya | Program studi |
| kode_kurikulum | varchar(50) | Ya | Kode kurikulum |
| nama_kurikulum | varchar(150) | Ya | Nama kurikulum |
| tahun_mulai | int | Ya | Tahun mulai berlaku |
| tahun_selesai | int nullable | Tidak | Tahun akhir |
| total_sks | int | Ya | Total SKS kurikulum |
| status | enum | Ya | draft / active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 11.2 Relasi

```text
kurikulum_mata_kuliah
├── kurikulum_id
├── mata_kuliah_id
├── semester
└── wajib
```

## 11.3 Kaitan M6

Kurikulum menjadi dasar:

```text
CPL
PLO
Pemetaan MK → CPL
Pemetaan CPL → PLO
```

## 11.4 Business Rule

```text
MD-BR-021 Satu prodi dapat memiliki beberapa versi kurikulum.
MD-BR-022 Hanya kurikulum aktif yang digunakan sesuai periode berlaku.
MD-BR-023 Kurikulum yang sudah digunakan mahasiswa tidak boleh dihapus permanen.
```

---

# 12. Master Data Semester

## 12.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| tahun_akademik_id | FK | Ya | Tahun akademik |
| nama_semester | enum | Ya | Ganjil / Genap / Pendek |
| kode_semester | varchar(20) | Ya | Kode periode |
| tanggal_mulai | date | Ya | Awal semester |
| tanggal_selesai | date | Ya | Akhir semester |
| status | enum | Ya | planned / active / closed |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 12.2 Business Rule

```text
MD-BR-024 Hanya satu semester utama dapat berstatus aktif pada periode tertentu sesuai kebijakan tenant.
MD-BR-025 Tanggal selesai harus lebih besar dari tanggal mulai.
MD-BR-026 Semester closed tidak dapat digunakan untuk transaksi baru kecuali role berwenang.
```

---

# 13. Master Data Ruangan

## 13.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| kode_ruang | varchar(30) | Ya | Kode ruang |
| nama_ruang | varchar(100) | Ya | Nama ruang |
| gedung | varchar(100) | Tidak | Gedung |
| lantai | varchar(20) | Tidak | Lantai |
| kapasitas | int | Ya | Kapasitas |
| tipe_ruang | enum | Ya | kelas / lab / sidang / lainnya |
| status | enum | Ya | active / maintenance / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 13.2 Business Rule

```text
MD-BR-027 Kode ruangan wajib unik dalam tenant.
MD-BR-028 Kapasitas harus lebih besar dari 0.
MD-BR-029 Ruangan maintenance/inactive tidak boleh digunakan pada jadwal baru.
```

---

# 14. Master Data Tahun Akademik

## 14.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| kode_tahun | varchar(20) | Ya | Contoh 2026/2027 |
| tahun_mulai | int | Ya | Tahun awal |
| tahun_selesai | int | Ya | Tahun akhir |
| status | enum | Ya | planned / active / closed |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 14.2 Business Rule

```text
MD-BR-030 Kode tahun akademik wajib unik.
MD-BR-031 Satu tenant hanya memiliki satu tahun akademik aktif pada satu waktu kecuali kebijakan sistem menentukan lain.
MD-BR-032 Tahun akademik closed tetap dipertahankan untuk histori.
```

---

# 15. Master Data KBK

## 15.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| kode_kbk | varchar(30) | Ya | Kode KBK |
| nama_kbk | varchar(150) | Ya | Nama KBK |
| deskripsi | text | Tidak | Deskripsi |
| ketua_dosen_id | FK nullable | Tidak | Ketua KBK |
| status | enum | Ya | active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 15.2 Business Rule

```text
MD-BR-033 Kode KBK wajib unik dalam tenant.
MD-BR-034 Ketua KBK harus berasal dari dosen aktif.
MD-BR-035 KBK yang sudah direferensikan profil dosen tidak boleh dihapus permanen.
```

---

# 16. Master Data Rumpun Ilmu

## 16.1 Struktur Data

| Field | Tipe | Wajib | Keterangan |
|---|---|---:|---|
| id | bigint/uuid | Ya | Primary key |
| kode_rumpun | varchar(30) | Ya | Kode rumpun |
| nama_rumpun | varchar(150) | Ya | Nama rumpun |
| deskripsi | text | Tidak | Deskripsi |
| status | enum | Ya | active / inactive |
| created_at | timestamp | Ya | Tanggal dibuat |
| updated_at | timestamp | Ya | Tanggal diperbarui |

## 16.2 Business Rule

```text
MD-BR-036 Kode rumpun ilmu wajib unik.
MD-BR-037 Rumpun ilmu nonaktif tidak dapat ditetapkan pada dosen baru.
MD-BR-038 Riwayat rumpun ilmu lama tetap dipertahankan untuk audit dan histori.
```

---

# 17. Relasi Utama Master Data

```text
Fakultas
│
└── Program Studi
    │
    ├── Mahasiswa
    ├── Dosen
    ├── Mata Kuliah
    │   └── Prasyarat Mata Kuliah
    │
    └── Kurikulum
        └── Kurikulum Mata Kuliah

Tahun Akademik
└── Semester

KBK
└── Dosen

Rumpun Ilmu
└── Dosen

Ruangan
├── Jadwal Kuliah
└── Jadwal Sidang
```

---

# 18. Dependency Master Data ke Modul

| Modul | Master Data yang Digunakan |
|---|---|
| M1 Sidang Sempro & TA | Mahasiswa, Dosen, Prodi, Semester, Tahun Akademik, Ruangan, Rumpun Ilmu |
| M2 Surat Menyurat | Fakultas, Prodi, Mahasiswa, Dosen, Tahun Akademik |
| M3 Monitoring & Alert | Mahasiswa, Prodi, Semester, Tahun Akademik |
| M4 KRS & Penjadwalan | Mahasiswa, Dosen, Mata Kuliah, Kurikulum, Semester, Tahun Akademik, Ruangan |
| M5 Profiling Dosen | Dosen, Mata Kuliah, KBK, Rumpun Ilmu, Semester, Tahun Akademik |
| M6 Profiling Mahasiswa | Mahasiswa, Mata Kuliah, Kurikulum, Prodi, Semester |
| M7 Dokumen TA | Mahasiswa, Dosen, Prodi, Tahun Akademik |

---

# 19. Hak Akses Master Data

| Aktor | Fakultas | Prodi | Mahasiswa | Dosen | MK | Kurikulum | Semester | Ruangan | Tahun Akademik | KBK | Rumpun |
|---|---|---|---|---|---|---|---|---|---|---|---|
| Super Admin | View platform only | - | - | - | - | - | - | - | - | - | - |
| Admin Tenant | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD | CRUD |
| Admin Prodi | View | Scope Prodi | CRUD Scope Prodi | CRUD Scope Prodi | CRUD | CRUD | View | View | View | View | View |
| Admin Fakultas/TU | CRUD terbatas | View | View | View | View | View | View | CRUD sesuai scope | View | View | View |
| Kaprodi | View | View own | View own prodi | View own prodi | View | View | View | View | View | View | View |
| KBK | View | View | - | View | View | - | - | - | - | CRUD/Validasi | CRUD/Validasi |
| Dosen | - | View | Assignment only | Own profile | View | View | View | View | View | View | View |
| Mahasiswa | - | Own | Own | Related only | View | View | View | View | View | - | - |

> CRUD tetap tunduk pada permission dan policy/data scope.

---

# 20. Permission Master Data

```text
view_fakultas
update_fakultas

view_prodi
create_prodi
update_prodi
deactivate_prodi

view_mahasiswa
create_mahasiswa
update_mahasiswa
deactivate_mahasiswa
import_mahasiswa
export_mahasiswa

view_dosen
create_dosen
update_dosen
deactivate_dosen
import_dosen
export_dosen

view_mata_kuliah
create_mata_kuliah
update_mata_kuliah
deactivate_mata_kuliah

view_kurikulum
create_kurikulum
update_kurikulum
activate_kurikulum

view_semester
create_semester
update_semester
activate_semester
close_semester

view_ruangan
create_ruangan
update_ruangan
deactivate_ruangan

view_tahun_akademik
create_tahun_akademik
activate_tahun_akademik
close_tahun_akademik

view_kbk
create_kbk
update_kbk
validate_kbk

view_rumpun_ilmu
create_rumpun_ilmu
update_rumpun_ilmu
validate_rumpun_ilmu
```

---

# 21. Struktur Menu Admin

```text
Master Data
├── Fakultas
├── Program Studi
├── Mahasiswa
├── Dosen
├── Mata Kuliah
├── Kurikulum
├── Semester
├── Tahun Akademik
├── Ruangan
├── KBK
└── Rumpun Ilmu
```

---

# 22. Filament Resource

```text
FacultyResource
ProgramStudiResource
MahasiswaResource
DosenResource
MataKuliahResource
KurikulumResource
SemesterResource
TahunAkademikResource
RuanganResource
KbkResource
RumpunIlmuResource
```

Resource tidak dibuat ulang per tenant. Tenant context menentukan database aktif.

---

# 23. Import dan Export

Data besar seperti mahasiswa dan dosen harus mendukung import CSV/XLSX.

## 23.1 Import Mahasiswa

```text
nim
nama
kode_prodi
angkatan
email
status
```

## 23.2 Import Dosen

```text
nidn
nama
kode_prodi
email
jabatan
kode_kbk
kode_rumpun
status
```

## 23.3 Import Validation

Sistem harus:

- menolak NIM duplikat;
- menolak NIDN duplikat;
- menolak kode prodi tidak dikenal;
- menolak KBK tidak dikenal;
- menolak rumpun ilmu tidak dikenal;
- menghasilkan laporan baris gagal.

---

# 24. Audit Log Master Data

Perubahan Master Data minimal mencatat:

```text
tenant_id
user_id
role
action
table/resource
record_id
old_value
new_value
timestamp
ip_address
```

---

# 25. Validasi Umum

Semua form Master Data harus memiliki:

- required validation;
- unique validation;
- foreign key validation;
- status validation;
- format email;
- numeric validation;
- tenant scope validation;
- authorization validation.

---

# 26. Soft Delete dan Deactivation

Untuk data akademik penting, gunakan `inactive` atau soft delete daripada hard delete apabila data sudah pernah digunakan transaksi.

Data yang harus dipertahankan historinya:

```text
Program Studi
Mahasiswa
Dosen
Mata Kuliah
Kurikulum
Semester
Tahun Akademik
Ruangan
KBK
Rumpun Ilmu
```

---

# 27. Seed Data Awal Tenant

Saat tenant baru dibuat, provisioning dapat menyiapkan:

```text
Fakultas
Default Roles
Default Permissions
Default System Settings
```

Setelah tenant aktif, Admin Tenant mengisi:

```text
Program Studi
KBK
Rumpun Ilmu
Tahun Akademik
Semester
Ruangan
Dosen
Kurikulum
Mata Kuliah
Mahasiswa
```

---

# 28. Urutan Implementasi Master Data

```text
1. Fakultas
↓
2. Program Studi
↓
3. Tahun Akademik
↓
4. Semester
↓
5. KBK
↓
6. Rumpun Ilmu
↓
7. Dosen
↓
8. Kurikulum
↓
9. Mata Kuliah
↓
10. Ruangan
↓
11. Mahasiswa
```

Alasan:

- Prodi bergantung pada Fakultas.
- Semester bergantung pada Tahun Akademik.
- Dosen membutuhkan Prodi, KBK, dan Rumpun Ilmu.
- Mata Kuliah membutuhkan Prodi/Kurikulum.
- Mahasiswa membutuhkan Prodi dan konteks akademik.

---

# 29. Acceptance Criteria Master Data

Master Data dinyatakan siap ketika:

- [ ] tenant aktif dapat memiliki data fakultas;
- [ ] program studi dapat dibuat;
- [ ] mahasiswa dapat dibuat dan terhubung ke prodi;
- [ ] dosen dapat dibuat dan terhubung ke prodi;
- [ ] dosen dapat dikaitkan ke KBK;
- [ ] dosen dapat dikaitkan ke rumpun ilmu;
- [ ] mata kuliah dapat dibuat;
- [ ] prasyarat mata kuliah dapat dibuat;
- [ ] kurikulum dapat dibuat;
- [ ] mata kuliah dapat dikaitkan ke kurikulum;
- [ ] tahun akademik dapat dibuat;
- [ ] semester dapat dibuat;
- [ ] ruangan dapat dibuat;
- [ ] hanya user berwenang dapat CRUD;
- [ ] tenant FASILKOM tidak dapat membaca data tenant lain;
- [ ] duplicate NIM ditolak;
- [ ] duplicate NIDN ditolak;
- [ ] duplicate kode prodi ditolak;
- [ ] duplicate kode ruang ditolak;
- [ ] audit log tersimpan;
- [ ] data yang sudah direferensikan transaksi tidak dapat dihapus sembarangan;
- [ ] import mahasiswa dapat tervalidasi;
- [ ] import dosen dapat tervalidasi;
- [ ] Master Data dapat dipakai oleh M1–M7.

---

# 30. Definition of Done

Master Data dianggap selesai apabila:

1. migration selesai;
2. model dan relation selesai;
3. Filament Resource tersedia;
4. permission tersedia;
5. policy tersedia;
6. validation tersedia;
7. import/export tersedia untuk data yang membutuhkan;
8. audit log berjalan;
9. tenant isolation teruji;
10. soft delete/deactivation berjalan;
11. unit/feature test utama lulus;
12. Master Data dapat digunakan oleh modul berikutnya tanpa duplikasi struktur.

---

# 31. Output Fase Master Data

Setelah fase ini selesai, tenant FASILKOM minimal memiliki:

```text
Fakultas
↓
Program Studi
↓
Tahun Akademik + Semester
↓
KBK + Rumpun Ilmu
↓
Dosen
↓
Kurikulum + Mata Kuliah
↓
Ruangan
↓
Mahasiswa
```

Fondasi kemudian siap untuk fase berikutnya:

```text
M5 Profiling Dosen
+
M4 KRS & Penjadwalan
```

---

# 32. Kesimpulan

Master Data bukan modul bisnis baru, melainkan fondasi data bersama untuk seluruh Modul M1–M7.

```text
Tenant FASILKOM
→ Master Data FASILKOM
→ M1–M7 FASILKOM

Tenant FEB
→ Master Data FEB
→ M1–M7 FEB
```

Struktur Master Data sama untuk semua tenant, tetapi data tetap terisolasi pada database masing-masing tenant.

---

**SIFAK — Master Data Specification v1.0**
