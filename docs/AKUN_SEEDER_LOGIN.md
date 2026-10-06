# Akun Seeder Login SIFAK

Dokumen ini berisi akun demo hasil seeder untuk pengujian panel SIFAK lokal.

## Informasi Umum

```text
Base domain       : https://sifakueu.test
Tenant FASILKOM   : https://fasilkom.sifakueu.test
Password semua    : password
```

## Panel Login

| Panel | URL Login | Keterangan |
|---|---|---|
| Super Admin | `https://sifakueu.test/admin/login` | Database pusat/general |
| Admin Tenant | `https://fasilkom.sifakueu.test/admin/login` | Admin fakultas/prodi tenant FASILKOM |
| Mahasiswa | `https://fasilkom.sifakueu.test/mahasiswa/login` | Panel mahasiswa tenant FASILKOM |
| Dosen | `https://fasilkom.sifakueu.test/dosen/login` | Panel dosen tenant FASILKOM |
| Pimpinan | `https://fasilkom.sifakueu.test/pimpinan/login` | Panel pimpinan tenant FASILKOM |

## Akun Super Admin

| Nama | Email | Password | Role | Login |
|---|---|---|---|---|
| Super Admin SIFAK | `admin@admin.com` | `password` | `super_admin` | `https://sifakueu.test/admin/login` |

## Akun Admin Tenant

| Nama | Email | Password | Role | Login |
|---|---|---|---|---|
| Admin FASILKOM | `admin.fasilkom@sifak.local` | `password` | `admin_fakultas` | `https://fasilkom.sifakueu.test/admin/login` |
| Admin Prodi Informatika | `admin.prodi.fasilkom@sifak.local` | `password` | `admin_prodi` | `https://fasilkom.sifakueu.test/admin/login` |

## Akun Mahasiswa

| Nama | Email | Password | Role | Login |
|---|---|---|---|---|
| Mahasiswa FASILKOM | `mahasiswa.fasilkom@sifak.local` | `password` | `mahasiswa` | `https://fasilkom.sifakueu.test/mahasiswa/login` |

## Akun Dosen

Satu dosen dapat menjalankan fungsi pengajaran, pembimbing akademik, pembimbing TA, dan penguji. Karena itu akun demo dosen memakai multi-role dalam satu login.

| Nama | Email | Password | Role | Login |
|---|---|---|---|---|
| Dr. Dosen FASILKOM | `dosen.fasilkom@sifak.local` | `password` | `dosen`, `dosen_pa`, `dosen_pembimbing`, `dosen_penguji` | `https://fasilkom.sifakueu.test/dosen/login` |

## Akun Pimpinan

| Nama | Email | Password | Role | Login |
|---|---|---|---|---|
| Kaprodi Informatika | `kaprodi.fasilkom@sifak.local` | `password` | `kaprodi` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| Dekan FASILKOM | `dekan.fasilkom@sifak.local` | `password` | `dekan` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| Wakil Dekan FASILKOM | `wd.fasilkom@sifak.local` | `password` | `wd` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| Koordinator KBK Sistem Informasi | `kbk.fasilkom@sifak.local` | `password` | `kbk` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| LPM FASILKOM | `lpm.fasilkom@sifak.local` | `password` | `lpm` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| BAAK FASILKOM | `baak.fasilkom@sifak.local` | `password` | `baak` | `https://fasilkom.sifakueu.test/pimpinan/login` |
| Kepala Laboratorium Komputasi | `kalab.fasilkom@sifak.local` | `password` | `kepala_laboratorium` | `https://fasilkom.sifakueu.test/pimpinan/login` |

## Akun Portal Terbatas

| Nama | Email | Password | Role | Keterangan |
|---|---|---|---|---|
| Alumni FASILKOM | `alumni.fasilkom@sifak.local` | `password` | `alumni` | Portal alumni belum dibuat sebagai panel penuh |

## Catatan

- Super Admin memakai database pusat.
- Semua akun selain Super Admin berada di database tenant `sifakueu_tenant_fasilkom`.
- Jika browser masih membawa session tenant lama, logout terlebih dahulu atau buka private window.
- Menu ringkasan jobdesk tiap aktor tersedia di `Modul & Hak Akses` pada masing-masing panel.
