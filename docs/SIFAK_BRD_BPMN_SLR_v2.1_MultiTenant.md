# BUSINESS REQUIREMENTS & PROCESS DESIGN
## SISTEM INFORMASI FAKULTAS TERINTEGRASI (SIFAK)

**Business Requirements Document (BRD) · Business Process Model & Notation (BPMN) · Systematic Literature Review (SLR)**

**Versi:** 2.1  
**Status:** Final Draft  
**Ruang Lingkup:** Seluruh proses akademik & administrasi fakultas dalam satu aplikasi modular — 7 modul, termasuk manajemen dokumen tugas akhir dan penjadwalan bimbingan berbasis lokasi dosen, dengan penambahan arsitektur multi-tenant.

---

# Daftar Isi

1. Business Requirements Document (BRD)
2. BPMN — 9 Proses Bisnis + Arsitektur
3. Algoritma & Formula
4. Systematic Literature Review (SLR)
5. Rekomendasi Implementasi

---

# Bagian 1 — Business Requirements Document (BRD)

## 1.1 Latar Belakang & Tujuan

Fakultas mengelola banyak proses secara terpisah: pendaftaran sidang, surat menyurat, KRS, penjadwalan, monitoring mahasiswa, profiling dosen, pemetaan profil lulusan, serta pengelolaan dokumen tugas akhir. Kondisi ini menyebabkan duplikasi data, proses lambat, keputusan tidak berbasis data, dan kesulitan mahasiswa dalam mengatur jadwal bimbingan dengan dosen.

### Tujuan sistem

- Menyediakan satu aplikasi terintegrasi dengan master data bersama.
- Mempercepat administrasi sidang & surat.
- Meningkatkan kelulusan tepat waktu melalui monitoring & alert dini.
- Mengotomatisasi plotting dosen & jadwal kuliah dari KRS.
- Memberikan rekomendasi berbasis data untuk penempatan dosen mengajar dan pemetaan profil lulusan mahasiswa.
- Memudahkan mahasiswa melihat ketersediaan dan lokasi dosen untuk mengatur jadwal bimbingan.
- Menyediakan manajemen dokumen tugas akhir yang terstruktur (judul, daftar isi, daftar pustaka, bab 1–5) dan tersimpan sebagai satu kesatuan sesuai template fakultas.
- Menjadi *single source of truth* data akademik fakultas.

---

## 1.2 Ruang Lingkup (7 Modul)

| Kode | Modul | Fungsi Utama |
|---|---|---|
| M1 | Sidang Sempro & TA | Pendaftaran, verifikasi syarat, plotting penguji, penjadwalan, berita acara |
| M2 | Surat Menyurat | Pengajuan, template, approval berjenjang, arsip, nomor surat otomatis |
| M3 | Monitoring & Alert | Dashboard progres mahasiswa, rule engine, notifikasi, eskalasi |
| M4 | KRS & Penjadwalan | Pengisian KRS, plotting dosen pengampu, jadwal kuliah otomatis |
| M5 | Profiling Dosen & Rekomendasi Pengajaran | Profil dosen, rumpun ilmu, KBK, matriks kesesuaian, rekomendasi pengampu MK, lokasi & jadwal konsultasi |
| M6 | Profiling Mahasiswa & Rekomendasi Profil Lulusan | Profil mahasiswa, skor CPL, rekomendasi PLO, rekomendasi TA & karier |
| M7 | Manajemen Dokumen TA & Repositori Digital | Input judul, daftar isi, daftar pustaka, bab 1–5; kompilasi otomatis sesuai template fakultas; repositori digital |

> Di luar scope fase awal: keuangan/pembayaran, perpustakaan, akuntansi.

---

## 1.3 Stakeholder

| Peran | Kepentingan |
|---|---|
| Mahasiswa | Daftar sidang, isi KRS, terima alert, ajukan surat, lihat rekomendasi profil lulusan, atur jadwal bimbingan, unggah dokumen TA |
| Dosen Pembimbing | Verifikasi, bimbingan, jadi penguji, lihat profil & beban sendiri, kelola jadwal bimbingan |
| Dosen Penguji | Jadwal menguji, input nilai |
| Dosen PA | Approval KRS, bimbingan akademik, tindak lanjut alert |
| Admin Prodi | Plotting, verifikasi syarat, jadwal, input data dosen/MK |
| Admin Fakultas / TU | Surat menyurat, arsip, operasional harian |
| Kaprodi | Approval, monitoring prodi, validasi plotting & rumpun |
| Dekan/WD | Approval surat strategis, monitoring fakultas |
| KBK (Kelompok Bidang Keahlian) | Validasi rumpun ilmu & bidang keahlian dosen |
| LPM / Gugus Mutu | Monitoring capaian CPL/PLO, data akreditasi |
| Kepala Laboratorium | Kebutuhan asisten & topik riset |
| Alumni / Pengguna Lulusan | Umpan balik profil lulusan |
| BAAK | Rekap akademik & yudisium |

---

## 1.4 Kebutuhan Fungsional

### M1 — Sidang Sempro & TA

- **FR1.1** Mahasiswa mengisi formulir pendaftaran sidang (jenis: Sempro/TA).
- **FR1.2** Sistem validasi syarat otomatis: SKS lulus minimum, IPK, lulus matkul prasyarat, status pembayaran, berkas bimbingan.
- **FR1.3** Admin memverifikasi & menetapkan penguji.
- **FR1.4** Rule plotting penguji: 1 pembimbing + 2 penguji, hindari konflik jadwal & kesamaan bidang (opsional).
- **FR1.5** Sistem generate jadwal (tanggal, ruang, waktu) bebas bentrok.
- **FR1.6** Input hasil sidang + revisi + berita acara (PDF otomatis).
- **FR1.7** Notifikasi ke mahasiswa, pembimbing, penguji.
- **FR1.8** Rekomendasi calon pembimbing/penguji berdasarkan rumpun ilmu (integrasi M5).
- **FR1.9** Mahasiswa dapat melihat lokasi/ketersediaan dosen (berdasarkan jadwal mengajar & presensi geolocation) untuk mengatur jadwal bimbingan dengan pembimbing dan penguji.

### M2 — Surat Menyurat

- **FR2.1** Katalog jenis surat (aktif kuliah, izin penelitian, undangan, surat tugas, dll).
- **FR2.2** Pengajuan surat online + upload lampiran.
- **FR2.3** Approval berjenjang configurable (contoh: Prodi → Fakultas → Dekan).
- **FR2.4** Nomor surat otomatis & unik.
- **FR2.5** Template surat (merge field data mahasiswa/dosen).
- **FR2.6** Arsip digital + pencarian + audit trail.
- **FR2.7** Tanda tangan digital / QR verifikasi (opsional).

### M3 — Monitoring & Alert

- **FR3.1** Dashboard per mahasiswa: semester, IPK, SKS, progres TA, status.
- **FR3.2** Rule engine alert (configurable): IPK < 2.5 → alert akademik; SKS lulus < 50% di semester 6 → alert risiko telat; belum daftar sempro padahal SKS cukup → alert; TA tidak ada progres 30 hari → alert pembimbing; belum mengunggah bab tertentu dalam 14 hari → alert.
- **FR3.3** Kanal notifikasi: in-app, email, WhatsApp (opsional).
- **FR3.4** Eskalasi otomatis ke Kaprodi jika alert tidak ditindaklanjuti.
- **FR3.5** Dashboard agregat per prodi & fakultas.

### M4 — KRS & Penjadwalan

- **FR4.1** Mahasiswa isi KRS (validasi SKS maks, prasyarat, bentrok jadwal).
- **FR4.2** Approval dosen PA (Pembimbing Akademik).
- **FR4.3** Sistem hitung jumlah peminat per MK.
- **FR4.4** Plotting dosen pengampu otomatis/manual berdasarkan beban & bidang (integrasi M5).
- **FR4.5** Generate jadwal kuliah bebas bentrok (dosen, ruang, mahasiswa).
- **FR4.6** Alokasi ruang otomatis.
- **FR4.7** Sinkron ke kalender & notifikasi.

### M5 — Profiling Dosen & Rekomendasi Pengajaran

- **FR5.1** Profil dosen memuat: NIDN, jabatan akademik, pendidikan (S1/S2/S3 + bidang), rumpun ilmu, KBK, bidang keahlian spesifik (tag/skill), sertifikasi, publikasi, pengalaman industri.
- **FR5.2** Matriks kesesuaian dosen ↔ mata kuliah (skor 0–100 berdasarkan: rumpun, riwayat mengajar, publikasi terkait, pelatihan).
- **FR5.3** Rekomendasi otomatis dosen pengampu per MK (top-N kandidat).
- **FR5.4** Perhitungan beban dosen (SKS mengajar, bimbingan, penguji, penelitian) — cegah overload.
- **FR5.5** Preferensi dosen (MK yang ingin diampu) sebagai input.
- **FR5.6** Riwayat & evaluasi (nilai mahasiswa, feedback) untuk memperbaiki rekomendasi.
- **FR5.7** Alumni tracking dosen (dimana mengajar/sekarang).
- **FR5.8** Dashboard KBK — peta kekuatan & gap rumpun ilmu fakultas.
- **FR5.9** Manajemen lokasi & jadwal dosen: sistem mencatat jadwal mengajar, jam konsultasi, dan lokasi/keberadaan dosen di kampus (melalui presensi geolocation) sehingga mahasiswa dapat melihat ketersediaan dosen untuk bimbingan.

### M6 — Profiling Mahasiswa & Rekomendasi Profil Lulusan

- **FR6.1** Profil mahasiswa: nilai per MK, IPK, tren nilai, minat, organisasi, sertifikasi, portofolio, MBKM/magang.
- **FR6.2** Pemetaan CPL → MK → Profil Lulusan (PLO) dari kurikulum.
- **FR6.3** Skor capaian CPL per mahasiswa (dihitung dari nilai MK pembawa CPL).
- **FR6.4** Rekomendasi Profil Lulusan (top-3) berdasarkan skor CPL + minat + soft skill.
- **FR6.5** Rekomendasi MK pilihan / konsentrasi yang menutup gap CPL.
- **FR6.6** Rekomendasi topik TA & calon pembimbing (match dengan rumpun dosen — terhubung M5).
- **FR6.7** Rekomendasi karier / industri sesuai PLO (terhubung alumni & pengguna lulusan).
- **FR6.8** Dashboard Prodi: sebaran profil lulusan, gap CPL, kesiapan kerja.

### M7 — Manajemen Dokumen TA & Repositori Digital

- **FR7.1** Mahasiswa dapat menginput dan menyimpan judul TA, daftar isi, daftar pustaka, dan isi bab 1 sampai bab 5 melalui antarmuka terstruktur.
- **FR7.2** Sistem menyediakan template dokumen sesuai standar fakultas (format penulisan, margin, font, penomoran halaman, sitasi).
- **FR7.3** Setiap bab dapat disimpan sebagai komponen terpisah dan dikompilasi otomatis menjadi satu dokumen utuh (PDF/DOCX) sesuai template fakultas.
- **FR7.4** Riwayat versi dokumen (versioning) sehingga mahasiswa & pembimbing dapat melacak perubahan.
- **FR7.5** Validasi otomatis: kelengkapan bab, format daftar pustaka (misal APA/IEEE), konsistensi penomoran.
- **FR7.6** Pembimbing dapat memberikan komentar & persetujuan per bab melalui sistem.
- **FR7.7** Log revisi mencatat setiap perubahan, komentar, dan persetujuan secara kronologis.
- **FR7.8** Dokumen final yang telah disetujui tersimpan di repositori digital fakultas dan dapat diakses sesuai hak akses.
- **FR7.9** Integrasi dengan M1: dokumen TA yang telah disetujui menjadi syarat pendaftaran sidang.
- **FR7.10** Integrasi dengan M6: rekomendasi topik TA dan pembimbing dari M6 dapat langsung dijadikan draf awal judul di M7.

---

## 1.5 Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |
|---|---|
| Keamanan | RBAC, enkripsi data, audit log |
| Performa | Halaman < 3 detik, mendukung 5.000+ mahasiswa |
| Ketersediaan | 99.5% uptime |
| Skalabilitas | Modular, bisa tambah modul baru |
| Usability | Mobile-responsive |
| Integrasi | SSO kampus, SIAKAD existing (jika ada), email/WA gateway, Google Maps API (untuk lokasi dosen) |
| Audit | Semua perubahan data tercatat (siapa, kapan, apa) |
| Penyimpanan Dokumen | Mendukung penyimpanan file besar (PDF, DOCX), versioning, dan kompilasi otomatis |

---

## 1.6 Arsitektur “1 Aplikasi”

Prinsip utama:

- Modular monolith (mulai) → bisa dipecah jadi microservice nanti.
- Master data bersama → modul lain refer ke sini (tidak duplikat).
- Workflow engine bersama → dipakai M1, M2, M4, M7.
- Notification service bersama → dipakai semua modul.
- Event bus internal — contoh: KRS disetujui → trigger penjadwalan → trigger plotting dosen → M5 rekomendasi → M7 draf TA.

### Event Bus Internal

- `KRS.approved` → M4 hitung peminat → M5 beri rekomendasi dosen → M4 plotting final.
- `Nilai.finalized` → M6 update skor CPL → M3 evaluasi alert.
- `Mahasiswa.daftar_sempro` → M6 rekomendasi topik → M7 draf judul → M1 cek kesesuaian pembimbing.
- `TA.bab_disimpan` → M7 update progres → M3 evaluasi alert.
- `Dosen.presensi_masuk` → M5 update lokasi → M1 notifikasi ketersediaan bimbingan.

---

## 1.7 Model Data Inti

| Tabel | Deskripsi |
|---|---|
| mahasiswa | NIM, nama, prodi, angkatan, IPK, SKS |
| dosen | NIDN, nama, bidang, beban |
| mata_kuliah | Kode, SKS, prasyarat, semester |
| ruangan | Kode, kapasitas, tipe |
| krs | mahasiswa_id, mk_id, status_approval |
| jadwal_kuliah | mk_id, dosen_id, ruang_id, waktu |
| pendaftaran_sidang | mahasiswa_id, jenis, status, penguji[] |
| surat | jenis, pemohon, status, nomor, file |
| alert | mahasiswa_id, tipe, severity, status, tindak_lanjut |
| dosen_profil | nidn, rumpun_id, keahlian[], pendidikan, publikasi[], sertifikasi[] |
| kbk | id, nama, bidang |
| rumpun_ilmu | id, nama, deskripsi |
| matriks_kesesuaian | dosen_id, mk_id, skor, sumber |
| beban_dosen | dosen_id, semester, sks_mengajar, bimbingan, penguji |
| kurikulum | id, prodi, tahun, berlaku |
| cpl | id, kurikulum_id, deskripsi |
| plo | id, kurikulum_id, nama, deskripsi |
| pemetaan_mk_cpl | mk_id, cpl_id, bobot |
| pemetaan_cpl_plo | cpl_id, plo_id, bobot |
| skor_cpl_mahasiswa | mahasiswa_id, cpl_id, skor, semester |
| rekomendasi_plo | mahasiswa_id, plo_id, skor, tanggal |
| minat_mahasiswa | mahasiswa_id, kategori, nilai |
| dosen_lokasi | dosen_id, latitude, longitude, timestamp, status_kehadiran |
| jadwal_konsultasi | dosen_id, hari, jam_mulai, jam_selesai, ruang, tipe |
| dokumen_ta | mahasiswa_id, judul, daftar_isi, daftar_pustaka, status |
| bab_ta | dokumen_ta_id, nomor_bab, konten, versi, status |
| log_revisi | dokumen_ta_id, bab_id, komentar, dosen_id, timestamp, status |
| template_dokumen | id, prodi, versi, format, file_template |
| repositori_ta | dokumen_ta_id, file_final, tanggal_unggah, status_akses |

---

## 1.8 Aturan Bisnis Kunci

| Kode | Aturan |
|---|---|
| BR1 | Jadwal tidak boleh bentrok (dosen/ruang/mahasiswa). |
| BR2 | Penguji tidak boleh sama dengan pembimbing. |
| BR3 | Surat butuh nomor unik berurutan. |
| BR4 | KRS hanya bisa disetujui dosen PA. |
| BR5 | Alert level tinggi wajib eskalasi ≤ 3 hari. |
| BR6 | Dosen tidak boleh mengajar MK di luar rumpun tanpa persetujuan KBK/Kaprodi. |
| BR7 | Skor kesesuaian < threshold wajib ada justifikasi tertulis. |
| BR8 | Rekomendasi PLO hanya muncul jika mahasiswa sudah menempuh ≥ 50% SKS kurikulum. |
| BR9 | Data profil dosen & mahasiswa hanya bisa diubah oleh pemilik + admin berwenang. |
| BR10 | Semua rekomendasi bersifat saran; keputusan akhir tetap pada manusia (*human-in-the-loop*). |
| BR11 | Dokumen TA hanya bisa didaftarkan untuk sidang jika seluruh bab telah disetujui pembimbing. |
| BR12 | Lokasi dosen hanya dapat dilihat oleh mahasiswa yang dibimbing/diuji oleh dosen tersebut (privasi terjaga). |
| BR13 | Setiap perubahan pada bab TA wajib tercatat dalam log revisi. |
| BR14 | Template dokumen TA wajib mengikuti standar fakultas yang berlaku pada tahun akademik mahasiswa. |

---

## 1.9 Hak Akses per Role

| Aktor | Modul yang Diakses |
|---|---|
| Mahasiswa | M1, M2, M3, M4, M6, M7 (lihat rekomendasi sendiri, kelola dokumen TA, lihat lokasi dosen pembimbing/penguji) |
| Dosen | M1, M3, M4, M5 (profil sendiri), M6 (bimbingan), M7 (review & approval dokumen TA), kelola lokasi & jadwal konsultasi |
| Admin Prodi | M1, M4, M5 (input), M6, M7 (monitoring) |
| Admin Fakultas / TU | M1, M2, M3, M7 (repositori) |
| Kaprodi | M1, M2, M3, M5, M6, M7 (dashboard prodi) |
| Dekan/WD | M2, M3, M5, M6 (dashboard fakultas) |
| LPM / Gugus Mutu | M6 (capaian CPL/PLO), M3, M7 (repositori untuk akreditasi) |
| KBK | M5 (validasi rumpun) |
| BAAK | M1, M3, M6, M7 (rekap & yudisium) |

---

## 1.10 Manfaat per Pihak

| Pihak | Manfaat Nyata |
|---|---|
| Mahasiswa | Tahu profil lulusan yang cocok, dapat alert tepat waktu, KRS & sidang mudah, atur jadwal bimbingan dengan melihat lokasi dosen, kelola dokumen TA terstruktur |
| Dosen | Mengajar sesuai keahlian, beban adil, pembimbingan terarah, kelola jadwal konsultasi & review dokumen TA |
| Prodi | Plotting objektif, capaian CPL terukur, data akreditasi otomatis, monitoring dokumen TA |
| Fakultas | Surat & approval cepat, monitoring risiko lulus, repositori TA terpusat |
| LPM/Mutu | Data CPL/PLO siap pakai untuk akreditasi, repositori dokumen TA untuk audit |
| KBK | Peta kekuatan & gap rumpun ilmu, dasar rekrutmen/pelatihan dosen |

---

## 1.11 Roadmap Pengembangan

| Fase | Modul | Fokus |
|---|---|---|
| 1 | Master Data + Auth + M4 (KRS & Jadwal) | Fondasi data & proses inti |
| 2 | M5 (Profiling Dosen) + M1 (Sidang) | Kualitas pengajaran & sidang |
| 3 | M6 (Profiling Mahasiswa) + M3 (Monitoring) | Rekomendasi & kestabilan lulus |
| 4 | M2 (Surat) + M7 (Dokumen TA) + Dashboard LPM & Fakultas | Administrasi, repositori & pelaporan |

---

## 1.12 Penambahan Arsitektur Multi-Tenant

Penambahan multi-tenant dilakukan **tanpa mengubah ruang lingkup, fungsi, kode, dan kebutuhan Modul M1 sampai M7**. Multi-tenant menjadi lapisan tambahan agar satu sistem SIFAK dapat digunakan oleh lebih dari satu fakultas.

Pada implementasi awal, **FASILKOM menjadi tenant utama/pilot**. Setelah sistem FASILKOM stabil, Super Admin dapat menambahkan fakultas lain melalui panel pusat. Fakultas baru mendapatkan tenant baru dengan keseluruhan sistem SIFAK yang sama, termasuk Modul M1–M7, role, dashboard, workflow, notifikasi, dan fitur pendukung yang telah tersedia.

### 1.12.1 Konsep Multi-Tenant

- SIFAK tetap menggunakan satu codebase aplikasi.
- FASILKOM menjadi tenant pertama.
- Setiap fakultas baru direpresentasikan sebagai tenant baru.
- Setiap tenant menggunakan keseluruhan Modul M1–M7 yang sama.
- Setiap tenant memiliki data dan konfigurasi fakultas masing-masing.
- Data antar-tenant harus terisolasi.
- Setiap tenant memiliki subdomain unik sebagai identitas akses.
- Super Admin berada pada level platform dan bertugas mengelola tenant/fakultas.
- Penambahan tenant tidak membuat project aplikasi baru; sistem hanya membuat konteks tenant baru pada aplikasi SIFAK yang sama.

### 1.12.2 Super Admin dan Tenant Management

| Kode | Kebutuhan Multi-Tenant |
|---|---|
| MT1 | Super Admin dapat melihat daftar seluruh tenant/fakultas. |
| MT2 | Super Admin dapat menambahkan fakultas baru sebagai tenant. |
| MT3 | Super Admin mengisi data minimal fakultas: nama fakultas, kode fakultas, nama singkat/slug, dan admin awal tenant. |
| MT4 | Sistem menghasilkan identitas tenant unik ketika fakultas berhasil ditambahkan. |
| MT5 | Sistem menghasilkan subdomain tenant secara otomatis. |
| MT6 | Sistem melakukan validasi slug/subdomain menggunakan regex sebelum tenant dibuat. |
| MT7 | Sistem memastikan subdomain belum digunakan oleh tenant lain. |
| MT8 | Sistem menolak reserved subdomain seperti `www`, `admin`, `api`, `mail`, `app`, `assets`, dan subdomain sistem lainnya. |
| MT9 | Setelah tenant berhasil dibuat, seluruh Modul M1–M7 tersedia pada tenant tersebut. |
| MT10 | Sistem membuat konfigurasi awal tenant serta akun admin tenant/fakultas. |
| MT11 | Super Admin dapat mengaktifkan, menonaktifkan sementara, dan mengaktifkan kembali tenant. |
| MT12 | Seluruh proses pembuatan, perubahan status, dan perubahan konfigurasi tenant dicatat dalam audit log. |
| MT13 | User tenant hanya dapat beroperasi pada tenant tempat user tersebut terdaftar. |
| MT14 | Request dari subdomain yang tidak terdaftar tidak boleh diarahkan ke data tenant lain. |
| MT15 | FASILKOM ditetapkan sebagai tenant awal sebelum fakultas lain ditambahkan. |

### 1.12.3 Generate Subdomain

Format:

```text
<tenant-slug>.<domain-sifak>
```

Contoh:

```text
FASILKOM
tenant_slug : fasilkom
subdomain   : fasilkom.<domain-sifak>

Fakultas Ekonomi dan Bisnis
tenant_slug : feb
subdomain   : feb.<domain-sifak>
```

Regex validasi:

```regex
^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$
```

Aturan:

- hanya huruf kecil `a-z`, angka `0-9`, dan tanda hubung `-`;
- tidak boleh diawali atau diakhiri tanda hubung;
- harus unik;
- tidak boleh menggunakan reserved subdomain;
- jika slug tidak valid atau sudah digunakan, tenant tidak boleh dibuat sampai tersedia slug yang valid dan unik.

### 1.12.4 Alur Penambahan Fakultas Baru

```text
Super Admin
    ↓
Tambah Fakultas
    ↓
Isi Data Fakultas
    ↓
Generate Tenant Slug
    ↓
Validasi Regex
    ↓
Cek Reserved Subdomain
    ↓
Cek Keunikan Subdomain
    ↓
Generate Tenant Baru
    ↓
Generate Subdomain
    ↓
Siapkan Konfigurasi Tenant
    ↓
Aktifkan Keseluruhan Modul M1–M7
    ↓
Buat Admin Tenant
    ↓
Tenant Aktif
```

### 1.12.5 Isolasi Tenant

- Seluruh query data operasional berjalan dalam konteks tenant aktif.
- User tenant A tidak boleh membaca, mengubah, atau menghapus data tenant B.
- File dan dokumen dipisahkan berdasarkan tenant.
- Cache, queue job, notification, audit log, dan proses terjadwal membawa identitas tenant.
- Konfigurasi tenant tidak boleh mengubah konfigurasi tenant lain.
- Super Admin mengelola tenant pada level platform, sedangkan proses akademik tetap dilakukan oleh role di dalam masing-masing tenant.

### 1.12.6 Aturan Bisnis Tambahan Multi-Tenant

| Kode | Aturan |
|---|---|
| BR15 | Hanya Super Admin yang dapat membuat tenant/fakultas baru. |
| BR16 | Setiap tenant harus memiliki identitas tenant yang unik. |
| BR17 | Setiap tenant harus memiliki slug/subdomain yang unik dan valid. |
| BR18 | Tenant baru memperoleh keseluruhan Modul M1–M7 tanpa mengubah definisi modul tersebut. |
| BR19 | Data antar-tenant wajib terisolasi. |
| BR20 | Request hanya boleh memuat tenant yang sesuai dengan subdomain aktif. |
| BR21 | Subdomain yang tidak terdaftar tidak boleh diarahkan ke tenant lain. |
| BR22 | Tenant yang dinonaktifkan sementara tidak dapat digunakan oleh user tenant sampai diaktifkan kembali. |
| BR23 | Penonaktifan tenant tidak otomatis menghapus data tenant. |
| BR24 | Semua aksi administrasi tenant wajib tercatat dalam audit log. |

---

# Bagian 2 — BPMN (Business Process Model & Notation)

Format BPMN: **Pool → Lanes → Events → Tasks → Gateways → Flows**.

Sembilan proses bisnis inti SIFAK:

1. **BPMN 1 — Pendaftaran & Penjadwalan Sidang Sempro/TA (Terintegrasi M5 & M7)**
2. **BPMN 2 — Surat Menyurat**
3. **BPMN 3 — Monitoring & Alert**
4. **BPMN 4 — KRS, Plotting Dosen & Penjadwalan Kuliah**
5. **BPMN 5 — Profiling Dosen, Lokasi & Rekomendasi Pengampu MK**
6. **BPMN 6 — Profiling Mahasiswa & Rekomendasi Profil Lulusan**
7. **BPMN 7 — Manajemen Dokumen Tugas Akhir (Input, Kompilasi, Revisi, Repositori)**
8. **BPMN 8 — Jadwal Bimbingan Berbasis Lokasi Dosen**
9. **BPMN 9 — Integrasi End-to-End Overview (SIFAK Terintegrasi)**

> Diagram visual BPMN terdapat pada versi PDF. Markdown ini mempertahankan struktur dan penamaan proses bisnisnya.

---

# Bagian 3 — Algoritma & Formula

## 3.1 Skor Kesesuaian Dosen–MK

```text
Skor = (0.35 × kesesuaian_rumpun)
     + (0.25 × riwayat_mengajar_sukses)
     + (0.20 × publikasi_terkait)
     + (0.10 × sertifikasi/pelatihan)
     + (0.10 × preferensi_dosen)
     − penalti_overload
```

Keterangan:

- `kesesuaian_rumpun`: 0–100, dari kecocokan rumpun ilmu dosen dengan MK.
- `riwayat_mengajar_sukses`: 0–100, dari rata-rata nilai & feedback mahasiswa.
- `publikasi_terkait`: 0–100, dari jumlah & relevansi publikasi.
- `sertifikasi/pelatihan`: 0–100, dari sertifikat yang relevan.
- `preferensi_dosen`: 0–100, dari keinginan dosen mengampu MK.
- `penalti_overload`: pengurang jika beban dosen melebihi ambang.

**Output:** Top-N dosen per MK untuk direkomendasikan ke Admin Prodi.

## 3.2 Skor Profil Lulusan (PLO) Mahasiswa

```text
Skor_PL_i = Σ (bobot_CPL_j × skor_CPL_mahasiswa_j) / Σ bobot_CPL_j
Rekomendasi = Top-3 Skor_PL
Gap = CPL dengan skor < threshold → sarankan MK/kegiatan penutup
```

## 3.3 Skor CPL Mahasiswa

```text
Skor_CPL = Σ (nilai_MK × bobot_MK_terhadap_CPL) / Σ bobot_MK_terhadap_CPL
```

## 3.4 Validasi Kelengkapan Dokumen TA

```text
Status_Dokumen = (Jumlah_Bab_Disetujui == 5)
AND (Daftar_Isi_Lengkap == true)
AND (Daftar_Pustaka_Valid == true)
AND (Format_Sesuai_Template == true)
```

Jika `Status_Dokumen == true`, dokumen siap untuk pendaftaran sidang.

---

# Bagian 4 — Systematic Literature Review (SLR)

## 4.1 Tujuan SLR

1. Mengidentifikasi metode dan pendekatan yang telah digunakan dalam pengembangan sistem informasi akademik terintegrasi di perguruan tinggi Indonesia.
2. Menganalisis kesenjangan penelitian (*research gap*) yang menjadi dasar pengembangan SIFAK.
3. Memberikan landasan akademik bagi setiap keputusan desain dalam BRD, khususnya terkait manajemen dokumen tugas akhir, penjadwalan bimbingan berbasis lokasi, dan integrasi sistem.

## 4.2 Metode SLR

| Aspek | Keterangan |
|---|---|
| Pendekatan | Systematic Literature Review (SLR) berdasarkan pedoman PRISMA |
| Rentang Literatur | 2019–2026 |
| Sumber | Garuda, SINTA, Google Scholar, jurnal nasional terakreditasi |
| Kriteria Inklusi | Artikel jurnal SINTA 2 atau lebih tinggi; bahasa Indonesia/Inggris; relevan dengan sistem informasi akademik, BPMN, penjadwalan, sistem rekomendasi, manajemen dokumen TA, atau monitoring mahasiswa |
| Kriteria Eksklusi | Artikel non-peer-reviewed; artikel tanpa metodologi jelas; duplikasi |
| Jumlah Artikel Terpilih | 18 artikel utama (SINTA 2) + 4 artikel pendukung |

## 4.3 Hasil SLR per Tema

### Tema 1 — Pemodelan Proses Bisnis dengan BPMN dalam Sistem Akademik

Husein et al. (2024) mengembangkan SIAKAD Mobile dengan API Service menggunakan metode SCRUM, di mana BPMN digunakan untuk membuat, merancang, dan mendesain proses bisnis aplikasi.

Studi Fauzi et al. (2026) merancang Sistem Informasi Terintegrasi (SIUNU) dengan menggabungkan BPMN business process modeling dan analisis matriks RACI untuk menghasilkan arsitektur modular.

**Implikasi untuk SIFAK:** BPMN digunakan untuk seluruh proses bisnis utama SIFAK.

### Tema 2 — Penjadwalan Kuliah Berbasis Algoritma

Fatchurrochman et al. (2023) meneliti algoritma penanganan constraint pada penjadwalan perkuliahan universitas di PTKI dengan sequential search.

**Implikasi untuk SIFAK:** Modul M4 dapat mengadopsi sequential search sebagai baseline algoritma.

### Tema 3 — Sistem Rekomendasi Dosen & Pemilihan Pembimbing

Wondal et al. (2024) mengimplementasikan Hybrid Recommender System dengan Content-Based Filtering (TF-IDF dan Cosine Similarity) serta Constraint-Based Filtering.

Suahati et al. (2024) menggunakan Simple Additive Weighting (SAW) untuk pemilihan dosen pembimbing tugas akhir.

**Implikasi untuk SIFAK:** Modul M5 dapat menggunakan pendekatan hybrid berdasarkan publikasi, keahlian, dan aturan bisnis.

### Tema 4 — Sistem Informasi Tugas Akhir & E-Document

Juliane et al. (2019) meneliti kualitas Sistem Informasi Administrasi Tugas Akhir (SIATA) menggunakan metode McCall's.

Kurniawan & Wicakso (2020) mengembangkan Sistem Informasi Terintegrasi Tugas Akhir/Skripsi Berbasis Web.

**Implikasi untuk SIFAK:** Modul M7 mengadopsi konsep e-document dan integrasi multi-aktor.

### Tema 5 — Penjadwalan Bimbingan Berbasis Lokasi & CRM

Studi di Garuda (2025) mengembangkan sistem informasi penjadwalan bimbingan TA berbasis website dengan konsep CRM.

Yusuf & Setiawati (2024) serta Hidayat & Hafli (2025) membahas presensi dosen berbasis geolocation.

**Implikasi untuk SIFAK:** Modul M5 mengintegrasikan data lokasi & jadwal konsultasi dosen berbasis geolocation, dan Modul M1/M7 menyediakan fitur penjadwalan bimbingan berbasis ketersediaan dosen.

### Tema 6 — Portal Bimbingan & Log Revisi Dokumen TA

Suhartanto & Budi (2025) merancang Portal Bimbingan Tugas Akhir dengan log revisi, persetujuan, dan arsip digital.

**Implikasi untuk SIFAK:** Modul M7 mengadopsi fitur log revisi, persetujuan digital per bab, dan arsip digital.

### Tema 7 — SLR dalam Konteks Sistem Informasi Akademik

Hasan et al. (2023) melakukan SLR terhadap metode perancangan SI akademik berbasis web.

Al Mutawakkil et al. (2026) menganalisis penerapan Governance, Risk, and Compliance (GRC) dalam SIAKAD.

**Implikasi untuk SIFAK:** SIFAK perlu mengadopsi prinsip GRC sejak desain awal.

### Tema 8 — Teknologi Cloud & Monitoring Kelulusan

Nugroho (2023) meneliti peningkatan performa SI akademik menggunakan cloud computing.

Studi di Jurnal Pendidikan Progresif mengembangkan prediksi kelulusan tepat waktu menggunakan Decision Tree.

**Implikasi untuk SIFAK:** Modul M3 dapat mengintegrasikan model prediktif Decision Tree sebagai rule engine tambahan.

## 4.4 Matriks Sintesis Literatur

| No | Penulis (Tahun) | Jurnal | Metode/Fokus | Relevansi ke SIFAK |
|---|---|---|---|---|
| 1 | Husein et al. (2024) | Jurnal Sinkron | SCRUM + BPMN untuk SIAKAD Mobile | M1–M4 |
| 2 | Fatchurrochman et al. (2023) | Jurnal JEPIN | Sequential search + 10 constraint | M4 |
| 3 | Wondal et al. (2024) | Jurnal Nasional | Hybrid Recommender System | M5 |
| 4 | Suahati et al. (2024) | Jurnal ITN | SAW untuk pemilihan dosen pembimbing | M5 |
| 5 | Juliane et al. (2019) | Jurnal RESTI | McCall's | M7 |
| 6 | Kurniawan & Wicakso (2020) | Jurnal SIMADA | Sistem Informasi Terintegrasi TA | M7 |
| 7 | Yusuf & Setiawati (2024) | Jurnal JIFORTY | Presensi dosen berbasis geolocation | M5 |
| 8 | Hidayat & Hafli (2025) | Jurnal SITERA | PIN + Geolocation + Google Maps API | M5 |
| 9 | Suhartanto & Budi (2025) | Jurnal Eduscotech | Portal bimbingan TA | M7 |
| 10 | Garuda (2025) | Garuda | Penjadwalan bimbingan TA berbasis website + CRM | M1/M7 |
| 11 | Hasan et al. (2023) | Jurnal Manajemen Mutu Pendidikan | SLR metode perancangan SI akademik | Metodologi |
| 12 | Al Mutawakkil et al. (2026) | Jurnal JIAN | SLR GRC dalam SIAKAD | Tata kelola & risiko |
| 13 | Fauzi et al. (2026) | Jurnal Locus | Enterprise Architecture TOGAF ADM | Arsitektur enterprise |
| 14 | Reza (2025) | Jurnal DJIT | Literature review AIS di PTKIN | Konteks institusi |
| 15 | Nugroho (2023) | IJAIR | Cloud computing untuk SI akademik | Arsitektur teknis |
| 16 | Jurnal Pendidikan Progresif (2021) | JPP | Decision Tree prediksi kelulusan | M3 |
| 17 | Garuda (2024) | Garuda | BPMN 2.0 + RACI untuk SIUNU modular | M1–M4 |
| 18 | Mahendra (2024) | Thesis/Paper | Predictive analytics + learning dashboard | M3, M6 |

## 4.5 Research Gap & Posisi SIFAK

| No | Gap | Kondisi Existing | Solusi SIFAK |
|---|---|---|---|
| 1 | Fragmentasi sistem | Penelitian fokus satu modul | Integrasi 7 modul dalam 1 aplikasi |
| 2 | Profiling dosen belum terintegrasi | Kriteria rekomendasi terbatas | Integrasi rumpun, KBK, publikasi, beban, preferensi, lokasi |
| 3 | Profil lulusan tidak dipetakan ke CPL | CPL/PLO masih statis | Skor CPL → rekomendasi PLO otomatis |
| 4 | GRC belum terintegrasi | Governance operasional, risiko reaktif | Audit trail, RBAC, manajemen risiko |
| 5 | Monitoring masih manual | Rentan kesalahan | Rule engine + eskalasi otomatis + prediksi |
| 6 | Dokumen TA tersebar | Tidak terstruktur | M7 dengan input terstruktur, kompilasi, log revisi, repositori |
| 7 | Jadwal bimbingan sulit | Ketersediaan & lokasi dosen sulit diketahui | Geolocation + jadwal konsultasi |
| 8 | Interoperabilitas terbatas | Integrasi sistem nasional terbatas | API terbuka untuk integrasi eksternal |

## 4.6 Kesimpulan SLR

1. BPMN 2.0 efektif sebagai alat pemodelan proses bisnis sistem akademik.
2. Sequential search efektif sebagai baseline penjadwalan dengan constraint.
3. Sistem rekomendasi hybrid dapat digunakan untuk reviewer/pembimbing.
4. Sistem TA berbasis e-document membantu pengelolaan administrasi.
5. Portal bimbingan dengan log revisi meningkatkan transparansi.
6. Geolocation dapat mendukung pencatatan kehadiran dan lokasi dosen.
7. Gap utama adalah belum adanya sistem terintegrasi yang menggabungkan seluruh fungsi utama SIFAK dalam satu platform.

---

## Daftar Pustaka (SLR)

1. Husein, A. M., Simanjuntak, A. J., Sinaga, C. J., Tampubolon, M. M., & Situmorang, P. N. C. (2024). *SIAKAD Mobile With API Service To Improve Academic Services*. Jurnal Sinkron, 8(2).
2. Fatchurrochman, F., Nur, A., Arif, Z., Arifin, M., & Mahmudy, W. (2023). *Algoritma Penanganan Constraint pada Persoalan Penjadwalan Perkuliahan Universitas di (PTKI)*. Jurnal JEPIN, 9(2), 331–338.
3. Wondal, F. M., Bawiling, G. E., Polii, A. S. C., & Tangkudung, R. (2024). *Implementasi Sistem Rekomendasi Hybrid untuk Penentuan Reviewer dan Rekomendasi Anggota Tim Peneliti*.
4. Suahati, A. F., Nurrahman, A. A., & Tunjung, A. F. (2024). *Perancangan Sistem Pendukung Keputusan untuk Pemilihan Dosen Pembimbing Tugas Akhir dengan Metode Simple Additive Weighting*.
5. Juliane, C., Dzulkarnaen, R., & Susanti, W. (2019). *Metode McCall's untuk Pengujian Kualitas Sistem Informasi Administrasi Tugas Akhir (SIATA)*. Jurnal RESTI, 3(3), 488–495.
6. Kurniawan, H., & Wicakso, B. (2020). *Sistem Informasi Terintegrasi Tugas Akhir/Skripsi Berbasis Web*. Jurnal SIMADA.
7. Yusuf, D., & Setiawati, S. (2024). *Pengembangan Sistem Presensi Dosen Berbasis Geolocation Untuk Meningkatkan Akurasi Dan Efisiensi Pencatatan Kehadiran Perkuliahan*. Jurnal JIFORTY, 5(2), 107–118.
8. Hidayat, R., & Hafli, M. (2025). *Pengembangan Sistem Presensi Dosen Berbasis PIN dan Geolocation untuk Meningkatkan Akurasi dan Akuntabilitas Kehadiran*. Jurnal SITERA.
9. Suhartanto, A., & Budi, I. (2025). *Rancang Bangun Portal Bimbingan Tugas Akhir (Log Revisi, Persetujuan, Arsip) Berbasis Web*.
10. Garuda. (2025). *Sistem Informasi Penjadwalan Bimbingan Tugas Akhir Berbasis Website dengan Konsep CRM*.
11. Hasan, A. R., Chotimah, C., & Junaris, I. (2023). *Analisis Manajemen Metode Perancangan Sistem Informasi Akademik Berbasis Web: Systematic Literatur Review*.
12. Al Mutawakkil, M. S., Murchan, M., & Ramly. (2026). *Analisis Penerapan Prinsip Governance, Risk, and Compliance dalam Sistem Informasi Akademik*.
13. Fauzi, C., Pribadi, D. S., Rifan, M., & Fauziyya. (2026). *Integrating Academic Information System Based on Enterprise Architecture using ETL: A Case Study of SIUNU Using the TOGAF ADM Framework*.
14. Reza, L. (2025). *Tracing the Digital Path: Academic Information Systems in Indonesian Islamic Universities*.
15. Nugroho, H. W. (2023). *Improving the Performance of Higher Education Academic Information Systems Using Cloud Computing Technology*.
16. Jurnal Pendidikan Progresif. (2021). *Predicting On-time Graduation based on Student Performance in Core Introductory Computing Courses using Decision Tree Algorithm*.
17. Garuda. (2024). *Integrating Academic Information System Based on Enterprise Architecture: SIUNU Modular*.
18. Mahendra, A. S. P. (2024). *Integrating Predictive Analytics and Learning Dashboards to Improve Graduation Timeliness: A Study of Higher Education in Indonesia*.

---

# Bagian 5 — Rekomendasi Implementasi

## 5.1 Tech Stack

| Layer | Teknologi |
|---|---|
| Application Framework | Laravel |
| Presentation Layer | Filament PHP Multi-Panel + Laravel Livewire |
| UI | Blade + Tailwind CSS + Alpine.js |
| Database | MariaDB |
| Architecture | Modular Monolith |
| Authentication & Authorization | Laravel Auth + RBAC + Spatie Laravel Permission |
| Workflow | Laravel Custom Workflow / State Management |
| Notification | Laravel Notification + Firebase / WhatsApp API |
| File Storage | Laravel Storage + S3-Compatible Storage / MinIO |
| Geolocation | Leaflet + OpenStreetMap / Google Maps API |
| Document Generator | DomPDF / Browsershot + PHPWord / LibreOffice Headless |
| Queue & Scheduler | Laravel Queue + Laravel Scheduler |
| Cache | Redis (Opsional) |
| Web Server | Nginx |
| Deployment | Docker |

---

## 5.2 Fase Pengembangan

| Fase | Modul | Fokus |
|---|---|---|
| 1 | Master Data + Auth + M4 | Fondasi data & proses inti |
| 2 | M5 + M1 | Kualitas pengajaran & sidang |
| 3 | M6 + M3 | Rekomendasi & kestabilan lulus |
| 4 | M2 + M7 + Dashboard LPM & Fakultas | Administrasi, repositori & pelaporan |

---

## 5.3 Prinsip 1 Aplikasi

- Satu login, satu database master pada konteks tenant, satu workflow engine, satu notification service, satu UI shell dengan menu per modul.
- Modul saling komunikasi lewat event internal.
- Setiap role memiliki dashboard yang relevan.
- M7 terintegrasi dengan M1 dan M6.

---

## 5.4 Implementasi Multi-Tenant

Penambahan multi-tenant mengikuti tech stack yang telah ditetapkan pada Bagian 5.1 dan tidak mengganti teknologi yang sudah ada.

| Komponen Tambahan | Implementasi |
|---|---|
| Tenant Management | Laravel |
| Central Super Admin | Filament PHP Multi-Panel |
| Tenant Resolution | Subdomain-based tenant resolver |
| Subdomain Validation | Regex + uniqueness check + reserved word validation |
| Database | MariaDB dengan isolasi data per tenant sesuai rancangan teknis |
| Tenant UI | Filament PHP Multi-Panel + Laravel Livewire |
| Tenant Context | Middleware / service tenant context |
| File Isolation | Laravel Storage dengan namespace/pemisahan per tenant |
| Queue & Scheduler | Laravel Queue + Laravel Scheduler dengan tenant context |
| Audit | Audit log mencatat tenant, aktor, aksi, dan waktu |
| Deployment | Nginx + Docker dengan dukungan wildcard subdomain |

### Prinsip Provisioning

Ketika Super Admin menambahkan fakultas baru, sistem melakukan provisioning tenant tanpa membuat codebase baru:

1. membuat record tenant;
2. menghasilkan tenant ID dan tenant slug;
3. memvalidasi slug menggunakan regex;
4. memastikan subdomain unik;
5. mendaftarkan subdomain;
6. menyiapkan konfigurasi tenant;
7. menyediakan seluruh Modul M1–M7;
8. membuat akun admin tenant;
9. menyiapkan pemisahan storage/data tenant;
10. mencatat proses provisioning pada audit log.

### Contoh Struktur Akses

```text
Central / Super Admin
└── admin.<domain-sifak>
    └── Kelola Tenant / Fakultas

Tenant FASILKOM
└── fasilkom.<domain-sifak>
    └── Seluruh Sistem SIFAK M1–M7

Tenant Fakultas Baru
└── <tenant-slug>.<domain-sifak>
    └── Seluruh Sistem SIFAK M1–M7
```

FASILKOM tetap menjadi fokus utama implementasi awal. Penambahan fakultas lain dilakukan melalui mekanisme tenant setelah sistem utama FASILKOM siap digunakan.

---

**SIFAK BRD/BPMN/SLR v2.1 · Final Draft**
