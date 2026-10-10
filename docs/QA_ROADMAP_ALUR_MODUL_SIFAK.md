# QA Roadmap dan Alur Modul SIFAK

Tanggal QA: 9 Oktober 2026  
Environment: Docker local, Laravel 12.8.1, PHP 8.3, MariaDB 10.11, Filament 3.3.10.

## Ringkasan QA

Status umum: PASS untuk boot aplikasi, migrasi, seed, route registration, test suite, build frontend, login panel, dan dashboard smoke test.

Catatan penting:

- Docker berjalan dengan `DB_FORWARD_PORT=23306` karena port host `13306` sedang dipakai service lain.
- Asset multi-tenant disesuaikan agar mengikuti domain aktif. `ASSET_URL` dibuat kosong supaya Filament tidak memuat asset tenant dari domain pusat.
- Parse error pada halaman verifikasi surat sudah diperbaiki di controller verifikasi surat.
- QA browser mencakup login dan dashboard utama setiap panel, bukan exhaustive klik semua create/edit/delete untuk seluruh 866 route.

## URL Panel Lokal

| Panel | URL Login | Database |
|---|---|---|
| Super Admin | `https://sifakueu.test/admin/login` | Central/general |
| Admin Tenant | `https://fasilkom.sifakueu.test/admin/login` | Tenant FASILKOM |
| Dosen | `https://fasilkom.sifakueu.test/dosen/login` | Tenant FASILKOM |
| Mahasiswa | `https://fasilkom.sifakueu.test/mahasiswa/login` | Tenant FASILKOM |
| Pimpinan | `https://fasilkom.sifakueu.test/pimpinan/login` | Tenant FASILKOM |

## Bukti Screenshot

| Area | File |
|---|---|
| Login Super Admin | `docs/screenshots/qa/01-superadmin-login.png` |
| Login Admin Tenant | `docs/screenshots/qa/02-admin-tenant-login.png` |
| Login Dosen | `docs/screenshots/qa/03-dosen-login.png` |
| Login Mahasiswa | `docs/screenshots/qa/04-mahasiswa-login.png` |
| Login Pimpinan | `docs/screenshots/qa/05-pimpinan-login.png` |
| Dashboard Super Admin | `docs/screenshots/qa/06-superadmin-dashboard.png` |
| Dashboard Admin Tenant | `docs/screenshots/qa/07-admin-tenant-dashboard.png` |
| Dashboard Dosen | `docs/screenshots/qa/08-dosen-dashboard.png` |
| Dashboard Mahasiswa | `docs/screenshots/qa/09-mahasiswa-dashboard.png` |
| Dashboard Pimpinan | `docs/screenshots/qa/10-pimpinan-dashboard.png` |

## Hasil Verifikasi Teknis

| Pemeriksaan | Hasil |
|---|---|
| `docker compose ps` | PASS: `db`, `nginx`, dan `php` running |
| `php artisan migrate:status` | PASS: semua migration berstatus `Ran` |
| `php artisan db:seed --force` | PASS: tenant FASILKOM, role, aktor, dan data akademik seed berhasil |
| Tenant data check | PASS: tenant FASILKOM memiliki 12 user, 2 mahasiswa, 7 dosen |
| `php artisan route:list --except-vendor` | PASS: 866 route terdaftar |
| `php artisan test` | PASS: 21 test, 29 assertion |
| `npm run build` | PASS: Vite build berhasil |
| Browser login smoke test | PASS: 5 panel berhasil login ke dashboard |
| Browser console smoke test | PASS: tidak ada error console pada dashboard panel |
| Browser resource smoke test | PASS: halaman utama modul representatif status 200 untuk semua panel |

Resource smoke test yang dibuka:

- Super Admin: users, tenants, modul hak akses.
- Admin Tenant: master fakultas, KRS, surat, monitoring alert, profil dosen, profil mahasiswa, sidang, dokumen TA, repository.
- Dosen: KRS, surat, monitoring alert, profil dosen, profil mahasiswa, sidang, dokumen TA, repository.
- Mahasiswa: KRS, surat, monitoring alert, profil mahasiswa, sidang, dokumen TA, repository.
- Pimpinan: KRS, surat, monitoring alert, profil dosen, profil mahasiswa, sidang, dokumen TA, repository.

## Alur Multi-Tenant

1. Super Admin login ke panel pusat `sifakueu.test`.
2. Super Admin membuat tenant/fakultas.
3. Sistem menyediakan metadata tenant di database pusat.
4. Tenant memakai domain/subdomain seperti `fasilkom.sifakueu.test`.
5. Aplikasi resolve tenant dari subdomain.
6. Data operasional admin, dosen, mahasiswa, dan pimpinan tenant berjalan di database tenant.
7. Super Admin tetap berada di database pusat dan tidak bekerja pada data operasional fakultas secara langsung.

## Alur Aktor

| Aktor | Fokus Akses |
|---|---|
| Super Admin | Kelola tenant/fakultas, user platform, modul hak akses, dan konfigurasi platform pusat |
| Admin Tenant | Kelola master data, user tenant, seluruh modul akademik, workflow, monitoring, surat, sidang, profiling, dan repository |
| Dosen | Mengajar, dosen PA, pembimbing, penguji, validasi/monitoring mahasiswa, surat, sidang, profil dosen, dan dokumen TA sesuai role dosen gabungan |
| Mahasiswa | KRS, jadwal, surat, pendaftaran sidang, profiling mahasiswa, tugas akhir, dokumen TA, dan repository |
| Pimpinan | Monitoring risiko akademik, laporan, approval/review, evaluasi kinerja modul, dan oversight data tenant |

## Alur Modul

### Platform dan Multi-Tenant

Input utama: data tenant/fakultas, domain, database tenant, admin awal.  
Alur: super admin membuat tenant -> sistem resolve subdomain -> database tenant aktif -> seeder tenant menyiapkan aktor dan dataset awal.  
Output: tenant siap pakai dengan panel admin, dosen, mahasiswa, dan pimpinan.

### Master Data

Input utama: fakultas, program studi, tahun akademik, semester, kurikulum, mata kuliah, dosen, mahasiswa, ruangan, CPL/PLO, KBK, keahlian.  
Alur: admin tenant melengkapi master data -> data dipakai oleh KRS, sidang, monitoring, profiling, surat, dan repository.  
Output: data dasar akademik tenant yang konsisten.

### Shared Services

Input utama: approval flow, approval step, template notifikasi, file storage, audit log, scheduled task log.  
Alur: modul membuat request/aktivitas -> shared service menyimpan workflow, audit, notifikasi, dan file -> modul membaca status terbaru.  
Output: jejak proses, approval, notifikasi, dan penyimpanan file yang terpusat per tenant.

### M1 Sidang Sempro dan TA

Input utama: jenis sidang, requirement, pendaftaran, jadwal, assignment penguji/pembimbing, rubric, score, revisi, berita acara.  
Alur: mahasiswa daftar sidang -> admin/dosen verifikasi syarat -> jadwal dan penguji ditetapkan -> dosen memberi penilaian -> hasil, revisi, dan minutes tercatat.  
Output: status sidang, nilai, revisi, dan dokumen berita acara.

### M2 Surat Menyurat

Input utama: jenis surat, template, field form, request surat, approval, nomor surat, file generated, distribusi, arsip, token verifikasi.  
Alur: mahasiswa/dosen mengajukan surat -> sistem validasi field -> approval berjalan -> nomor surat dan dokumen dibuat -> surat didistribusikan dan dapat diverifikasi.  
Output: dokumen surat, arsip surat, dan halaman verifikasi publik.

### M3 Monitoring dan Alert

Input utama: monitoring rule, snapshot, indicator result, alert, follow-up, escalation, override.  
Alur: data akademik dievaluasi -> rule menghasilkan indikator risiko -> alert dibuat -> admin/dosen/pimpinan melakukan follow-up atau escalation.  
Output: dashboard risiko, daftar alert aktif, follow-up, dan histori eskalasi.

### M4 KRS dan Penjadwalan

Input utama: periode KRS, penawaran mata kuliah, kelas kuliah, KRS, detail KRS, validasi KRS, plotting dosen, jadwal kuliah, conflict, history.  
Alur: admin membuka periode dan penawaran -> mahasiswa mengisi KRS -> dosen PA/admin validasi -> jadwal dan plotting dosen ditetapkan -> konflik jadwal dicatat.  
Output: KRS tervalidasi, jadwal kuliah, dan histori perubahan jadwal.

### M5 Profiling Dosen

Input utama: profil dosen, pendidikan, sertifikasi, publikasi, pengalaman industri, preferensi MK, lokasi, beban dosen, kompetensi.  
Alur: dosen/admin melengkapi profil -> sistem membaca kompetensi dan histori -> rekomendasi pengampu dan gap kompetensi tersedia.  
Output: profil dosen lengkap, rekomendasi pengampu, dan analisis gap.

### M6 Profiling Mahasiswa

Input utama: profil mahasiswa, minat, portofolio, sertifikasi, organisasi, MBKM/magang, skor CPL/PLO, skor profil lulusan, rekomendasi.  
Alur: mahasiswa/admin melengkapi data -> sistem menghitung capaian dan gap -> rekomendasi akademik/karier tersimpan.  
Output: profil mahasiswa, pemetaan capaian, gap kompetensi, dan rekomendasi.

### M7 Dokumen TA dan Repository

Input utama: tugas akhir, dokumen TA, section, version, review, comment, approval, progress log, repository item, revision cycle.  
Alur: mahasiswa mengunggah dokumen -> dosen memberi review/komentar -> approval dan revisi berjalan -> dokumen final masuk repository.  
Output: histori versi dokumen, review, approval, progress, dan repository final.

## Roadmap QA Lanjutan

1. Tambahkan E2E create-edit-delete untuk resource prioritas setiap modul.
2. Tambahkan test workflow lengkap M1 dari pendaftaran sampai hasil sidang.
3. Tambahkan test workflow lengkap M2 dari request sampai verifikasi surat publik.
4. Tambahkan test rule M3 untuk memastikan alert muncul otomatis dari data akademik tertentu.
5. Tambahkan test KRS M4 untuk validasi bentrok jadwal, limit SKS, dan approval dosen PA.
6. Tambahkan test rekomendasi M5/M6 berdasarkan dataset kompetensi.
7. Tambahkan test upload, review, approval, dan publish repository M7.
8. Uji integrasi produksi untuk email, storage eksternal, PDF signing, dan backup database bila kredensial produksi sudah tersedia.

## Perintah QA yang Dipakai

```bash
DB_FORWARD_PORT=23306 docker compose up -d
docker compose ps
docker compose exec -T php php artisan migrate:status
docker compose exec -T php php artisan db:seed --force
docker compose exec -T php php artisan route:list --except-vendor
docker compose exec -T php php artisan test
docker compose exec -T php npm run build
```
