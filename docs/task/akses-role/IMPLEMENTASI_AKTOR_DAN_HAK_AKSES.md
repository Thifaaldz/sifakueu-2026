# Implementasi Aktor dan Hak Akses SIFAK

Dokumen ini adalah checklist implementasi dari `SIFAK_Aktor_dan_Hak_Akses_v1.0.md`.

## Panel

| Panel | URL Lokal | Aktor |
|---|---|---|
| Super Admin | `https://sifakueu.test/admin` | `super_admin` |
| Admin Tenant | `https://{tenant}.sifakueu.test/admin` | `admin_fakultas`, `admin_prodi` |
| Mahasiswa | `https://{tenant}.sifakueu.test/mahasiswa` | `mahasiswa` |
| Dosen | `https://{tenant}.sifakueu.test/dosen` | `dosen`, `dosen_pa`, `dosen_pembimbing`, `dosen_penguji` |
| Pimpinan | `https://{tenant}.sifakueu.test/pimpinan` | `kaprodi`, `dekan`, `wd`, `kbk`, `lpm`, `baak`, `kepala_laboratorium` |

## Role Tenant

Role yang diseed:

```text
admin_fakultas
admin_prodi
mahasiswa
dosen
dosen_pa
dosen_pembimbing
dosen_penguji
kaprodi
dekan
wd
kbk
lpm
baak
kepala_laboratorium
alumni
user
```

Role pusat:

```text
super_admin
```

## Akun Seed FASILKOM

Semua password: `password`.

| Aktor | Email | Panel |
|---|---|---|
| Super Admin | `admin@admin.com` | `https://sifakueu.test/admin` |
| Admin Fakultas | `admin.fasilkom@sifak.local` | `/admin` |
| Admin Prodi | `admin.prodi.fasilkom@sifak.local` | `/admin` |
| Mahasiswa | `mahasiswa.fasilkom@sifak.local` | `/mahasiswa` |
| Dosen Multi-Role | `dosen.fasilkom@sifak.local` | `/dosen` |
| Kaprodi | `kaprodi.fasilkom@sifak.local` | `/pimpinan` |
| Dekan | `dekan.fasilkom@sifak.local` | `/pimpinan` |
| Wakil Dekan | `wd.fasilkom@sifak.local` | `/pimpinan` |
| KBK | `kbk.fasilkom@sifak.local` | `/pimpinan` |
| LPM | `lpm.fasilkom@sifak.local` | `/pimpinan` |
| BAAK | `baak.fasilkom@sifak.local` | `/pimpinan` |
| Kepala Lab | `kalab.fasilkom@sifak.local` | `/pimpinan` |
| Alumni | `alumni.fasilkom@sifak.local` | Portal terbatas |

## Modul yang Ditampilkan

Menu `Modul & Hak Akses` tersedia pada seluruh panel:

```text
/admin/modul-hak-akses
/mahasiswa/modul-hak-akses
/dosen/modul-hak-akses
/pimpinan/modul-hak-akses
```

Isi halaman mengikuti role login dan mengambil daftar menu dari `ActorModuleRegistry`.

## Implementasi Teknis

| Kebutuhan | File |
|---|---|
| Matrix role dan permission | `app/Support/Access/AccessControl.php` |
| Daftar menu/jobdesk aktor | `app/Support/Access/ActorModuleRegistry.php` |
| Halaman tampilan modul aktor | `app/Filament/Pages/AksesRolePage.php` |
| View halaman modul aktor | `resources/views/filament/pages/akses-role.blade.php` |
| Scope data resource | `app/Support/Access/SifakResourceScope.php` |
| Role/permission seeder | `database/seeders/RoleSeeder.php` |
| Akun contoh aktor | `database/seeders/FasilkomActorSeeder.php` |
| Panel access | `app/Models/User.php` |

## Status

- Role dan permission mengikuti dokumen aktor.
- Panel access mengikuti dokumen aktor.
- Data tenant dibatasi oleh tenant context.
- Mahasiswa dan dosen memakai data scope konservatif.
- Super Admin tidak diberi permission akademik tenant seperti approve KRS atau input nilai.
- Modul besar yang belum punya tabel/service operasional detail tetap ditampilkan sebagai jobdesk pada halaman `Modul & Hak Akses`.
