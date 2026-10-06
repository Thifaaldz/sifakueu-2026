# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SISTEM INFORMASI FAKULTAS TERINTEGRASI (SIFAK)

**Versi:** 2.1  
**Status:** Draft Product Requirements  
**Basis Dokumen:** BRD/BPMN/SLR SIFAK v2.1 Multi-Tenant  
**Produk:** Sistem Informasi Fakultas Terintegrasi (SIFAK)  
**Implementasi Awal:** FASILKOM sebagai tenant utama/pilot  
**Model Sistem:** Multi-Tenant Modular Monolith  
**Tech Stack Utama:** Laravel + Filament PHP Multi-Panel + Laravel Livewire + MariaDB

---

# 1. Ringkasan Produk

SIFAK adalah sistem informasi fakultas terintegrasi yang menggabungkan proses akademik dan administrasi fakultas dalam satu aplikasi modular. Produk terdiri dari tujuh modul utama yang saling terhubung dan menggunakan master data bersama.

SIFAK dikembangkan dengan konsep **multi-tenant**, sehingga satu aplikasi dapat digunakan oleh beberapa fakultas tanpa membuat project aplikasi baru untuk setiap fakultas. FASILKOM menjadi tenant awal/pilot. Fakultas lain dapat ditambahkan oleh Super Admin melalui proses pembuatan tenant baru dan subdomain unik.

Setiap tenant memperoleh keseluruhan sistem SIFAK, meliputi:

- M1 — Sidang Sempro & TA
- M2 — Surat Menyurat
- M3 — Monitoring & Alert
- M4 — KRS & Penjadwalan
- M5 — Profiling Dosen & Rekomendasi Pengajaran
- M6 — Profiling Mahasiswa & Rekomendasi Profil Lulusan
- M7 — Manajemen Dokumen TA & Repositori Digital

Penambahan multi-tenant tidak mengubah definisi, fungsi, maupun ruang lingkup ketujuh modul tersebut.

---

# 2. Tujuan Produk

## 2.1 Tujuan Utama

SIFAK bertujuan untuk:

1. menyediakan satu aplikasi terintegrasi untuk proses akademik dan administrasi fakultas;
2. mengurangi duplikasi data;
3. mempercepat proses administrasi sidang dan surat;
4. meningkatkan monitoring mahasiswa;
5. mendukung kelulusan tepat waktu melalui alert dan monitoring;
6. mengotomatisasi proses plotting dosen dan penjadwalan;
7. menyediakan rekomendasi berbasis data untuk dosen, mahasiswa, profil lulusan, dan tugas akhir;
8. mendukung proses pengelolaan dokumen tugas akhir secara terstruktur;
9. mempermudah mahasiswa dalam melihat ketersediaan dan lokasi dosen;
10. menyediakan satu sumber data akademik yang terpusat pada konteks fakultas;
11. memungkinkan SIFAK digunakan oleh beberapa fakultas melalui arsitektur multi-tenant;
12. memungkinkan Super Admin menambahkan fakultas baru tanpa membuat aplikasi baru;
13. menjaga isolasi data antar-fakultas;
14. memberikan subdomain unik untuk setiap tenant/fakultas.

## 2.2 Sasaran Implementasi Awal

Implementasi awal difokuskan pada:

- FASILKOM sebagai tenant utama/pilot;
- seluruh modul M1–M7 berjalan pada FASILKOM;
- multi-tenant sudah tersedia pada fondasi aplikasi;
- fakultas lain ditambahkan setelah sistem utama FASILKOM stabil.

---

# 3. Ruang Lingkup Produk

## 3.1 In Scope

Produk mencakup:

| Kode | Modul | Ruang Lingkup |
|---|---|---|
| M1 | Sidang Sempro & TA | Pendaftaran, validasi syarat, verifikasi, plotting penguji, penjadwalan, hasil sidang, revisi, berita acara, notifikasi |
| M2 | Surat Menyurat | Pengajuan surat, upload lampiran, approval, nomor otomatis, template, arsip, audit trail |
| M3 | Monitoring & Alert | Monitoring mahasiswa, rule engine, alert, notifikasi, eskalasi, dashboard |
| M4 | KRS & Penjadwalan | KRS, approval PA, perhitungan peminat, plotting dosen, jadwal kuliah, alokasi ruang |
| M5 | Profiling Dosen & Rekomendasi Pengajaran | Profil dosen, rumpun ilmu, keahlian, beban, rekomendasi MK, jadwal konsultasi, lokasi |
| M6 | Profiling Mahasiswa & Rekomendasi Profil Lulusan | Profil mahasiswa, CPL, PLO, rekomendasi profil lulusan, topik TA, pembimbing, karier |
| M7 | Manajemen Dokumen TA & Repositori Digital | Judul, daftar isi, daftar pustaka, bab 1–5, versioning, review, approval, kompilasi, repositori |
| MT | Multi-Tenant | Tenant management, subdomain, tenant isolation, tenant provisioning, lifecycle tenant |

## 3.2 Out of Scope Fase Awal

- keuangan/pembayaran;
- perpustakaan;
- akuntansi.

---

# 4. Pengguna dan Role

## 4.1 Level Platform

### Super Admin

Super Admin berada di level platform, bukan di dalam satu tenant tertentu.

Kebutuhan utama:

- melihat daftar tenant/fakultas;
- menambah tenant baru;
- mengelola subdomain tenant;
- melihat status tenant;
- mengaktifkan atau menonaktifkan tenant;
- melihat audit aktivitas tenant;
- memastikan proses provisioning berhasil.

## 4.2 Level Tenant

Role di dalam tenant:

| Role | Fungsi Utama |
|---|---|
| Mahasiswa | KRS, sidang, surat, monitoring, profil lulusan, dokumen TA |
| Dosen Pembimbing | Bimbingan, review TA, persetujuan dokumen, monitoring mahasiswa |
| Dosen Penguji | Jadwal menguji dan input nilai |
| Dosen PA | Approval KRS dan bimbingan akademik |
| Admin Prodi | Verifikasi, plotting, jadwal, master data akademik |
| Admin Fakultas / TU | Surat menyurat, arsip, operasional fakultas |
| Kaprodi | Approval, monitoring prodi, validasi plotting |
| Dekan / WD | Approval surat strategis dan monitoring fakultas |
| KBK | Validasi rumpun ilmu dan bidang keahlian |
| LPM / Gugus Mutu | Monitoring CPL/PLO dan kebutuhan akreditasi |
| Kepala Laboratorium | Kebutuhan asisten dan topik riset |
| Alumni / Pengguna Lulusan | Umpan balik profil lulusan |
| BAAK | Rekap akademik dan yudisium |

---

# 5. Struktur Filament Multi-Panel

SIFAK menggunakan Filament PHP Multi-Panel dan Laravel Livewire.

## 5.1 Panel Platform

### Super Admin Panel

Contoh struktur:

```text
/super-admin
├── Dashboard
├── Fakultas / Tenant
├── Domain / Subdomain
├── Provisioning Log
├── Tenant Status
└── Audit Log
```

## 5.2 Panel Tenant

Tenant menggunakan subdomain masing-masing.

Contoh:

```text
fasilkom.<domain-sifak>
```

Pada tenant tersebut tersedia panel:

```text
/mahasiswa
/dosen
/admin
/pimpinan
```

Panel dan menu yang terlihat disesuaikan berdasarkan role dan permission.

---

# 6. Arsitektur Produk

## 6.1 Prinsip Utama

SIFAK menggunakan prinsip:

- satu codebase;
- satu platform;
- banyak tenant;
- satu tenant mewakili satu fakultas;
- seluruh tenant menggunakan modul M1–M7 yang sama;
- setiap tenant memiliki konteks data sendiri;
- setiap tenant memiliki subdomain unik;
- setiap role hanya dapat mengakses data sesuai tenant dan hak aksesnya.

## 6.2 Arsitektur Tingkat Tinggi

```text
                        SIFAK
                          │
              ┌───────────┴───────────┐
              │                       │
       CENTRAL PLATFORM          TENANT SYSTEM
              │                       │
       Super Admin Panel        Tenant Resolver
              │                       │
       Tenant Management         Subdomain
                                      │
                         ┌────────────┼─────────────┐
                         │            │             │
                      FASILKOM       FEB        Fakultas lain
                         │            │             │
                       M1–M7        M1–M7          M1–M7
```

---

# 7. Product Requirement — Multi-Tenant

## 7.1 Pembuatan Tenant Baru

### User Story

Sebagai **Super Admin**, saya ingin menambahkan fakultas baru agar fakultas tersebut dapat menggunakan seluruh sistem SIFAK melalui subdomain sendiri.

### Input Minimal

Super Admin mengisi:

- nama fakultas;
- kode fakultas;
- nama singkat / tenant slug;
- admin awal tenant;
- status tenant.

### Flow

```text
Super Admin
    ↓
Tambah Fakultas
    ↓
Input Data Fakultas
    ↓
Generate Tenant Slug
    ↓
Regex Validation
    ↓
Reserved Subdomain Check
    ↓
Uniqueness Check
    ↓
Create Tenant
    ↓
Generate Subdomain
    ↓
Setup Tenant Context
    ↓
Aktifkan M1–M7
    ↓
Buat Admin Tenant
    ↓
Tenant Aktif
```

## 7.2 Subdomain

Format:

```text
<tenant-slug>.<domain-sifak>
```

Contoh:

```text
fasilkom.<domain-sifak>
feb.<domain-sifak>
fikom.<domain-sifak>
```

Regex:

```regex
^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$
```

### Reserved Subdomain

Minimal:

```text
www
admin
api
mail
app
assets
static
support
status
super-admin
```

## 7.3 Functional Requirements Multi-Tenant

| ID | Requirement |
|---|---|
| MT-FR-001 | Super Admin dapat melihat seluruh tenant. |
| MT-FR-002 | Super Admin dapat membuat tenant baru. |
| MT-FR-003 | Sistem menghasilkan tenant ID unik. |
| MT-FR-004 | Sistem menghasilkan tenant slug. |
| MT-FR-005 | Sistem memvalidasi slug dengan regex. |
| MT-FR-006 | Sistem melakukan reserved word validation. |
| MT-FR-007 | Sistem memastikan subdomain unik. |
| MT-FR-008 | Sistem mendaftarkan tenant baru. |
| MT-FR-009 | Sistem menyediakan seluruh M1–M7 pada tenant baru. |
| MT-FR-010 | Sistem membuat admin tenant pertama. |
| MT-FR-011 | Sistem menyediakan konteks penyimpanan tenant. |
| MT-FR-012 | Sistem mencatat aktivitas provisioning. |
| MT-FR-013 | Sistem dapat mengaktifkan tenant. |
| MT-FR-014 | Sistem dapat menonaktifkan sementara tenant. |
| MT-FR-015 | Sistem dapat mengaktifkan kembali tenant. |
| MT-FR-016 | Sistem menentukan tenant berdasarkan subdomain. |
| MT-FR-017 | Subdomain tidak valid tidak boleh memuat data tenant lain. |
| MT-FR-018 | User hanya dapat mengakses tenant tempat user terdaftar. |
| MT-FR-019 | Data tenant A tidak dapat diakses tenant B. |
| MT-FR-020 | Audit log tenant wajib disimpan. |

## 7.4 Acceptance Criteria Multi-Tenant

Tenant dianggap berhasil dibuat apabila:

- tenant ID berhasil dibuat;
- slug valid;
- slug tidak termasuk reserved word;
- subdomain unik;
- konfigurasi tenant tersedia;
- Modul M1–M7 tersedia;
- admin tenant tersedia;
- tenant dapat dibuka dari subdomain;
- user tenant hanya melihat data tenant tersebut;
- data FASILKOM tidak muncul pada tenant fakultas lain;
- data tenant lain tidak muncul pada FASILKOM;
- audit log provisioning tersimpan.

---

# 8. Product Requirement — M1 Sidang Sempro & TA

## 8.1 Tujuan

Memfasilitasi proses pendaftaran, verifikasi, plotting penguji, penjadwalan, pelaksanaan, hasil, revisi, dan berita acara sidang.

## 8.2 User Story Utama

- Sebagai mahasiswa, saya ingin mendaftar Sempro/TA secara online.
- Sebagai admin, saya ingin memverifikasi syarat sidang.
- Sebagai admin, saya ingin menentukan penguji.
- Sebagai dosen, saya ingin melihat jadwal sidang.
- Sebagai mahasiswa, saya ingin mengetahui status pendaftaran dan hasil sidang.

## 8.3 Functional Requirements

| ID | Requirement |
|---|---|
| M1-FR-001 | Mahasiswa dapat mengisi formulir Sempro/TA. |
| M1-FR-002 | Sistem memvalidasi SKS minimum. |
| M1-FR-003 | Sistem memvalidasi IPK. |
| M1-FR-004 | Sistem memvalidasi mata kuliah prasyarat. |
| M1-FR-005 | Sistem memvalidasi status pembayaran jika tersedia. |
| M1-FR-006 | Sistem memvalidasi berkas bimbingan. |
| M1-FR-007 | Admin dapat memverifikasi pendaftaran. |
| M1-FR-008 | Admin dapat menetapkan penguji. |
| M1-FR-009 | Sistem mencegah penguji sama dengan pembimbing. |
| M1-FR-010 | Sistem mencegah konflik jadwal. |
| M1-FR-011 | Sistem dapat menghasilkan jadwal sidang. |
| M1-FR-012 | Dosen dapat menginput hasil sidang. |
| M1-FR-013 | Sistem mencatat revisi. |
| M1-FR-014 | Sistem menghasilkan berita acara PDF. |
| M1-FR-015 | Sistem mengirim notifikasi terkait jadwal/status. |
| M1-FR-016 | Sistem dapat memberikan rekomendasi pembimbing/penguji berdasarkan M5. |
| M1-FR-017 | Mahasiswa dapat melihat ketersediaan/lokasi dosen yang relevan. |

## 8.4 Acceptance Criteria

- pendaftaran tidak dapat diproses jika syarat wajib tidak terpenuhi;
- jadwal tidak boleh bentrok;
- penguji tidak boleh sama dengan pembimbing;
- hasil sidang tersimpan;
- berita acara dapat dihasilkan;
- notifikasi terkirim sesuai event;
- seluruh data tersimpan dalam konteks tenant aktif.

---

# 9. Product Requirement — M2 Surat Menyurat

## 9.1 Tujuan

Mendigitalisasi proses pengajuan, approval, penomoran, template, arsip, dan audit surat.

## 9.2 Functional Requirements

| ID | Requirement |
|---|---|
| M2-FR-001 | User dapat memilih jenis surat. |
| M2-FR-002 | User dapat mengajukan surat secara online. |
| M2-FR-003 | User dapat mengunggah lampiran. |
| M2-FR-004 | Sistem mendukung approval berjenjang. |
| M2-FR-005 | Sistem menghasilkan nomor surat unik. |
| M2-FR-006 | Sistem menggunakan template surat. |
| M2-FR-007 | Sistem dapat melakukan merge field. |
| M2-FR-008 | Surat tersimpan dalam arsip digital. |
| M2-FR-009 | Surat dapat dicari. |
| M2-FR-010 | Semua perubahan status tercatat dalam audit trail. |
| M2-FR-011 | QR/tanda tangan digital dapat ditambahkan jika fitur diaktifkan. |

## 9.3 Acceptance Criteria

- nomor surat unik;
- approval mengikuti alur;
- arsip tersimpan;
- audit trail tersedia;
- data surat tenant A tidak muncul pada tenant B.

---

# 10. Product Requirement — M3 Monitoring & Alert

## 10.1 Tujuan

Memberikan monitoring progres akademik dan tugas akhir serta alert dini.

## 10.2 Rule Dasar

- IPK < 2.5 → alert akademik;
- SKS lulus < 50% pada semester 6 → alert risiko telat;
- belum daftar sempro padahal SKS cukup → alert;
- TA tidak memiliki progres 30 hari → alert pembimbing;
- bab tertentu belum diunggah dalam 14 hari → alert.

## 10.3 Functional Requirements

| ID | Requirement |
|---|---|
| M3-FR-001 | Sistem menampilkan dashboard per mahasiswa. |
| M3-FR-002 | Dashboard menampilkan semester. |
| M3-FR-003 | Dashboard menampilkan IPK. |
| M3-FR-004 | Dashboard menampilkan SKS. |
| M3-FR-005 | Dashboard menampilkan progres TA. |
| M3-FR-006 | Sistem menjalankan rule engine alert. |
| M3-FR-007 | Sistem mendukung notifikasi in-app. |
| M3-FR-008 | Sistem dapat mendukung email. |
| M3-FR-009 | Sistem dapat mendukung WhatsApp. |
| M3-FR-010 | Alert dapat dieskalasi ke Kaprodi. |
| M3-FR-011 | Sistem menyediakan dashboard agregat prodi/fakultas. |

## 10.4 Acceptance Criteria

- rule menghasilkan alert sesuai kondisi;
- alert memiliki status;
- alert dapat ditindaklanjuti;
- alert level tinggi dapat dieskalasi;
- dashboard hanya memuat data tenant aktif.

---

# 11. Product Requirement — M4 KRS & Penjadwalan

## 11.1 Tujuan

Mendukung pengisian KRS dan penjadwalan kuliah tanpa bentrok.

## 11.2 Functional Requirements

| ID | Requirement |
|---|---|
| M4-FR-001 | Mahasiswa dapat memilih mata kuliah. |
| M4-FR-002 | Sistem memvalidasi batas maksimum SKS. |
| M4-FR-003 | Sistem memvalidasi prasyarat MK. |
| M4-FR-004 | Sistem memvalidasi bentrok jadwal. |
| M4-FR-005 | Dosen PA dapat approve KRS. |
| M4-FR-006 | Sistem menghitung jumlah peminat MK. |
| M4-FR-007 | Sistem mendukung plotting dosen otomatis/manual. |
| M4-FR-008 | Sistem mempertimbangkan beban dosen. |
| M4-FR-009 | Sistem mempertimbangkan bidang dosen. |
| M4-FR-010 | Sistem dapat menghasilkan jadwal kuliah. |
| M4-FR-011 | Jadwal tidak boleh bentrok dosen. |
| M4-FR-012 | Jadwal tidak boleh bentrok ruang. |
| M4-FR-013 | Jadwal tidak boleh bentrok mahasiswa. |
| M4-FR-014 | Sistem dapat mengalokasikan ruang. |
| M4-FR-015 | Sistem dapat melakukan sinkronisasi kalender/notifikasi. |

## 11.3 Acceptance Criteria

- KRS tidak dapat disetujui selain oleh PA;
- jadwal bebas bentrok;
- jumlah peminat terhitung;
- plotting dosen tersimpan;
- seluruh proses berada dalam tenant aktif.

---

# 12. Product Requirement — M5 Profiling Dosen & Rekomendasi Pengajaran

## 12.1 Tujuan

Membangun profil dosen dan mendukung rekomendasi pengajaran serta informasi ketersediaan dosen.

## 12.2 Data Profil

- NIDN;
- jabatan akademik;
- pendidikan;
- rumpun ilmu;
- KBK;
- keahlian;
- sertifikasi;
- publikasi;
- pengalaman industri;
- riwayat mengajar;
- preferensi mengajar;
- beban dosen;
- lokasi;
- jadwal konsultasi.

## 12.3 Functional Requirements

| ID | Requirement |
|---|---|
| M5-FR-001 | Sistem menyimpan profil dosen. |
| M5-FR-002 | Sistem menghitung matriks kesesuaian dosen–MK. |
| M5-FR-003 | Sistem menghasilkan Top-N rekomendasi dosen. |
| M5-FR-004 | Sistem menghitung beban dosen. |
| M5-FR-005 | Sistem mencegah overload sesuai aturan. |
| M5-FR-006 | Dosen dapat memasukkan preferensi MK. |
| M5-FR-007 | Sistem menyimpan riwayat evaluasi. |
| M5-FR-008 | Sistem menyediakan dashboard KBK. |
| M5-FR-009 | Dosen dapat mengelola jadwal konsultasi. |
| M5-FR-010 | Sistem dapat menyimpan lokasi dosen dari geolocation. |
| M5-FR-011 | Mahasiswa hanya dapat melihat lokasi dosen yang terkait dengan bimbingan/pengujian sesuai aturan. |

## 12.4 Formula Rekomendasi

```text
Skor = (0.35 × kesesuaian_rumpun)
     + (0.25 × riwayat_mengajar_sukses)
     + (0.20 × publikasi_terkait)
     + (0.10 × sertifikasi/pelatihan)
     + (0.10 × preferensi_dosen)
     − penalti_overload
```

---

# 13. Product Requirement — M6 Profiling Mahasiswa & Rekomendasi Profil Lulusan

## 13.1 Tujuan

Membangun profil akademik mahasiswa serta memberikan rekomendasi profil lulusan, MK, tugas akhir, pembimbing, dan karier.

## 13.2 Functional Requirements

| ID | Requirement |
|---|---|
| M6-FR-001 | Sistem menyimpan nilai mahasiswa. |
| M6-FR-002 | Sistem menampilkan IPK dan tren nilai. |
| M6-FR-003 | Sistem menyimpan data minat. |
| M6-FR-004 | Sistem menyimpan organisasi, sertifikasi, portofolio, MBKM/magang. |
| M6-FR-005 | Sistem memetakan CPL → MK → PLO. |
| M6-FR-006 | Sistem menghitung skor CPL mahasiswa. |
| M6-FR-007 | Sistem menghasilkan Top-3 rekomendasi PLO. |
| M6-FR-008 | Sistem memberikan rekomendasi MK/konsentrasi. |
| M6-FR-009 | Sistem memberikan rekomendasi topik TA. |
| M6-FR-010 | Sistem memberikan rekomendasi calon pembimbing. |
| M6-FR-011 | Sistem memberikan rekomendasi karier/industri. |
| M6-FR-012 | Sistem menyediakan dashboard Prodi. |

## 13.3 Formula CPL

```text
Skor_CPL =
Σ (nilai_MK × bobot_MK_terhadap_CPL)
------------------------------------
Σ bobot_MK_terhadap_CPL
```

## 13.4 Formula PLO

```text
Skor_PL_i =
Σ (bobot_CPL_j × skor_CPL_mahasiswa_j)
---------------------------------------
Σ bobot_CPL_j
```

---

# 14. Product Requirement — M7 Manajemen Dokumen TA & Repositori Digital

## 14.1 Tujuan

Memfasilitasi penulisan, review, revisi, approval, kompilasi, dan penyimpanan dokumen TA.

## 14.2 Functional Requirements

| ID | Requirement |
|---|---|
| M7-FR-001 | Mahasiswa dapat menginput judul TA. |
| M7-FR-002 | Mahasiswa dapat menginput daftar isi. |
| M7-FR-003 | Mahasiswa dapat menginput daftar pustaka. |
| M7-FR-004 | Mahasiswa dapat mengelola Bab 1–5. |
| M7-FR-005 | Sistem menyediakan template fakultas. |
| M7-FR-006 | Sistem menyimpan setiap bab sebagai komponen. |
| M7-FR-007 | Sistem menyediakan versioning. |
| M7-FR-008 | Sistem memvalidasi kelengkapan bab. |
| M7-FR-009 | Sistem memvalidasi format daftar pustaka. |
| M7-FR-010 | Sistem memvalidasi konsistensi penomoran. |
| M7-FR-011 | Pembimbing dapat memberikan komentar. |
| M7-FR-012 | Pembimbing dapat memberikan approval per bab. |
| M7-FR-013 | Sistem menyimpan log revisi. |
| M7-FR-014 | Sistem dapat mengompilasi dokumen menjadi PDF/DOCX. |
| M7-FR-015 | Dokumen final disimpan pada repositori. |
| M7-FR-016 | Dokumen yang telah disetujui menjadi syarat pendaftaran sidang. |
| M7-FR-017 | Rekomendasi topik dari M6 dapat digunakan sebagai draft awal. |

## 14.3 Validasi Dokumen

```text
Status_Dokumen =
(Jumlah_Bab_Disetujui == 5)
AND (Daftar_Isi_Lengkap == true)
AND (Daftar_Pustaka_Valid == true)
AND (Format_Sesuai_Template == true)
```

---

# 15. Integrasi Antar-Modul

## 15.1 Event Internal

| Event | Dampak |
|---|---|
| KRS.approved | M4 hitung peminat → M5 rekomendasi dosen → M4 plotting final |
| Nilai.finalized | M6 update skor CPL → M3 evaluasi alert |
| Mahasiswa.daftar_sempro | M6 rekomendasi topik → M7 draft judul → M1 cek kesesuaian pembimbing |
| TA.bab_disimpan | M7 update progres → M3 evaluasi alert |
| Dosen.presensi_masuk | M5 update lokasi → M1 notifikasi ketersediaan |
| Tenant.created | Sistem mengaktifkan konteks tenant baru dan menyediakan M1–M7 |

---

# 16. Business Rules

## 16.1 Business Rules Utama

| Kode | Aturan |
|---|---|
| BR1 | Jadwal tidak boleh bentrok. |
| BR2 | Penguji tidak boleh sama dengan pembimbing. |
| BR3 | Nomor surat harus unik dan berurutan. |
| BR4 | KRS hanya dapat disetujui dosen PA. |
| BR5 | Alert level tinggi wajib dieskalasi ≤ 3 hari. |
| BR6 | Dosen tidak boleh mengajar MK di luar rumpun tanpa approval. |
| BR7 | Skor kesesuaian di bawah threshold wajib memiliki justifikasi. |
| BR8 | Rekomendasi PLO muncul jika mahasiswa menempuh ≥ 50% SKS. |
| BR9 | Profil hanya dapat diubah pemilik/admin berwenang. |
| BR10 | Rekomendasi bersifat saran dan keputusan akhir tetap pada manusia. |
| BR11 | Dokumen TA dapat didaftarkan sidang jika seluruh bab disetujui. |
| BR12 | Lokasi dosen hanya dilihat oleh mahasiswa yang terkait. |
| BR13 | Perubahan bab TA wajib tercatat dalam log revisi. |
| BR14 | Template TA mengikuti standar fakultas pada tahun akademik mahasiswa. |

## 16.2 Business Rules Multi-Tenant

| Kode | Aturan |
|---|---|
| BR15 | Hanya Super Admin yang dapat membuat tenant. |
| BR16 | Tenant memiliki ID unik. |
| BR17 | Tenant memiliki subdomain unik dan valid. |
| BR18 | Tenant baru memperoleh M1–M7. |
| BR19 | Data antar-tenant wajib terisolasi. |
| BR20 | Request hanya memuat tenant sesuai subdomain aktif. |
| BR21 | Subdomain tidak terdaftar tidak boleh diarahkan ke tenant lain. |
| BR22 | Tenant nonaktif tidak dapat digunakan user tenant. |
| BR23 | Menonaktifkan tenant tidak menghapus data secara otomatis. |
| BR24 | Aktivitas administrasi tenant wajib tercatat. |

---

# 17. Non-Functional Requirements

| Kategori | Requirement |
|---|---|
| Security | RBAC, encryption, audit log |
| Tenant Isolation | Data antar-tenant tidak boleh tercampur |
| Performance | Target halaman < 3 detik pada beban normal |
| Capacity | Mendukung 5.000+ mahasiswa sesuai infrastruktur |
| Availability | Target 99.5% uptime |
| Scalability | Modular dan dapat menambah tenant |
| Usability | Mobile-responsive |
| Auditability | Semua perubahan penting tercatat |
| Storage | Mendukung PDF, DOCX, versioning |
| Integration | SSO, SIAKAD, email/WA gateway, Maps |
| Maintainability | Satu codebase untuk seluruh tenant |
| Reliability | Kegagalan satu proses tenant tidak boleh memuat data tenant lain |
| Security Context | Tenant context wajib dibawa pada proses background |
| Data Separation | File dan data dipisahkan per tenant |

---

# 18. Data Model Tingkat Produk

## 18.1 Data Tenant

Data tambahan multi-tenant:

```text
tenants
tenant_domains
tenant_settings
tenant_audit_logs
```

Contoh informasi tenant:

```text
tenant_id
faculty_name
faculty_code
tenant_slug
subdomain
status
created_at
updated_at
```

## 18.2 Model Data Inti SIFAK

```text
mahasiswa
dosen
mata_kuliah
ruangan
krs
jadwal_kuliah
pendaftaran_sidang
surat
alert
dosen_profil
kbk
rumpun_ilmu
matriks_kesesuaian
beban_dosen
kurikulum
cpl
plo
pemetaan_mk_cpl
pemetaan_cpl_plo
skor_cpl_mahasiswa
rekomendasi_plo
minat_mahasiswa
dosen_lokasi
jadwal_konsultasi
dokumen_ta
bab_ta
log_revisi
template_dokumen
repositori_ta
```

---

# 19. Tech Stack

| Layer | Teknologi |
|---|---|
| Application Framework | Laravel |
| Presentation Layer | Filament PHP Multi-Panel + Laravel Livewire |
| UI | Blade + Tailwind CSS + Alpine.js |
| Database | MariaDB |
| Architecture | Modular Monolith + Multi-Tenant |
| Auth & Authorization | Laravel Auth + RBAC + Spatie Laravel Permission |
| Workflow | Laravel Custom Workflow / State Management |
| Notification | Laravel Notification + Firebase / WhatsApp API |
| Storage | Laravel Storage + S3-Compatible Storage / MinIO |
| Geolocation | Leaflet + OpenStreetMap / Google Maps API |
| Document Generator | DomPDF / Browsershot + PHPWord / LibreOffice Headless |
| Queue & Scheduler | Laravel Queue + Laravel Scheduler |
| Cache | Redis (opsional) |
| Web Server | Nginx |
| Deployment | Docker |
| Tenant Resolution | Subdomain-based tenant resolver |
| Domain | Wildcard subdomain / tenant domain mapping |

---

# 20. UX Requirement

## 20.1 Prinsip UX

- tampilan mobile-responsive;
- menu menyesuaikan role;
- dashboard menampilkan informasi yang relevan;
- user tidak melihat menu yang tidak memiliki permission;
- proses utama tidak bergantung pada tabel CRUD saja;
- fitur interaktif menggunakan Livewire custom page/component;
- Filament Resource digunakan untuk administrasi data yang sesuai.

## 20.2 Mahasiswa

Area utama:

```text
Dashboard
KRS
Jadwal
Sidang
Surat
Monitoring
Profil Lulusan
Bimbingan
Dokumen TA
```

## 20.3 Dosen

Area utama:

```text
Dashboard
Mahasiswa Bimbingan
Approval KRS
Jadwal Mengajar
Jadwal Sidang
Review Dokumen TA
Profil Dosen
Jadwal Konsultasi
```

## 20.4 Admin

Area utama:

```text
Dashboard
Master Data
KRS
Penjadwalan
Sidang
Surat
Monitoring
Profil Dosen
Profil Mahasiswa
Repository TA
```

## 20.5 Pimpinan

Area utama:

```text
Dashboard Fakultas
Monitoring Mahasiswa
Monitoring Prodi
CPL/PLO
Profiling Dosen
Alert
Laporan
```

---

# 21. Acceptance Criteria Global

Produk dinyatakan memenuhi requirement minimum apabila:

1. seluruh Modul M1–M7 dapat diakses sesuai role;
2. seluruh role memiliki permission yang sesuai;
3. data tenant tidak tercampur;
4. FASILKOM dapat digunakan sebagai tenant utama;
5. Super Admin dapat membuat tenant fakultas baru;
6. tenant baru memiliki subdomain valid;
7. tenant baru mendapatkan keseluruhan M1–M7;
8. user hanya dapat mengakses tenant tempat user terdaftar;
9. audit log tersedia;
10. workflow utama setiap modul dapat berjalan;
11. event antar-modul dapat diproses sesuai requirement;
12. dashboard menampilkan informasi sesuai role;
13. dokumen dapat disimpan dan dihasilkan;
14. KRS dapat divalidasi;
15. jadwal tidak bentrok;
16. alert dapat dihasilkan;
17. rekomendasi dosen/mahasiswa dapat dihitung sesuai formula yang didefinisikan.

---

# 22. Prioritas Pengembangan

## Phase 1 — Foundation

- Laravel application;
- MariaDB;
- authentication;
- RBAC;
- Filament Multi-Panel;
- Livewire;
- tenant foundation;
- FASILKOM tenant;
- master data;
- M4 KRS & Penjadwalan.

## Phase 2 — Academic Operation

- M5 Profiling Dosen;
- M1 Sidang Sempro & TA.

## Phase 3 — Monitoring & Recommendation

- M6 Profiling Mahasiswa;
- M3 Monitoring & Alert.

## Phase 4 — Administration & Document

- M2 Surat Menyurat;
- M7 Dokumen TA;
- dashboard LPM dan fakultas.

## Phase 5 — Multi-Tenant Expansion

- onboarding fakultas baru;
- subdomain generation;
- tenant provisioning;
- tenant activation/deactivation;
- tenant audit;
- validasi isolasi tenant.

---

# 23. Product Backlog Tingkat Tinggi

| Prioritas | Epic |
|---|---|
| P0 | Authentication & Authorization |
| P0 | Multi-Tenant Foundation |
| P0 | Master Data |
| P0 | Tenant FASILKOM |
| P1 | M4 KRS & Penjadwalan |
| P1 | M1 Sidang Sempro & TA |
| P1 | M5 Profiling Dosen |
| P1 | M3 Monitoring & Alert |
| P1 | M6 Profiling Mahasiswa |
| P2 | M2 Surat Menyurat |
| P2 | M7 Dokumen TA |
| P2 | Dashboard LPM/Fakultas |
| P2 | Tenant Expansion |

---

# 24. Risiko Produk

| Risiko | Dampak |
|---|---|
| Data antar-tenant tercampur | Sangat tinggi |
| Salah tenant context pada queue/job | Sangat tinggi |
| Konflik jadwal tidak terdeteksi | Tinggi |
| Role memiliki akses berlebih | Tinggi |
| File tenant tersimpan pada namespace salah | Tinggi |
| Proses tenant provisioning gagal | Sedang–tinggi |
| Subdomain duplikat | Sedang |
| Alert terlalu banyak | Sedang |
| Dokumen besar memengaruhi performa | Sedang |
| Formula rekomendasi tidak sesuai data | Sedang |

---

# 25. Definisi Selesai Tingkat Produk

Sebuah fitur dianggap selesai ketika:

- requirement telah diimplementasikan;
- akses role telah sesuai;
- tenant context telah tervalidasi;
- happy path dan negative path telah diuji;
- error state memiliki penanganan;
- audit/log tersedia jika diperlukan;
- UI dapat digunakan pada desktop dan mobile sesuai kebutuhan;
- integrasi antar-modul tidak merusak modul lain;
- requirement memiliki acceptance criteria yang dapat diuji.

---

# 26. Kesimpulan

SIFAK v2.1 mempertahankan keseluruhan fungsi 7 modul dari BRD sebelumnya dan menambahkan kemampuan multi-tenant sebagai lapisan platform. FASILKOM menjadi tenant utama/pilot. Fakultas baru ditambahkan oleh Super Admin dan memperoleh subdomain serta keseluruhan sistem M1–M7 tanpa membuat project aplikasi baru.

Arsitektur ini memungkinkan pengembangan tetap terpusat pada satu codebase Laravel dengan Filament PHP Multi-Panel, Laravel Livewire, dan MariaDB, sementara setiap fakultas tetap memiliki konteks penggunaan dan data masing-masing.

---

**SIFAK PRD v2.1 · Multi-Tenant · Draft Product Requirements**
