// M7 Dokumen TA & Repositori — upload versi -> submit -> review/revisi -> approve -> progress -> checklist -> finalisasi -> repository -> publish.
const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const { run } = require('./flow.cjs');
const { root } = require('./lib.cjs');

const meta = {
  code: 'M7',
  title: 'Manajemen Dokumen TA & Repositori Digital',
  purpose: 'Mengelola dokumen tugas akhir per bab dengan versioning, review dan permintaan revisi dari pembimbing, approval bab, perhitungan progres, checklist finalisasi, kompilasi dokumen final, hingga publikasi ke repositori digital dengan metadata dan hak akses.',
  actors: ['Mahasiswa', 'Dosen Pembimbing', 'Admin Fakultas / TU', 'BAAK'],
  testCase: 'Budi Demo QA (NIM 2026001003) mengunggah Bab 1 versi 1, pembimbing Dr. Dosen FASILKOM meminta revisi, Budi mengunggah versi 2 dan disetujui. Bab lain disetujui dengan langkah yang sama, lalu Admin Fakultas memfinalisasi TA dan mempublikasikannya ke repositori.',
};

const tmp = path.join(root, 'docs', 'manual-guides', 'v2', 'tmp');
fs.mkdirSync(tmp, { recursive: true });
const tinker = (file) => execSync(`docker compose exec -T php php artisan tinker --execute='require "storage/app/${file}";'`, { cwd: root }).toString();

async function makePdf(browser, name, text) {
  const p = await browser.newPage();
  await p.setContent(`<h1 style="font-family:sans-serif">${text}</h1><p style="font-family:sans-serif">Dokumen contoh QA SIFAK.</p>`);
  const file = path.join(tmp, name);
  await p.pdf({ path: file, format: 'A4' });
  await p.close();
  return file;
}

run('m7', meta, async (f) => {
  console.log(tinker('qa_reset_m7.php').trim());
  const v1 = await makePdf(f.browser, 'bab1_v1.pdf', 'BAB 1 PENDAHULUAN (versi 1)');
  const v2 = await makePdf(f.browser, 'bab1_v2.pdf', 'BAB 1 PENDAHULUAN (versi 2 - revisi)');

  const upload = async (file, summary, title, desc) => {
    const btn = f.row('Bab 1').locator('.fi-ta-actions button', { hasText: 'Upload Versi' });
    await f.shot(title, desc, { highlight: btn });
    await btn.click();
    await f.page.waitForTimeout(1200);
    const dlg = f.page.locator('.fi-modal-window:visible').last();
    await dlg.locator('input[type=file]').setInputFiles(file);
    await f.page.waitForTimeout(3000);
    await dlg.locator('textarea').first().fill(summary);
    const ok = dlg.locator('.fi-modal-footer-actions button').filter({ hasNotText: /cancel/i }).first();
    await f.shot(`${title} — pilih file & isi ringkasan`, 'Format PDF/DOC/DOCX, maksimal 10 MB. Isi ringkasan perubahan lalu Submit.', { highlight: ok });
    await ok.click();
    await f.page.waitForTimeout(2500);
    await f.checkErrors();
  };

  // ---- Mahasiswa: struktur & upload v1 ----
  await f.as('mahasiswa_demo2');
  await f.clickMenu('Tugas Akhirs', 'Buka M7 Dokumen TA > Tugas Akhirs', 'Metadata TA: judul, pembimbing, status.');
  await f.shot('Data tugas akhir', 'Judul, pembimbing, dan status TA mahasiswa.');
  await f.clickMenu('Ta Documents', 'Buka Ta Documents', 'Dokumen TA per bab (Halaman Awal, Bab 1–5, Daftar Pustaka, Lampiran).');
  await f.shot('Struktur dokumen TA', 'Setiap bab memiliki status dan versi sendiri.', { fullPage: true });
  await upload(v1, 'Draft awal latar belakang dan rumusan masalah.', 'Klik Upload Versi pada Bab 1', 'Unggah file bab.');
  await f.rowAction('Bab 1', 'Submit', 'Klik Submit pada Bab 1', 'Bab dikirim ke pembimbing untuk direview; pembimbing menerima notifikasi.');
  await f.shot('Bab 1 berstatus submitted (versi 1)', 'Menunggu review pembimbing.');

  // ---- Dosen: minta revisi ----
  await f.as('dosen');
  await f.clickMenu('Ta Documents', 'Pembimbing membuka Ta Documents', 'Dokumen mahasiswa bimbingan.');
  await f.rowAction(['2026001003', 'Bab 1'], 'Minta Revisi', 'Klik Minta Revisi', 'Tuliskan poin revisi.', { modal: { summary: 'Tambahkan data pendukung pada latar belakang dan perjelas batasan masalah.' } });
  await f.clickMenu('Ta Reviews', 'Buka Ta Reviews', 'Riwayat review/revisi.');
  await f.shot('Review revisi tersimpan', 'Status review revision_required beserta catatan.');

  // ---- Mahasiswa: upload v2 ----
  await f.as('mahasiswa_demo2', { showLogin: false });
  await f.open('/ta-documents');
  await f.shot('Bab 1 berstatus revision_required', 'Mahasiswa melihat permintaan revisi.');
  await upload(v2, 'Revisi: data pendukung & batasan masalah ditambahkan.', 'Upload Versi 2 (revisi)', 'Unggah file hasil revisi.');
  await f.rowAction('Bab 1', 'Submit', 'Submit versi 2', 'Versi 2 dikirim ke pembimbing.');
  await f.clickMenu('Ta Document Versions', 'Buka Ta Document Versions', 'Histori versi dokumen.');
  await f.shot('Histori versi', 'Versi 1 dan versi 2 tersimpan lengkap (versioning).');

  // ---- Dosen: approve ----
  await f.as('dosen', { showLogin: false });
  await f.open('/ta-documents');
  await f.rowAction(['2026001003', 'Bab 1'], 'Approve', 'Pembimbing klik Approve Bab 1', 'Bab 1 disetujui.', { modal: { note: 'Bab 1 sudah baik.' } });
  await f.clickMenu('Ta Approvals', 'Buka Ta Approvals', 'Riwayat approval bab.');
  await f.shot('Approval bab', 'Bab 1 approved oleh pembimbing.');

  // Bab lain: langkah upload -> submit -> approve yang sama dijalankan otomatis oleh skrip QA.
  console.log(tinker('qa_m7_bulk.php').trim());

  // ---- Admin Fakultas: progress, checklist, finalisasi, repository ----
  await f.as('admin_fakultas');
  await f.clickMenu('Tugas Akhirs', 'Admin Fakultas membuka Tugas Akhirs', 'Kelola finalisasi TA.');
  await f.rowAction('2026001003', 'Hitung Progress', 'Klik Hitung Progress', 'Progres dihitung dari jumlah bab wajib yang sudah approved.');
  await f.rowAction('2026001003', 'Checklist', 'Klik Checklist', 'Checklist finalisasi: judul, pembimbing, semua bab wajib approved, metadata, tidak ada komentar terbuka.');
  await f.shot('Hasil checklist', 'Semua item checklist terpenuhi.');
  await f.rowAction('2026001003', 'Finalisasi', 'Klik Finalisasi', 'Dokumen final dikompilasi; status TA finalized.');
  await f.rowAction('2026001003', 'Buat Repository', 'Klik Buat Repository', 'Item repositori dibuat dari dokumen final beserta metadata.');
  await f.clickMenu('Repository Items', 'Buka Repository Items', 'Daftar item repositori digital.');
  await f.rowAction('Budi Demo QA', 'Publish', 'Klik Publish', 'Item repositori dipublikasikan sesuai hak akses.');
  await f.shot('Repositori terpublikasi', 'Status published.');
  await f.clickMenu('Ta Progress Logs', 'Buka Ta Progress Logs', 'Log progres TA.');
  await f.shot('Log progres TA', 'Setiap perubahan status tercatat (audit).');

  // ---- Mahasiswa & BAAK ----
  await f.as('mahasiswa_demo2', { showLogin: false });
  await f.open('/repository-items');
  await f.shot('Mahasiswa melihat repositori TA', 'TA sudah terpublikasi di repositori.');
  await f.as('baak');
  await f.clickMenu('Repository Items', 'BAAK membuka Repository Items', 'Rekap repositori final untuk keperluan yudisium.');
  await f.shot('Repositori final (BAAK)', 'BAAK memverifikasi TA mahasiswa sudah masuk repositori.');
});
