# Manual Guide PDF SIFAK

## Versi 2 (9 Oktober 2026) — Manual langkah demi langkah + QA

Folder `v2/pdf/` berisi hasil uji end-to-end otomatis lewat browser dengan screenshot di setiap langkah:

| File | Isi |
|---|---|
| `v2/pdf/SIFAK_Manual_Guide_QA_M1-M7.pdf` | Gabungan: cara menjalankan, ringkasan QA, M1–M7, fitur per role |
| `v2/pdf/SIFAK_M1_Sidang_Sempro_TA.pdf` … `SIFAK_M7_Dokumen_TA_Repositori.pdf` | Manual per modul (terpisah) |
| `v2/pdf/SIFAK_Fitur_Per_Role.pdf` | Menu & aksi tiap role (diambil dari aplikasi) |
| `v2/pdf/SIFAK_Laporan_QA.pdf` | Ringkasan QA, bug yang diperbaiki, catatan terbuka |

Regenerate (aplikasi harus berjalan):

```bash
docker compose exec php php artisan db:seed --class=QaDemoSeeder   # akun data uji Rina & Budi
node scripts/qa/crawl-roles.cjs                                     # cek semua menu per role
node scripts/qa/m1.cjs   # ... sampai m7.cjs, alur modul + screenshot
node scripts/qa/build-pdf.cjs                                       # susun PDF
```

## Versi 1

Tanggal generate: 9 Oktober 2026

Folder ini berisi manual guide PDF terpisah untuk modul SIFAK. Setiap PDF memuat tujuan modul, panel/aktor yang memakai modul, alur proses, contoh 1 case, output yang diharapkan, dan screenshot contoh halaman.

## Daftar PDF

| Modul | PDF |
|---|---|
| Platform Multi-Tenant | `pdf/00-platform-multitenant.pdf` |
| Master Data | `pdf/00-master-data.pdf` |
| Shared Services | `pdf/00-shared-services.pdf` |
| M1 Sidang Sempro dan TA | `pdf/m1-sidang-sempro-ta.pdf` |
| M2 Surat Menyurat | `pdf/m2-surat-menyurat.pdf` |
| M3 Monitoring dan Alert | `pdf/m3-monitoring-alert.pdf` |
| M4 KRS dan Penjadwalan | `pdf/m4-krs-penjadwalan.pdf` |
| M5 Profiling Dosen | `pdf/m5-profiling-dosen.pdf` |
| M6 Profiling Mahasiswa | `pdf/m6-profiling-mahasiswa.pdf` |
| M7 Dokumen TA dan Repository | `pdf/m7-dokumen-ta-repository.pdf` |

## Screenshot Pendukung

Screenshot yang dipakai di PDF tersedia di folder `screenshots/`.

## Regenerate

Jika aplikasi lokal sedang berjalan, generator dapat mengambil screenshot langsung dari halaman modul:

```bash
node scripts/generate-manual-guides.cjs
```

Jika aplikasi lokal sedang tidak bisa diakses, generator dapat memakai screenshot QA sebagai fallback:

```bash
node scripts/generate-manual-guides.cjs --offline
```

