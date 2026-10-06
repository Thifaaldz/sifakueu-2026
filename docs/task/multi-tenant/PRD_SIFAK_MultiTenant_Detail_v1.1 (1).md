# PRODUCT REQUIREMENTS DOCUMENT (PRD) — MULTI-TENANT SIFAK

## Informasi Dokumen

| Item | Keterangan |
|---|---|
| Nama Dokumen | PRD Multi-Tenant SIFAK |
| Versi Dokumen | 1.1 |
| Parent Product | SIFAK v2.1 |
| Status | Draft Detail |
| Tenant Awal | FASILKOM |
| Arsitektur | Domain/Subdomain Based + Database Per Tenant |
| Framework | Laravel |
| UI | Filament PHP Multi-Panel + Laravel Livewire |
| Database | MariaDB |
| Web Server | Nginx |
| Deployment | Docker |
| Local Development Domain | `sifakueu.test` + `*.sifakueu.test` |

---

# 1. Tujuan Dokumen

Dokumen ini menjabarkan secara khusus mekanisme multi-tenant pada SIFAK. Fokus dokumen adalah proses ketika Super Admin menambahkan fakultas baru, mulai dari pembuatan tenant, validasi slug, generate subdomain, pembuatan database MariaDB baru, migration, seeding, pembuatan admin tenant, sampai tenant aktif dan memperoleh seluruh Modul M1–M7.

Multi-tenant tidak mengganti atau mengubah Modul M1–M7. Multi-tenant hanya menjadi lapisan platform agar sistem yang sama dapat digunakan oleh banyak fakultas dengan data yang terisolasi.

---

# 2. Konsep Utama

SIFAK menggunakan prinsip:

```text
ONE CODEBASE
+
ONE CENTRAL PLATFORM
+
MANY TENANTS
+
ONE DATABASE PER TENANT
+
ONE SUBDOMAIN PER TENANT
```

Contoh:

```text
admin.sifak.example.id
    → Central / Super Admin

fasilkom.sifak.example.id
    → Tenant FASILKOM
    → Database: sifak_tenant_fasilkom

feb.sifak.example.id
    → Tenant FEB
    → Database: sifak_tenant_feb

fikom.sifak.example.id
    → Tenant FIKOM
    → Database: sifak_tenant_fikom
```

Setiap tenant menggunakan source code, service, dan modul yang sama, tetapi database, konfigurasi, file, dan data operasionalnya dipisahkan.

---

# 3. Sasaran Multi-Tenant

Multi-tenant SIFAK harus memungkinkan:

1. Super Admin menambahkan fakultas baru dari Central Panel.
2. Sistem menghasilkan `tenant_id` unik.
3. Sistem menghasilkan dan memvalidasi `tenant_slug`.
4. Sistem menghasilkan subdomain tenant baru.
5. Sistem membuat database MariaDB baru untuk tenant tersebut.
6. Sistem menjalankan tenant migration secara otomatis.
7. Sistem menjalankan tenant seeder secara otomatis.
8. Sistem membuat role dan permission awal.
9. Sistem membuat Admin Tenant pertama.
10. Sistem mengaktifkan seluruh Modul M1–M7.
11. Sistem membuat storage namespace tenant.
12. Sistem mengaktifkan tenant setelah health check berhasil.
13. Tenant baru tidak dapat mengakses data tenant lain.
14. Tenant dapat di-suspend dan diaktifkan kembali tanpa menghapus database.
15. Semua aktivitas provisioning dan perubahan tenant tercatat pada audit log.

---

# 4. Scope

## 4.1 In Scope

- Central Super Admin Panel.
- Tenant Registry.
- Faculty/Tenant Management.
- Tenant slug generator.
- Regex validation.
- Reserved subdomain validation.
- Subdomain uniqueness validation.
- Tenant subdomain generation.
- Wildcard subdomain routing.
- Database-per-tenant.
- Automatic tenant database creation.
- Tenant migration.
- Tenant seeding.
- Tenant database connection switching.
- Tenant storage isolation.
- Tenant-aware cache.
- Tenant-aware queue.
- Tenant-aware scheduler.
- Tenant-aware notification.
- Tenant-aware audit log.
- Initial Admin Tenant creation.
- Tenant activation, suspension, reactivation.
- Provisioning status dan retry.
- Tenant health check.
- Seluruh Modul M1–M7 tersedia pada tenant baru.

## 4.2 Out of Scope Fase Awal

- Billing/subscription tenant.
- Custom domain eksternal fakultas.
- Tenant self-registration.
- Tenant menghapus dirinya sendiri.
- Source code berbeda per tenant.
- Marketplace modul.
- Cross-tenant access data akademik rinci.

---

# 5. Aktor

## 5.1 Super Admin

Super Admin berada pada level Central Platform dan memiliki hak untuk:

- melihat semua fakultas/tenant;
- membuat tenant baru;
- melihat subdomain tenant;
- melihat nama database tenant;
- melihat status provisioning;
- retry provisioning;
- suspend tenant;
- reactivate tenant;
- melihat audit tenant;
- melihat tenant health.

## 5.2 Admin Tenant

Admin Tenant berada di dalam tenant/fakultas tertentu dan hanya memiliki akses pada tenant tersebut.

Contoh:

```text
Admin FASILKOM
→ hanya konteks tenant FASILKOM

Admin FEB
→ hanya konteks tenant FEB
```

---

# 6. Arsitektur Tingkat Tinggi

```text
                         INTERNET
                            │
                            ▼
                    Wildcard DNS
                  *.sifak.example.id
                            │
                            ▼
                         NGINX
                            │
                            ▼
                    LARAVEL SIFAK
                            │
               ┌────────────┴────────────┐
               │                         │
               ▼                         ▼
       CENTRAL CONTEXT              TENANT CONTEXT
               │                         │
               ▼                         ▼
        sifak_central              Tenant Resolver
                                         │
                      ┌──────────────────┼──────────────────┐
                      ▼                  ▼                  ▼
                   FASILKOM             FEB               FIKOM
                      │                  │                  │
                      ▼                  ▼                  ▼
          sifak_tenant_fasilkom  sifak_tenant_feb  sifak_tenant_fikom
```

---

# 7. Central Database

Central database menyimpan data level platform, bukan transaksi akademik harian tenant.

Contoh nama:

```text
sifak_central
```

## 7.1 Tabel Central

| Tabel | Fungsi |
|---|---|
| tenants | Registry seluruh tenant |
| tenant_domains | Mapping tenant dengan domain/subdomain |
| tenant_settings | Konfigurasi tenant level platform |
| tenant_provisioning_logs | Log provisioning tenant |
| tenant_status_histories | Riwayat perubahan status tenant |
| tenant_audit_logs | Audit aktivitas tenant |
| reserved_subdomains | Daftar slug yang tidak boleh digunakan |
| super_admins | Akun pengelola platform |

## 7.2 Contoh Record Tenant

```text
id              : 550e8400-e29b-41d4-a716-446655440000
faculty_name    : Fakultas Ilmu Komputer
faculty_code    : FASILKOM
slug            : fasilkom
database_name   : sifak_tenant_fasilkom
status          : ACTIVE
created_at      : 2026-09-19 10:00:00
```

---

# 8. Tenant Database

Setiap tenant memiliki database MariaDB baru sendiri.

Contoh:

```text
sifak_tenant_fasilkom
sifak_tenant_feb
sifak_tenant_fikom
```

Setiap database memiliki struktur yang sama untuk mendukung M1–M7.

Contoh struktur tenant database:

```text
users
roles
permissions
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

# 9. Form Tambah Fakultas

Super Admin membuka:

```text
Super Admin
→ Tenant Management
→ Add Faculty
```

## 9.1 Field

| Field | Wajib | Contoh |
|---|---:|---|
| Nama Fakultas | Ya | Fakultas Ekonomi dan Bisnis |
| Kode Fakultas | Ya | FEB |
| Nama Singkat | Ya | FEB |
| Tenant Slug | Auto/Edit | feb |
| Nama Admin Tenant | Ya | Admin FEB |
| Email Admin Tenant | Ya | admin.feb@example.id |
| Logo | Opsional | feb.png |
| Status Awal | Otomatis | DRAFT |

---

# 10. Generate Tenant Slug

## 10.1 Normalisasi

Contoh:

```text
Input:
FEB

Output:
feb
```

Contoh lain:

```text
Fakultas Ekonomi & Bisnis
↓
fakultas-ekonomi-bisnis
```

## 10.2 Regex

```regex
^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$
```

Valid:

```text
fasilkom
feb
fikom-2
fakultas-ekonomi
```

Tidak valid:

```text
-FEB
FEB
feb_
feb!
feb-
```

---

# 11. Reserved Subdomain

Default reserved slug:

```text
www
admin
api
app
mail
smtp
ftp
cdn
assets
static
status
support
help
super-admin
dashboard
storage
files
```

Jika Super Admin memasukkan:

```text
admin
```

maka sistem menolak:

```text
Subdomain "admin" tidak dapat digunakan karena merupakan reserved subdomain.
```

---

# 12. Cek Keunikan Subdomain

Sebelum tenant dibuat, sistem mengecek Central Database.

Pseudo flow:

```text
slug = feb
↓
domain = feb.sifak.example.id
↓
search tenant_domains
↓
if exists → reject
if not exists → continue
```

---

# 13. Generate Subdomain Baru

Format:

```text
<tenant-slug>.<base-domain>
```

Contoh:

```text
fasilkom.sifak.example.id
feb.sifak.example.id
fikom.sifak.example.id
```

Subdomain tidak membutuhkan aplikasi Laravel baru.

---

# 14. Wildcard DNS

Agar tenant baru tidak membutuhkan record DNS manual satu per satu, gunakan wildcard DNS:

```text
Type  : A
Name  : *
Value : <SERVER_PUBLIC_IP>
```

Base domain:

```text
Type  : A
Name  : @
Value : <SERVER_PUBLIC_IP>
```

Hasil:

```text
fasilkom.sifak.example.id ─┐
feb.sifak.example.id       ├──→ server SIFAK
fikom.sifak.example.id     ┘
```

---

# 15. Wildcard HTTPS

Tenant wajib menggunakan HTTPS.

Konsep certificate:

```text
*.sifak.example.id
```

Contoh URL:

```text
https://fasilkom.sifak.example.id
https://feb.sifak.example.id
```

---

# 16. Nginx Routing

Nginx menerima base domain dan subdomain tenant, lalu meneruskan semuanya ke Laravel yang sama.

Konsep:

```text
server_name .sifak.example.id;
```

Flow:

```text
Browser
↓
feb.sifak.example.id
↓
Wildcard DNS
↓
Nginx
↓
Laravel
↓
Tenant Resolver
↓
FEB
```

---

# 17. Proses Provisioning Tenant

Ketika Super Admin menekan:

```text
[ Create Tenant ]
```

sistem menjalankan:

```text
1. Validate Faculty Form
2. Normalize Tenant Slug
3. Regex Validation
4. Reserved Subdomain Validation
5. Subdomain Uniqueness Check
6. Create Tenant Record
7. Set Status = PROVISIONING
8. Generate Database Name
9. Create New MariaDB Database
10. Configure Tenant DB Connection
11. Run Tenant Migrations
12. Run Tenant Seeders
13. Create Default Roles
14. Create Default Permissions
15. Create Default Tenant Settings
16. Create First Tenant Admin
17. Enable M1–M7
18. Create Tenant Storage Namespace
19. Register Tenant Domain
20. Run Health Check
21. Set Status = ACTIVE
22. Write Provisioning Log
23. Return Tenant URL
```

---

# 18. Tenant Lifecycle

```text
DRAFT
  ↓
PROVISIONING
  ↓
ACTIVE
  ↓
SUSPENDED
  ↓
ACTIVE
```

Jika gagal:

```text
PROVISIONING
  ↓
PROVISIONING_FAILED
  ↓
RETRY
  ↓
PROVISIONING
```

| Status | Arti |
|---|---|
| DRAFT | Tenant record sudah ada tetapi provisioning belum berjalan |
| PROVISIONING | Sistem sedang menyiapkan tenant |
| ACTIVE | Tenant siap digunakan |
| PROVISIONING_FAILED | Salah satu provisioning step gagal |
| SUSPENDED | Tenant sementara tidak dapat digunakan |
| MAINTENANCE | Tenant sedang pemeliharaan |

---

# 19. Generate Database Name

Pattern:

```text
sifak_tenant_<slug>
```

Contoh:

```text
fasilkom → sifak_tenant_fasilkom
feb      → sifak_tenant_feb
fikom    → sifak_tenant_fikom
```

Database name wajib dibuat dari slug yang sudah tervalidasi, bukan dari input mentah user.

---

# 20. Create New MariaDB Database

Contoh konsep SQL:

```sql
CREATE DATABASE sifak_tenant_feb
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Sebelum:

```text
MariaDB
├── sifak_central
└── sifak_tenant_fasilkom
```

Sesudah FEB dibuat:

```text
MariaDB
├── sifak_central
├── sifak_tenant_fasilkom
└── sifak_tenant_feb
```

---

# 21. Database Connection Switching

Laravel harus mempunyai minimal dua konteks koneksi:

```text
central
```

dan:

```text
tenant
```

Flow:

```text
Request Hostname
↓
Tenant Resolver
↓
Read tenant.database_name
↓
Configure tenant DB connection
↓
Reconnect
↓
Continue request
```

Contoh:

```text
host:
feb.sifak.example.id

resolved tenant:
FEB

database:
sifak_tenant_feb
```

Setelah tenant aktif:

```text
Mahasiswa::query()
```

harus membaca data dari database FEB, bukan FASILKOM.

---

# 22. Tenant Migration

Setelah database baru dibuat, tenant migration dijalankan.

Contoh struktur:

```text
database/migrations/tenant/
```

Migration membuat tabel untuk seluruh Modul M1–M7.

Flow:

```text
Create Tenant DB
↓
Initialize Tenant Connection
↓
Run Tenant Migration
↓
Validate Migration
```

---

# 23. Tenant Seeder

Setelah migration berhasil, seeder membuat data awal.

Minimal:

```text
roles
permissions
default status
default workflow status
default document types
default alert configuration
default tenant settings
```

Role awal:

```text
mahasiswa
dosen
dosen_pa
admin_prodi
admin_fakultas
kaprodi
dekan
lpm
kbk
baak
```

---

# 24. Generate Admin Tenant

Setiap tenant wajib memiliki admin pertama.

Contoh:

```text
Tenant:
FEB

Admin:
Admin FEB

Email:
admin.feb@example.id

Role:
admin_fakultas
```

User dibuat di database tenant:

```text
sifak_tenant_feb.users
```

---

# 25. Aktivasi M1–M7

Tenant baru memperoleh seluruh modul:

```text
M1 Sidang Sempro & TA
M2 Surat Menyurat
M3 Monitoring & Alert
M4 KRS & Penjadwalan
M5 Profiling Dosen
M6 Profiling Mahasiswa
M7 Manajemen Dokumen TA
```

Default:

```text
M1 = enabled
M2 = enabled
M3 = enabled
M4 = enabled
M5 = enabled
M6 = enabled
M7 = enabled
```

Modul tidak di-copy secara fisik. Semua tenant memakai source code modul yang sama.

---

# 26. Tenant Storage

File tenant harus dipisahkan.

Local storage:

```text
storage/app/tenants/
├── fasilkom/
│   ├── sidang/
│   ├── surat/
│   ├── ta/
│   └── profile/
│
└── feb/
    ├── sidang/
    ├── surat/
    ├── ta/
    └── profile/
```

S3/MinIO:

```text
sifak/tenants/fasilkom/
sifak/tenants/feb/
```

---

# 27. Tenant Resolver

Tenant Resolver berjalan sebelum request tenant diproses.

Flow:

```text
Request
↓
Read Hostname
↓
Extract Subdomain
↓
Find Tenant in Central DB
↓
Check Tenant Status
↓
Initialize Tenant
↓
Switch Database
↓
Load Tenant Settings
↓
Continue Request
```

Jika tenant tidak ditemukan:

```text
404 — Tenant Not Found
```

Jika tenant suspended:

```text
Tenant sementara tidak aktif.
Silakan hubungi administrator.
```

---

# 28. Central Routes vs Tenant Routes

## Central

```text
admin.sifak.example.id
```

Berisi:

```text
Super Admin Login
Tenant Management
Provisioning
Audit
Health
```

## Tenant

```text
{tenant}.sifak.example.id
```

Berisi panel:

```text
/mahasiswa
/dosen
/admin
/pimpinan
```

---

# 29. Filament Multi-Panel untuk Banyak Tenant

Multi-panel digunakan untuk **memisahkan pengalaman pengguna berdasarkan tipe pengguna**, bukan untuk membuat panel baru pada setiap tenant.

Prinsip utama:

```text
Tenant
→ menentukan fakultas mana

Panel
→ menentukan tipe pengguna

Role / Permission
→ menentukan user boleh melakukan apa

Policy / Ownership
→ menentukan data mana yang boleh diakses
```

Dengan desain ini, walaupun SIFAK memiliki banyak tenant, jumlah panel aplikasi tetap.

## 29.1 Central Panel

Central Panel hanya digunakan untuk pengelolaan platform.

Contoh provider:

```text
SuperAdminPanelProvider
```

Scope:

```text
Central Database
```

Menu:

```text
Dashboard
Tenants
Domains
Provisioning
Audit Logs
Tenant Health
System Configuration
```

Central Panel **tidak dibuat ulang per fakultas**.

Contoh akses local:

```text
https://admin.sifakueu.test
```

Contoh akses production:

```text
https://admin.<domain-sifak>
```

---

## 29.2 Tenant Panels

SIFAK menggunakan empat panel tenant yang dipakai ulang oleh semua fakultas:

```text
MahasiswaPanelProvider
DosenPanelProvider
AdminPanelProvider
PimpinanPanelProvider
```

Struktur:

```text
{tenant}.sifakueu.test
├── /mahasiswa
├── /dosen
├── /admin
└── /pimpinan
```

Contoh FASILKOM:

```text
https://fasilkom.sifakueu.test/mahasiswa
https://fasilkom.sifakueu.test/dosen
https://fasilkom.sifakueu.test/admin
https://fasilkom.sifakueu.test/pimpinan
```

Contoh FEB:

```text
https://feb.sifakueu.test/mahasiswa
https://feb.sifakueu.test/dosen
https://feb.sifakueu.test/admin
https://feb.sifakueu.test/pimpinan
```

Panel yang digunakan **tetap provider yang sama**. Yang berubah hanya tenant context dan database aktif.

---

## 29.3 Prinsip Reuse Panel pada Banyak Tenant

Jika terdapat:

```text
FASILKOM
FEB
FIKES
FH
FT
FIKOM
...
100 tenant
```

sistem **tidak** membuat:

```text
FasilkomAdminPanelProvider
FebAdminPanelProvider
FikesAdminPanelProvider
...
```

Yang digunakan tetap:

```text
1 SuperAdminPanelProvider
1 MahasiswaPanelProvider
1 DosenPanelProvider
1 AdminPanelProvider
1 PimpinanPanelProvider
```

Sehingga konsepnya:

```text
1 Central Panel
+
4 Tenant Panels
+
N Tenant Context
```

Bukan:

```text
N Tenant × 4 Panel
```

---

## 29.4 Flow Panel pada Tenant

Contoh request:

```text
https://feb.sifakueu.test/admin
```

Flow:

```text
Request
↓
Hostname = feb.sifakueu.test
↓
Tenant Resolver
↓
Tenant = FEB
↓
Database = sifak_tenant_feb
↓
Load AdminPanelProvider
↓
Check Tenant Membership
↓
Check Role & Permission
↓
Check Policy / Data Ownership
↓
Render Resource / Page
```

Request lain:

```text
https://fasilkom.sifakueu.test/admin
```

menggunakan `AdminPanelProvider` yang sama, tetapi:

```text
Tenant = FASILKOM
Database = sifak_tenant_fasilkom
```

---

## 29.5 Mapping Panel dan Role

| Panel | Role yang Dapat Menggunakan | Contoh Fungsi |
|---|---|---|
| Super Admin | Super Admin Platform | Tenant, domain, provisioning, health, audit |
| Mahasiswa | Mahasiswa | KRS, sidang, surat, monitoring, profil lulusan, dokumen TA |
| Dosen | Dosen, Dosen PA, Dosen Pembimbing, Dosen Penguji | Bimbingan, approval KRS, sidang, review TA, profil dosen |
| Admin | Admin Prodi, Admin Fakultas/TU | Master data, plotting, jadwal, surat, monitoring |
| Pimpinan | Kaprodi, Dekan/WD, LPM, KBK, BAAK | Dashboard, approval, monitoring, laporan |

Satu panel dapat digunakan beberapa role. Perbedaan kemampuan user ditentukan oleh permission dan policy.

---

## 29.6 Panel Bukan Mekanisme Keamanan Utama

Panel hanya menjadi presentation layer.

Keamanan tidak boleh hanya menggunakan:

```php
if ($user->role === 'kaprodi') {
    // allow
}
```

Gunakan permission granular, misalnya:

```text
approve_krs
manage_sidang
input_nilai_sidang
review_dokumen_ta
approve_surat
view_monitoring
view_cpl
manage_dosen
view_dosen_location
manage_tenant
```

Role merupakan kumpulan permission.

Contoh:

```text
Dosen PA
├── view_mahasiswa
├── approve_krs
└── view_monitoring
```

Contoh:

```text
Kaprodi
├── view_monitoring
├── manage_sidang
├── approve_surat
├── view_cpl
└── view_dosen_profile
```

---

## 29.7 Resource dan Business Logic Tidak Diduplikasi

Jangan membuat business logic berbeda untuk setiap tenant.

Tidak disarankan:

```text
FasilkomKrsService
FebKrsService
FikesKrsService
```

Tidak disarankan pula menduplikasi resource berdasarkan tenant:

```text
FasilkomKrsResource
FebKrsResource
FikesKrsResource
```

Gunakan:

```text
KrsResource
SidangResource
SuratResource
MahasiswaResource
DosenResource
DokumenTaResource
```

Business logic ditempatkan pada service/action/domain layer:

```text
app/
├── Models/
├── Services/
├── Actions/
├── Policies/
├── Domain/
└── Modules/
```

Filament dan Livewire hanya bertindak sebagai presentation layer.

---

## 29.8 Dynamic Database Connection untuk Banyak Tenant

Sistem tidak boleh membuat konfigurasi database statis untuk setiap tenant.

Tidak disarankan:

```php
'fasilkom' => [...],
'feb' => [...],
'fikes' => [...],
```

Gunakan satu definisi tenant connection:

```php
'central' => [
    // central connection
],

'tenant' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'database' => null,
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],
```

Ketika tenant berhasil di-resolve:

```text
tenant.database_name
↓
sifak_tenant_feb
```

runtime mengubah connection:

```php
config([
    'database.connections.tenant.database' => $tenant->database_name,
]);

DB::purge('tenant');
DB::reconnect('tenant');
```

Sehingga jumlah tenant dapat bertambah tanpa menambah konfigurasi connection baru secara manual.

---

## 29.9 Branding Tenant

Panel tetap sama, tetapi tenant dapat memiliki konfigurasi visual sendiri:

```text
faculty_name
faculty_code
logo
brand_name
primary_brand_setting
template_dokumen
kode_surat
```

Contoh:

```text
Tenant FASILKOM
→ logo FASILKOM
→ template TA FASILKOM
→ kode surat FASILKOM
```

Tenant FEB:

```text
Tenant FEB
→ logo FEB
→ template TA FEB
→ kode surat FEB
```

Business logic aplikasi tetap sama.

---

# 30. Tenant Isolation

## 30.1 Database Isolation

```text
Tenant FASILKOM
→ sifak_tenant_fasilkom

Tenant FEB
→ sifak_tenant_feb
```

Tenant FASILKOM tidak boleh menggunakan connection FEB dan sebaliknya.

## 30.2 Storage Isolation

Semua path tenant harus membawa tenant identifier.

## 30.3 Cache Isolation

Gunakan prefix tenant.

Contoh:

```text
tenant:fasilkom:user:100
tenant:feb:user:100
```

## 30.4 Queue Isolation

Job wajib menyimpan tenant context.

Contoh:

```text
GenerateSidangPDFJob
  tenant_id = fasilkom
```

Worker:

```text
initialize tenant
↓
execute job
↓
end tenant context
```

## 30.5 Scheduler Isolation

```text
for each ACTIVE tenant:
    initialize tenant
    run scheduled task
    end tenant
```

## 30.6 Notification Isolation

Notification harus membawa tenant asal sehingga link dan data tidak mengarah ke tenant lain.

---

# 31. Authentication dan Authorization

Authentication dan authorization tenant menggunakan beberapa lapisan. Multi-panel tidak menggantikan RBAC.

## 31.1 Access Control Layer

Urutan validasi:

```text
Request
↓
Resolve Tenant
↓
Check Tenant Status
↓
Authenticate User
↓
Check Tenant Membership
↓
Check Panel Access
↓
Check Role
↓
Check Permission
↓
Check Policy / Ownership / Business Rule
↓
Allow Resource / Action
```

---

## 31.2 Tenant Membership

User tenant hanya dapat menggunakan tenant tempat ia terdaftar.

Contoh:

```text
user:
admin.feb@example.id

tenant:
FEB
```

Allowed:

```text
https://feb.sifakueu.test/admin
```

Tidak otomatis allowed:

```text
https://fasilkom.sifakueu.test/admin
```

Apabila user tidak memiliki membership pada tenant aktif:

```text
403 Forbidden
```

---

## 31.3 Panel Access

Panel menentukan kelompok pengalaman pengguna.

Contoh:

```text
Mahasiswa → Mahasiswa Panel
Dosen PA → Dosen Panel
Admin Prodi → Admin Panel
Kaprodi → Pimpinan Panel
```

User tidak boleh mengakses panel yang tidak sesuai dengan role/permission.

---

## 31.4 RBAC

RBAC menggunakan:

```text
Laravel Auth
+
Spatie Laravel Permission
```

Role contoh:

```text
mahasiswa
dosen
dosen_pa
dosen_pembimbing
dosen_penguji
admin_prodi
admin_fakultas
kaprodi
dekan
lpm
kbk
baak
```

Permission harus granular.

Contoh:

```text
view_krs
submit_krs
approve_krs
manage_sidang
input_nilai_sidang
review_ta
approve_ta_bab
manage_surat
approve_surat
view_monitoring
manage_dosen_profile
view_cpl
view_dosen_location
```

---

## 31.5 Policy dan Data Ownership

Permission tidak selalu cukup.

Contoh BRD:

```text
Mahasiswa boleh melihat lokasi dosen
hanya jika dosen tersebut terkait dengan bimbingan/pengujian mahasiswa.
```

Maka authorization harus menggabungkan permission dan policy.

Konsep:

```php
$user->can('view_dosen_location')
AND
student_is_related_to_dosen($user, $dosen)
```

Contoh lain:

```text
Dosen Pembimbing
→ hanya dapat review TA mahasiswa bimbingannya

Dosen Penguji
→ hanya dapat input nilai sidang yang ditugaskan kepadanya

Admin Prodi
→ hanya dapat mengelola data prodi yang menjadi scope-nya

Mahasiswa
→ hanya dapat mengubah dokumen TA miliknya sendiri
```

---

## 31.6 Tiga Dimensi Akses

Setiap request tenant dapat dipahami sebagai:

```text
TENANT
→ fakultas mana?

PANEL
→ tipe pengguna apa?

RBAC + POLICY
→ boleh melakukan apa dan pada data mana?
```

Contoh:

```text
Tenant:
FASILKOM

Panel:
Dosen

Role:
Dosen PA

Permissions:
approve_krs
view_monitoring

Policy Scope:
mahasiswa PA yang menjadi tanggung jawab dosen
```

Desain ini wajib digunakan agar jumlah tenant yang besar tidak menyebabkan duplikasi panel maupun aturan akses.

---

# 32. Provisioning Log

Setiap provisioning step wajib dicatat.

| Step | Status | Contoh |
|---|---|---|
| Validate Tenant | SUCCESS | slug valid |
| Create Tenant Record | SUCCESS | UUID created |
| Create Database | SUCCESS | sifak_tenant_feb |
| Run Migration | SUCCESS | all tables created |
| Run Seeder | SUCCESS | roles created |
| Create Admin | SUCCESS | admin tenant created |
| Setup Storage | SUCCESS | namespace created |
| Register Domain | SUCCESS | feb.sifak.example.id |
| Health Check | SUCCESS | tenant healthy |

---

# 33. Provisioning Failure

Jika step gagal:

```text
Create Database   SUCCESS
Migration         FAILED
```

status tenant:

```text
PROVISIONING_FAILED
```

Tenant tidak boleh ACTIVE.

Super Admin melihat:

```text
Tenant FEB
Status: Provisioning Failed
Failed Step: Tenant Migration
```

Action:

```text
[ Retry Provisioning ]
```

---

# 34. Retry Provisioning

Retry harus idempotent.

Tidak boleh membuat tenant baru seperti:

```text
feb
feb-2
feb-3
```

Retry harus memakai tenant record yang sama.

Flow:

```text
Existing Tenant
↓
Read Last Provisioning State
↓
Re-run Safe Step
↓
Health Check
↓
ACTIVE
```

---

# 35. Cleanup dan Rollback

Default SIFAK:

> Database hasil provisioning yang gagal tidak langsung dihapus otomatis. Database dipertahankan sampai retry atau cleanup eksplisit oleh Super Admin.

Tujuannya untuk mencegah penghapusan data yang tidak disengaja.

---

# 36. Suspend Tenant

Super Admin dapat:

```text
Tenant Management
→ Select Tenant
→ Suspend
```

Status:

```text
ACTIVE
↓
SUSPENDED
```

Database tetap ada.

User tidak dapat mengakses tenant sampai diaktifkan kembali.

---

# 37. Reactivate Tenant

```text
SUSPENDED
↓
REACTIVATE
↓
ACTIVE
```

Data lama tetap tersedia.

---

# 38. Health Check

Sebelum tenant ACTIVE:

```text
Database Connection    PASS
Migration Status       PASS
Seeder Status          PASS
Admin Account          PASS
Tenant Domain          PASS
Storage Namespace      PASS
M1 Access              PASS
M2 Access              PASS
M3 Access              PASS
M4 Access              PASS
M5 Access              PASS
M6 Access              PASS
M7 Access              PASS
```

Jika satu critical check gagal:

```text
PROVISIONING_FAILED
```

---

# 39. User Stories

## US-MT-001 — Create Tenant

**Sebagai** Super Admin, **saya ingin** menambahkan fakultas baru, **agar** fakultas tersebut mendapatkan sistem SIFAK lengkap.

Acceptance Criteria:

```text
Given Super Admin mengisi data fakultas yang valid
When Create Tenant dijalankan
Then tenant record dibuat
And database baru dibuat
And subdomain dibuat
And migration berjalan
And seeder berjalan
And admin tenant dibuat
And M1–M7 tersedia
And status menjadi ACTIVE setelah health check sukses
```

## US-MT-002 — Generate Subdomain

```text
Given slug = feb
When tenant dibuat
Then URL = feb.<base-domain>
```

## US-MT-003 — Database Per Tenant

```text
Given tenant FEB dibuat
When provisioning database selesai
Then database sifak_tenant_feb tersedia
And FEB menggunakan database tersebut
And FASILKOM tetap menggunakan database fasilkom
```

## US-MT-004 — Resolve Tenant

```text
Given URL fasilkom.<domain>
When request masuk
Then tenant FASILKOM diinisialisasi
And database FASILKOM digunakan
```

## US-MT-005 — Isolation

```text
Given user berada di tenant FASILKOM
When user membaca data
Then hanya data FASILKOM dapat diakses
And data FEB tidak dapat diakses
```

---

# 40. Functional Requirements

| ID | Requirement |
|---|---|
| MT-FR-001 | Super Admin dapat melihat daftar tenant. |
| MT-FR-002 | Super Admin dapat membuat tenant. |
| MT-FR-003 | Sistem membuat UUID tenant unik. |
| MT-FR-004 | Sistem membuat slug tenant. |
| MT-FR-005 | Sistem memvalidasi slug menggunakan regex. |
| MT-FR-006 | Sistem menolak reserved subdomain. |
| MT-FR-007 | Sistem memastikan subdomain unik. |
| MT-FR-008 | Sistem menghasilkan subdomain tenant. |
| MT-FR-009 | Sistem membuat database MariaDB baru per tenant. |
| MT-FR-010 | Sistem menjalankan tenant migration. |
| MT-FR-011 | Sistem menjalankan tenant seeder. |
| MT-FR-012 | Sistem membuat roles dan permissions default. |
| MT-FR-013 | Sistem membuat Admin Tenant pertama. |
| MT-FR-014 | Sistem mengaktifkan M1–M7. |
| MT-FR-015 | Sistem membuat tenant storage namespace. |
| MT-FR-016 | Sistem mendaftarkan domain tenant. |
| MT-FR-017 | Sistem melakukan health check. |
| MT-FR-018 | Tenant menjadi ACTIVE hanya setelah provisioning sukses. |
| MT-FR-019 | Tenant gagal harus berstatus PROVISIONING_FAILED. |
| MT-FR-020 | Super Admin dapat retry provisioning. |
| MT-FR-021 | Super Admin dapat suspend tenant. |
| MT-FR-022 | Super Admin dapat reactivate tenant. |
| MT-FR-023 | Tenant resolver membaca hostname. |
| MT-FR-024 | Tenant resolver menemukan tenant dari subdomain. |
| MT-FR-025 | Sistem mengganti DB connection sesuai tenant. |
| MT-FR-026 | Storage dipisahkan per tenant. |
| MT-FR-027 | Cache dipisahkan per tenant. |
| MT-FR-028 | Queue membawa tenant context. |
| MT-FR-029 | Scheduler berjalan tenant-aware. |
| MT-FR-030 | Notification berjalan tenant-aware. |
| MT-FR-031 | Audit log menyimpan tenant context. |
| MT-FR-032 | Invalid subdomain menampilkan Tenant Not Found. |
| MT-FR-033 | Suspended tenant tidak dapat digunakan. |
| MT-FR-034 | Tenant baru tidak membuat codebase baru. |
| MT-FR-035 | Seluruh tenant menggunakan aplikasi inti yang sama. |
| MT-FR-036 | Seluruh tenant menggunakan empat Tenant Panel yang sama: Mahasiswa, Dosen, Admin, dan Pimpinan. |
| MT-FR-037 | Sistem tidak membuat panel baru ketika tenant baru dibuat. |
| MT-FR-038 | Sistem menentukan tenant sebelum Tenant Panel dimuat. |
| MT-FR-039 | Sistem memvalidasi tenant membership sebelum memberikan akses panel. |
| MT-FR-040 | Akses action/resource dikontrol dengan permission dan policy, bukan hanya nama panel. |
| MT-FR-041 | Sistem menggunakan dynamic tenant database connection, bukan connection statis per tenant. |
| MT-FR-042 | Resource dan service inti dapat digunakan ulang oleh seluruh tenant. |
| MT-FR-043 | Tenant dapat memiliki branding/configuration sendiri tanpa membuat codebase atau panel baru. |

---

# 41. Business Rules

| ID | Aturan |
|---|---|
| MT-BR-001 | Hanya Super Admin yang dapat membuat tenant baru. |
| MT-BR-002 | Setiap tenant harus memiliki UUID unik. |
| MT-BR-003 | Setiap tenant harus memiliki slug unik. |
| MT-BR-004 | Slug wajib lolos regex. |
| MT-BR-005 | Reserved subdomain tidak boleh digunakan tenant. |
| MT-BR-006 | Setiap tenant harus memiliki database sendiri. |
| MT-BR-007 | Database tenant tidak boleh digunakan tenant lain. |
| MT-BR-008 | Tenant baru mendapatkan M1–M7. |
| MT-BR-009 | Tenant hanya ACTIVE setelah provisioning sukses. |
| MT-BR-010 | Suspend tenant tidak menghapus database. |
| MT-BR-011 | Invalid domain tidak boleh fallback ke tenant lain. |
| MT-BR-012 | Semua tenant administration actions harus diaudit. |
| MT-BR-013 | Queue, cache, scheduler, storage, dan notification wajib tenant-aware. |
| MT-BR-014 | FASILKOM menjadi tenant awal/pilot. |
| MT-BR-015 | Panel tidak boleh dibuat secara khusus per tenant. |
| MT-BR-016 | Tenant Panel harus reusable untuk seluruh tenant. |
| MT-BR-017 | Tenant context wajib berhasil diinisialisasi sebelum query operasional tenant dilakukan. |
| MT-BR-018 | Role tidak boleh digunakan sebagai satu-satunya pemeriksaan keamanan; permission dan policy wajib diterapkan sesuai kebutuhan. |
| MT-BR-019 | User tidak boleh berpindah tenant hanya dengan memanipulasi URL, tenant ID, atau request payload. |
| MT-BR-020 | Database connection tenant harus ditentukan secara dinamis dari tenant yang sudah tervalidasi. |

---

# 42. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| Security | Data antar-tenant harus terisolasi. |
| Resolution | Tenant resolver tidak boleh fallback ke tenant lain. |
| Performance | Resolusi tenant target < 100 ms pada kondisi normal. |
| Availability | Kegagalan satu tenant tidak boleh membuka data tenant lain. |
| Scalability | Penambahan tenant tidak memerlukan codebase baru. |
| Maintainability | Migration tenant harus versioned. |
| Auditability | Tenant ID, user, action, timestamp tercatat. |
| Storage | File dipisahkan per tenant. |
| Queue | Job menyimpan tenant context. |
| Scheduler | Scheduled task berjalan per tenant aktif. |
| HTTPS | Semua subdomain tenant menggunakan HTTPS. |
| Panel Scalability | Penambahan tenant tidak menambah jumlah PanelProvider. |
| RBAC | Permission bersifat granular dan dapat digunakan lintas tenant dengan scope tenant aktif. |
| Policy | Data ownership/business rule tetap diperiksa setelah permission. |
| DB Configuration | Tidak ada static DB connection per tenant di source code. |
| Reusability | Resource, Livewire component, service, dan module harus dapat digunakan ulang seluruh tenant. |

---

# 43. QA Scenario

## Tenant Creation

```text
TC-MT-001 — Valid tenant
Expected: ACTIVE

TC-MT-002 — Duplicate slug
Expected: validation error

TC-MT-003 — Reserved slug
Expected: validation error

TC-MT-004 — Invalid regex
Expected: validation error

TC-MT-005 — Database creation failure
Expected: PROVISIONING_FAILED

TC-MT-006 — Migration failure
Expected: PROVISIONING_FAILED

TC-MT-007 — Seeder failure
Expected: PROVISIONING_FAILED

TC-MT-008 — Retry provisioning
Expected: reuse same tenant record
```

## Isolation

```text
TC-MT-020 — Data FASILKOM tidak tampil pada FEB
TC-MT-021 — File FASILKOM tidak dapat diakses FEB
TC-MT-022 — Queue FASILKOM berjalan pada DB FASILKOM
TC-MT-023 — Cache key tenant tidak collision
TC-MT-024 — Notification URL mengarah ke subdomain tenant yang benar
```

## Routing

```text
TC-MT-030 — fasilkom.<domain> resolve FASILKOM
TC-MT-031 — feb.<domain> resolve FEB
TC-MT-032 — unknown.<domain> → Tenant Not Found
TC-MT-033 — suspended tenant → access denied
```

---

# 44. Security Test Scenario

## ST-MT-001 — Cross-Tenant ID Manipulation

Manipulasi tenant ID pada request tidak boleh memindahkan konteks tenant.

## ST-MT-002 — Direct Database Access via Endpoint

Endpoint tenant tidak boleh dapat membaca database tenant lain.

## ST-MT-003 — Invalid Host Header

Host tidak terdaftar tidak boleh dimapping ke tenant default.

## ST-MT-004 — Cross-Tenant File Access

URL file tenant A tidak boleh memberikan file tenant B.

## ST-MT-005 — Queue Context Leak

Job dari tenant A tidak boleh dieksekusi menggunakan tenant B.

---

# 45. Provisioning Acceptance Checklist

Tenant baru boleh ACTIVE jika:

- [ ] tenant record dibuat;
- [ ] tenant UUID unik;
- [ ] slug valid;
- [ ] slug bukan reserved word;
- [ ] subdomain unik;
- [ ] database tenant berhasil dibuat;
- [ ] DB connection berhasil;
- [ ] migration berhasil;
- [ ] seeder berhasil;
- [ ] roles tersedia;
- [ ] permissions tersedia;
- [ ] Admin Tenant tersedia;
- [ ] M1 tersedia;
- [ ] M2 tersedia;
- [ ] M3 tersedia;
- [ ] M4 tersedia;
- [ ] M5 tersedia;
- [ ] M6 tersedia;
- [ ] M7 tersedia;
- [ ] storage namespace tersedia;
- [ ] tenant domain terdaftar;
- [ ] tenant resolver berhasil;
- [ ] health check berhasil;
- [ ] provisioning log tersimpan;
- [ ] audit log tersimpan.

---

# 46. Contoh End-to-End — Tenant FEB

## Step 1 — Kondisi Awal

```text
Central DB:
sifak_central

Tenant DB:
sifak_tenant_fasilkom
```

Domain:

```text
admin.sifak.example.id
fasilkom.sifak.example.id
```

## Step 2 — Super Admin Tambah FEB

```text
Nama Fakultas : Fakultas Ekonomi dan Bisnis
Kode          : FEB
Slug          : feb
Admin         : admin.feb@example.id
```

## Step 3 — Validation

```text
Regex       PASS
Reserved    PASS
Unique      PASS
```

## Step 4 — Central Record

```text
tenant_id      = UUID-FEB
slug           = feb
database_name  = sifak_tenant_feb
status         = PROVISIONING
```

## Step 5 — Create Database

```text
CREATE DATABASE sifak_tenant_feb;
```

## Step 6 — Migration

Semua tabel M1–M7 dibuat pada database FEB.

## Step 7 — Seeder

Roles, permissions, dan konfigurasi default dibuat.

## Step 8 — Admin Tenant

```text
admin.feb@example.id
```

dibuat pada database FEB.

## Step 9 — Subdomain

```text
feb.sifak.example.id
```

## Step 10 — Health Check

```text
DB          PASS
Migration   PASS
Seeder      PASS
Admin       PASS
Storage     PASS
Domain      PASS
M1–M7       PASS
```

## Step 11 — Activate

```text
status = ACTIVE
```

Result:

```text
FEB memperoleh:
- tenant baru;
- database baru;
- subdomain baru;
- seluruh sistem SIFAK M1–M7.
```

---

# 46.1 Implementasi Local Development untuk Banyak Tenant

Pada local development, SIFAK menggunakan base domain:

```text
sifakueu.test
```

Central:

```text
admin.sifakueu.test
```

Tenant:

```text
fasilkom.sifakueu.test
feb.sifakueu.test
fikes.sifakueu.test
```

Local environment harus mendukung wildcard:

```text
*.sifakueu.test
→ 127.0.0.1
```

Dengan wildcard local DNS, proses `Create Tenant` tidak perlu mengubah `/etc/hosts` setiap kali tenant baru dibuat.

Flow:

```text
Super Admin membuat tenant FEB
↓
slug = feb
↓
domain record pada Central DB = feb.sifakueu.test
↓
database = sifak_tenant_feb
↓
wildcard local DNS sudah mengenali *.sifakueu.test
↓
FEB langsung dapat diakses dari browser
```

Nginx local harus menerima:

```text
sifakueu.test
*.sifakueu.test
```

SSL local dapat menggunakan wildcard certificate dari `mkcert`:

```text
sifakueu.test
*.sifakueu.test
```

Contoh hasil akhir:

```text
https://admin.sifakueu.test
https://fasilkom.sifakueu.test
https://feb.sifakueu.test
```

---

# 47. Tech Stack Multi-Tenant

| Layer | Teknologi |
|---|---|
| Framework | Laravel |
| Tenant UI | Filament PHP Multi-Panel + Livewire |
| Central UI | Filament Super Admin Panel |
| Database | MariaDB |
| Tenant Model | Database Per Tenant |
| Resolution | Subdomain Based |
| Auth | Laravel Auth |
| RBAC | Spatie Laravel Permission |
| Queue | Laravel Queue |
| Scheduler | Laravel Scheduler |
| Cache | Redis (opsional) |
| Storage | Laravel Storage / S3 / MinIO |
| Web Server | Nginx |
| DNS | Wildcard Subdomain |
| TLS | Wildcard SSL / HTTPS |
| Deployment | Docker |

Catatan teknis: implementasi Laravel dapat menggunakan package multi-tenancy yang mendukung domain-based dan database-based tenancy, misalnya `stancl/tenancy`, atau implementasi internal selama seluruh requirement dokumen ini terpenuhi.

---

# 48. Environment Concept

```env
APP_URL=https://sifak.example.id
CENTRAL_DOMAIN=sifak.example.id
TENANCY_BASE_DOMAIN=sifak.example.id
SESSION_DOMAIN=.sifak.example.id

DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=sifak_central
```

Credential production tidak boleh ditaruh di repository publik.

---

# 49. Database Permission

Service provisioning harus memiliki izin yang cukup untuk membuat database tenant dan menjalankan migration. Untuk production, gunakan prinsip least privilege.

Jika memungkinkan, pisahkan:

```text
Application DB Credential
```

dengan:

```text
Provisioning DB Credential
```

agar credential aplikasi harian tidak memiliki hak database yang terlalu luas.

---

# 50. Tenant Migration Strategy

Ketika ada schema baru:

```text
Deploy Application
↓
Run Central Migration
↓
Run Tenant Migration
↓
Validate Tenant Schema
```

Setiap tenant sebaiknya memiliki informasi schema version untuk mempermudah monitoring kompatibilitas.

---

# 51. Backup dan Restore

Backup mendukung per tenant.

Contoh:

```text
backup/
├── fasilkom/
│   ├── database.sql
│   └── storage/
└── feb/
    ├── database.sql
    └── storage/
```

Restore FEB tidak boleh memengaruhi FASILKOM.

---

# 52. Logging

Tenant log minimal membawa:

```text
tenant_id
request_id
user_id
action
timestamp
status
error
```

Contoh:

```text
[tenant=fasilkom][user=123][action=KRS_APPROVED]
```

Central:

```text
[central][super_admin=10][action=TENANT_CREATED][tenant=feb]
```

---

# 53. Monitoring Tenant

Super Admin dapat melihat:

```text
Tenant Status
Database Reachability
Migration Status
Queue Status
Last Error
Last Provisioning
Last Activity
```

Dashboard:

```text
Total Tenant
Active Tenant
Suspended Tenant
Failed Provisioning
Tenant Created This Month
```

---

# 54. Definition of Done

Multi-tenant dianggap selesai apabila:

1. Central Super Admin dapat login.
2. FASILKOM berjalan sebagai tenant awal.
3. Super Admin dapat membuat tenant baru.
4. Tenant slug tervalidasi.
5. Subdomain tenant berhasil dibentuk.
6. Database MariaDB baru dibuat otomatis.
7. Tenant migration berjalan otomatis.
8. Tenant seeder berjalan otomatis.
9. Admin tenant dibuat otomatis.
10. Seluruh M1–M7 tersedia pada tenant baru.
11. Tenant resolver bekerja berdasarkan subdomain.
12. Database switching bekerja sesuai tenant.
13. Storage terisolasi.
14. Cache tenant-aware.
15. Queue tenant-aware.
16. Scheduler tenant-aware.
17. Notification tenant-aware.
18. Audit log tersedia.
19. Provisioning failure dapat di-retry.
20. Tenant dapat di-suspend.
21. Tenant dapat diaktifkan kembali.
22. Tenant A tidak dapat mengakses Tenant B.
23. Invalid subdomain tidak memuat tenant lain.
24. Seluruh critical QA scenario lulus.

---

# 55. Ringkasan Alur

```text
SUPER ADMIN
    │
    ▼
ADD FACULTY
    │
    ▼
GENERATE SLUG
    │
    ▼
REGEX + RESERVED + UNIQUE VALIDATION
    │
    ▼
CREATE TENANT RECORD
    │
    ▼
GENERATE SUBDOMAIN
    │
    ▼
GENERATE DATABASE NAME
    │
    ▼
CREATE NEW MARIADB DATABASE
    │
    ▼
RUN TENANT MIGRATION
    │
    ▼
RUN TENANT SEEDER
    │
    ▼
CREATE ADMIN TENANT
    │
    ▼
ENABLE M1–M7
    │
    ▼
CREATE TENANT STORAGE
    │
    ▼
HEALTH CHECK
    │
    ▼
ACTIVE
```

Hasil akhir:

```text
NEW FACULTY
+
NEW TENANT
+
NEW DATABASE
+
NEW SUBDOMAIN
+
FULL SIFAK M1–M7
```

---

# 56. Referensi Implementasi

Dokumen ini menggunakan pendekatan Laravel multi-tenancy berbasis domain/subdomain dan database-per-tenant sebagai referensi implementasi. Prinsip utamanya adalah satu Laravel codebase melayani banyak tenant, setiap tenant memiliki database terisolasi dan subdomain/domain unik. Infrastruktur menggunakan wildcard DNS agar subdomain baru dapat diarahkan ke server yang sama, kemudian Laravel menentukan tenant berdasarkan hostname dan mengaktifkan database tenant yang sesuai.

---

# 57. Kesimpulan

Multi-tenant SIFAK tidak membuat aplikasi baru untuk setiap fakultas. Ketika fakultas baru ditambahkan oleh Super Admin, sistem akan:

```text
1. Membuat tenant record baru
2. Membuat dan memvalidasi slug
3. Menghasilkan subdomain baru
4. Membuat database MariaDB baru
5. Menjalankan migration tenant
6. Menjalankan seeder tenant
7. Membuat Admin Tenant
8. Mengaktifkan M1–M7
9. Menyiapkan storage tenant
10. Melakukan health check
11. Mengaktifkan tenant
```

Dengan desain ini, FASILKOM tetap menjadi tenant awal/pilot dan fakultas lain dapat ditambahkan tanpa menduplikasi source code, sementara data setiap fakultas tetap terpisah.

---

**SIFAK — PRD Multi-Tenant Detail v1.1**  
**Parent Product: SIFAK v2.1**
