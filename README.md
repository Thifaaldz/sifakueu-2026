# SIFAK EU - Sistem Informasi Fakultas Terintegrasi

SIFAK adalah aplikasi Laravel + Filament untuk sistem informasi fakultas terintegrasi berbasis multi-tenant. Implementasi awal menggunakan tenant pilot FASILKOM dan modul utama M1-M7 sesuai PRD/BRD.

## Link Akses Lokal

Jalankan aplikasi:

```bash
docker compose up -d --build
./scripts/sifak-local-setup.sh
```

Jika ingin tenant baru langsung bisa dibuka sebagai `<slug>.sifakueu.test` tanpa sync hosts ulang, aktifkan wildcard DNS lokal sekali:

```bash
./scripts/sifak-local-wildcard-dns.sh
./scripts/sifak-local-setup.sh
```

`sifak-local-wildcard-dns.sh` memakai NetworkManager/dnsmasq dan akan meminta password `sudo`.

Tambahkan host lokal jika belum ada:

```text
127.0.0.1 sifakueu.test
127.0.0.1 fasilkom.sifakueu.test
```

Link utama:

```text
Super Admin      : https://sifakueu.test/admin
Admin Tenant     : https://fasilkom.sifakueu.test/admin
Mahasiswa Tenant : https://fasilkom.sifakueu.test/mahasiswa
Dosen Tenant     : https://fasilkom.sifakueu.test/dosen
Pimpinan Tenant  : https://fasilkom.sifakueu.test/pimpinan
```

Catatan: sertifikat SSL lokal berada di `nginx/ssl`. Jalankan `./scripts/sifak-local-setup.sh` untuk generate mkcert wildcard `sifakueu.test` dan `*.sifakueu.test`, sync `/etc/hosts`, lalu restart Nginx.

Jika muncul `DNS_PROBE_FINISHED_NXDOMAIN`, berarti domain lokal belum terdaftar di `/etc/hosts`. Jalankan:

```bash
sudo sh -c 'printf "\n127.0.0.1 sifakueu.test\n127.0.0.1 fasilkom.sifakueu.test\n" >> /etc/hosts'
```

Lalu buka ulang browser atau flush DNS cache browser.

Setelah menambah tenant/fakultas baru dari panel, sinkronkan domain tenant ke `/etc/hosts` dengan:

```bash
./scripts/sifak-local-setup.sh
```

Script ini membuat/refresh SSL wildcard lokal, membaca seluruh tenant dari database melalui Docker, lalu menambahkan domain yang belum ada ke `/etc/hosts`.

Jika `sifak-local-wildcard-dns.sh` sudah aktif, langkah sync hosts tidak perlu diulang untuk tenant baru.

## Akun Seed

```text
Super Admin
Email    : admin@admin.com
Password : password
Panel    : https://sifakueu.test/admin

Admin FASILKOM
Email    : admin.fasilkom@sifak.local
Password : password
Panel    : https://fasilkom.sifakueu.test/admin

Dosen
Email    : dosen.pembimbing.fasilkom@sifak.local
Password : password
Panel    : https://fasilkom.sifakueu.test/dosen

Mahasiswa
Email    : mahasiswa.fasilkom@sifak.local
Password : password
Panel    : https://fasilkom.sifakueu.test/mahasiswa
```

## Modul Sistem

```text
M1 - Sidang Sempro & TA
M2 - Surat Menyurat
M3 - Monitoring & Alert
M4 - KRS & Penjadwalan
M5 - Profiling Dosen & Rekomendasi Pengajaran
M6 - Profiling Mahasiswa & Rekomendasi Profil Lulusan
M7 - Manajemen Dokumen TA & Repositori Digital
MT - Multi-Tenant Fakultas
```

## Jobdesk Role

### Super Admin

Level platform. Mengelola tenant/fakultas, slug/subdomain, status tenant, provisioning admin awal, dan audit provisioning. Super Admin tidak menjalankan proses akademik harian tenant.

Menu utama:

```text
Platform -> Fakultas / Tenant
Administration -> User
Activity / Audit Log
```

### Admin Fakultas / TU

Level tenant. Mengelola operasional fakultas, surat menyurat, arsip, repositori TA, dan dukungan administrasi sidang.

Fokus modul:

```text
M1 Sidang
M2 Surat
M3 Monitoring
M7 Dokumen TA
Master Data tenant
```

### Admin Prodi

Mengelola data akademik prodi, verifikasi syarat sidang, plotting dosen, jadwal kuliah, dan monitoring mahasiswa prodi.

Fokus modul:

```text
Master Data Prodi
M1 Sidang
M4 KRS & Jadwal
M5 Profiling Dosen
M6 Profiling Mahasiswa
M7 Monitoring Dokumen TA
```

### Mahasiswa

Mengisi KRS, mengajukan surat, mendaftar sidang, menerima alert akademik, melihat rekomendasi profil lulusan, dan mengelola dokumen TA.

Fokus modul:

```text
M1 Pendaftaran Sidang
M2 Pengajuan Surat
M3 Alert Pribadi
M4 KRS
M6 Profil Mahasiswa
M7 Dokumen TA
```

### Dosen Pembimbing

Membimbing mahasiswa, review dokumen TA, memberi komentar/revisi, menyetujui bab, melihat progres mahasiswa, dan mengelola jadwal konsultasi.

Fokus modul:

```text
M1 Sidang bimbingan
M3 Monitoring mahasiswa bimbingan
M5 Profil dosen dan jadwal konsultasi
M7 Review Dokumen TA
```

### Dosen Penguji

Melihat jadwal sidang sebagai penguji, memberi penilaian, dan mencatat hasil/revisi sidang.

Fokus modul:

```text
M1 Jadwal Sidang
M1 Input Hasil Sidang
M1 Revisi dan Berita Acara
```

### Dosen PA

Mengecek dan menyetujui KRS mahasiswa bimbingan akademik, serta menindaklanjuti alert akademik.

Fokus modul:

```text
M3 Monitoring & Alert
M4 Approval KRS
```

### Kaprodi

Mengawasi proses akademik prodi, validasi plotting dosen, approval tertentu, monitoring mahasiswa, dan memastikan progres TA berjalan.

Fokus modul:

```text
M1 Sidang
M2 Approval Surat tertentu
M3 Dashboard Prodi
M4 Jadwal dan Plotting
M5 Validasi Dosen
M6 Dashboard Profil Lulusan
M7 Monitoring TA
```

### Dekan / Wakil Dekan

Mengawasi proses fakultas, approval surat strategis, dan dashboard monitoring fakultas.

Fokus modul:

```text
M2 Approval Surat Strategis
M3 Dashboard Fakultas
M5 Peta Beban dan Keahlian Dosen
M6 Sebaran Profil Lulusan
```

### KBK

Memvalidasi rumpun ilmu, bidang keahlian dosen, dan rekomendasi pengampu mata kuliah.

Fokus modul:

```text
M5 Rumpun Ilmu
M5 KBK
M5 Matriks Kesesuaian Dosen-MK
```

### LPM / Gugus Mutu

Memantau capaian CPL/PLO, kebutuhan akreditasi, kualitas dokumen TA, dan data mutu akademik.

Fokus modul:

```text
M3 Monitoring
M6 CPL/PLO dan Profil Lulusan
M7 Repositori TA
```

### Kepala Laboratorium

Melihat kebutuhan topik riset, kebutuhan asisten, dan dukungan data terkait lab.

Fokus modul:

```text
M5 Profil Dosen dan Riset
M6 Minat Mahasiswa
M7 Topik TA
```

### Alumni / Pengguna Lulusan

Memberikan umpan balik profil lulusan dan relevansi kompetensi dengan kebutuhan industri.

Fokus modul:

```text
M6 Feedback Profil Lulusan
M6 Rekomendasi Karier
```

### BAAK

Melihat rekap akademik, yudisium, status sidang, progres kelulusan, dan data mahasiswa lintas kebutuhan administratif.

Fokus modul:

```text
M1 Rekap Sidang
M3 Monitoring Kelulusan
M6 Rekap Akademik
M7 Repositori Final
```

## Alur Kerja Awal

1. Login sebagai Super Admin.
2. Pastikan tenant FASILKOM tersedia di `Platform -> Fakultas / Tenant`.
3. Login sebagai Admin FASILKOM.
4. Lengkapi master data: Prodi, Rumpun Ilmu, KBK, Dosen, Mahasiswa, Mata Kuliah, Ruangan.
5. Gunakan M4 untuk KRS dan jadwal.
6. Gunakan M5 untuk menghitung rekomendasi dosen pengampu.
7. Gunakan M1 untuk pendaftaran dan penjadwalan sidang.
8. Gunakan M2 untuk jenis surat dan pengajuan surat.
9. Gunakan M3 untuk evaluasi alert mahasiswa.
10. Gunakan M7 untuk dokumen TA dan cek kesiapan sidang.

## Arsitektur Multi-Tenant Database

SIFAK memakai model database-per-tenant:

```text
Central database
├── Super Admin
├── Daftar tenant/fakultas
├── Domain/subdomain tenant
└── Audit provisioning tenant

Tenant database: sifakueu_tenant_fasilkom
├── Admin tenant FASILKOM
├── Master data FASILKOM
├── M1-M7 FASILKOM
└── Data operasional FASILKOM

Tenant database: sifakueu_tenant_psikologi
├── Admin tenant Psikologi
├── Master data Psikologi
├── M1-M7 Psikologi
└── Data operasional Psikologi
```

Saat Super Admin membuat tenant baru, sistem akan:

1. menyimpan tenant di database central;
2. generate slug, subdomain, dan nama database;
3. membuat database tenant baru;
4. menjalankan migration ke database tenant;
5. membuat shell tenant di database tenant;
6. membuat admin tenant awal di database tenant.

Command untuk mem-provision ulang database semua tenant:

```bash
docker compose exec php php artisan sifak:tenants:provision-databases
```

Command untuk satu tenant:

```bash
docker compose exec php php artisan sifak:tenants:provision-databases --slug=psikologi
```

## Perintah Berguna

```bash
docker compose up -d --build
docker compose exec php php artisan migrate
docker compose exec php php artisan db:seed
docker compose exec php php artisan sifak:hosts
docker compose exec php php artisan sifak:tenants:provision-databases
./scripts/sifak-sync-hosts.sh
./scripts/sifak-local-setup.sh
./scripts/sifak-local-wildcard-dns.sh
docker compose exec php php artisan test
docker compose exec php php artisan optimize:clear
```

## Catatan Implementasi

- Central/Super Admin lokal memakai `sifakueu.test/admin`.
- Tenant aktif diselesaikan dari subdomain, misalnya `fasilkom.sifakueu.test`.
- Isolasi data tenant dilakukan dengan database-per-tenant dan koneksi database dinamis.
- Fitur PDF/DOCX final, WhatsApp gateway, SSO kampus, dan integrasi kalender masih disiapkan sebagai tahap lanjutan.
