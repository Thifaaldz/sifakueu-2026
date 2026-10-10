// M2 Surat Menyurat — pengajuan -> verifikasi -> approval berjenjang -> nomor -> generate -> distribusi -> arsip -> verifikasi QR publik.
const { execSync } = require('child_process');
const path = require('path');
const { run } = require('./flow.cjs');
const { root, TENANT } = require('./lib.cjs');

const meta = {
  code: 'M2',
  title: 'Surat Menyurat',
  purpose: 'Mengelola pengajuan surat secara digital: jenis surat & form dinamis, verifikasi, approval berjenjang, penomoran otomatis, pembuatan dokumen, distribusi, arsip, serta verifikasi keaslian surat melalui token/QR.',
  actors: ['Mahasiswa', 'Admin Prodi', 'Kaprodi', 'Admin Fakultas / TU'],
  testCase: 'Mahasiswa FASILKOM mengajukan Surat Keterangan Aktif Kuliah untuk keperluan beasiswa; surat diverifikasi, disetujui Kaprodi, diberi nomor, dibuat dokumennya, didistribusikan, diarsipkan, lalu dicek keasliannya.',
};

const subject = `Surat Aktif Kuliah - Beasiswa QA ${Date.now().toString().slice(-5)}`;

run('m2', meta, async (f) => {
  // ---- Admin Fakultas: konfigurasi ----
  await f.as('admin_fakultas');
  await f.clickMenu('Jenis Surats', 'Admin Fakultas membuka M2 Surat > Jenis Surats', 'Master jenis surat beserta alur approval yang dipakai.');
  await f.shot('Daftar jenis surat', 'Contoh: Surat Keterangan Aktif Kuliah (pemohon mahasiswa) terhubung ke Flow Surat Mahasiswa.');
  await f.clickMenu('Form Field Surat', 'Buka Form Field Surat', 'Field form dinamis per jenis surat.');
  await f.shot('Field form dinamis', 'Field Nama, NIM, Program Studi, Semester, Keperluan — otomatis tampil saat mahasiswa memilih jenis surat.');
  await f.clickMenu('Approval Step', 'Buka Approval Step', 'Urutan verifikasi/approval surat.');
  await f.shot('Urutan approval', '1) Admin Prodi verifikasi → 2) Kaprodi approval → 3) Admin Fakultas acknowledgement.');
  await f.clickMenu('Template Surat', 'Buka Template Surat', 'Template isi surat dengan placeholder.');
  await f.shot('Template surat', 'Template dipakai saat generate dokumen.');

  // ---- Mahasiswa: ajukan ----
  await f.as('mahasiswa');
  await f.shot('Beranda Mahasiswa', 'Menu M2 Surat ada di sidebar.');
  await f.clickMenu('Surats', 'Buka menu M2 Surat > Surats', 'Daftar pengajuan surat milik mahasiswa.');
  await f.create('Klik New surat', 'Buat pengajuan surat baru.');
  await f.select('jenis_surat_id', 'Surat Keterangan Aktif Kuliah');
  await f.page.waitForTimeout(1200);
  await f.fill('subject', subject);
  await f.fill('payload.keperluan', 'Persyaratan pengajuan beasiswa Bank Indonesia 2026.');
  await f.submitForm('Pilih jenis surat, lengkapi form dinamis, lalu klik Buat', 'Setelah jenis surat dipilih, form dinamis tampil dan data mahasiswa (nama, NIM, prodi, semester) terisi otomatis. Isi Perihal dan Keperluan.');
  await f.open('/surats');
  await f.shot('Pengajuan tersimpan sebagai Draft', 'Surat masih Draft dan bisa diubah sebelum diajukan.');
  await f.rowAction(subject, 'Submit', 'Klik Submit untuk mengajukan', 'Sistem memvalidasi field wajib & lampiran, membuat nomor pengajuan (REQ-…) dan antrian approval.');
  await f.shot('Status: Verifikasi', 'Pengajuan masuk tahap verifikasi Admin Prodi.');

  // ---- Admin Prodi: verifikasi ----
  await f.as('admin_prodi');
  await f.clickMenu('Surats', 'Admin Prodi membuka M2 Surat > Surats', 'Daftar pengajuan surat yang perlu diverifikasi.');
  await f.rowAction(subject, 'Verifikasi', 'Klik Verifikasi', 'Admin Prodi memeriksa kebenaran data pengajuan. Alternatif: Minta Revisi atau Tolak.', { modal: { note: 'Data mahasiswa valid, status aktif semester berjalan.' } });
  await f.shot('Status: Menunggu Approval', 'Setelah verifikasi, surat menunggu approval Kaprodi.');

  // ---- Kaprodi: approve ----
  await f.as('kaprodi');
  await f.clickMenu('Surats', 'Kaprodi membuka M2 Surat > Surats', 'Kaprodi melihat surat yang menunggu persetujuannya.');
  await f.rowAction(subject, 'Approve', 'Kaprodi klik Approve', 'Approval step ke-2 (Kaprodi).', { modal: { note: 'Disetujui.' } });
  await f.clickMenu('Approval Surat', 'Kaprodi membuka Approval Surat', 'Riwayat approval per step.');
  await f.shot('Riwayat approval', 'Step Kaprodi berstatus APPROVED, step Admin Fakultas masih PENDING.');

  // ---- Admin Fakultas: acknowledge, nomor, generate, distribusi, arsip ----
  await f.as('admin_fakultas', { showLogin: false });
  await f.open('/surats');
  await f.rowAction(subject, 'Approve', 'Admin Fakultas klik Approve (acknowledgement)', 'Step terakhir; status surat menjadi Disetujui.', { modal: { note: 'Diterima TU.' } });
  await f.rowAction(subject, 'Nomor', 'Klik Nomor', 'Nomor surat dibuat otomatis dari sequence (format nomor/kode/fakultas/bulan romawi/tahun).');
  await f.rowAction(subject, 'Generate', 'Klik Generate', 'Dokumen surat dibuat dari template beserta token verifikasi (QR).');
  await f.shot('Status: Generated dan nomor surat terisi', 'Kolom Nomor sudah terisi dan tombol Download tersedia.');
  await f.rowAction(subject, 'Distribusi', 'Klik Distribusi', 'Surat dikirim ke pemohon (kanal download/notifikasi).');
  await f.rowAction(subject, 'Arsip', 'Klik Arsip', 'Surat masuk arsip digital.');
  await f.shot('Status: Arsip', 'Seluruh siklus surat selesai.');
  await f.clickMenu('Dokumen Generated', 'Buka Dokumen Generated', 'Daftar file surat yang dibuat sistem.');
  await f.shot('Dokumen generated', 'File surat dan versinya.');
  await f.clickMenu('Arsip Surat', 'Buka Arsip Surat', 'Arsip digital surat beserta klasifikasi.');
  await f.shot('Arsip surat', 'Arsip dapat dicari dan difilter.');
  await f.clickMenu('Token Verifikasi', 'Buka Token Verifikasi', 'Token yang dipakai untuk QR verifikasi keaslian surat.');
  await f.shot('Token verifikasi', 'Setiap dokumen surat memiliki token unik.');

  // ---- Mahasiswa: lihat & download ----
  await f.as('mahasiswa', { showLogin: false });
  await f.open('/surats');
  await f.shot('Mahasiswa melihat surat selesai', 'Status Arsip, nomor surat tersedia dan tombol Download aktif.', { highlight: f.row(subject).locator('a', { hasText: 'Download' }) });

  // ---- Publik: verifikasi keaslian ----
  const out = execSync(`docker compose exec -T php php artisan tinker --execute='app(\\App\\Support\\Tenancy\\TenantContext::class)->set(\\App\\Models\\Tenant::where("slug","fasilkom")->first()); $s=\\App\\Models\\Surat::where("subject", ${JSON.stringify(subject)})->first(); echo "TOKEN=".$s->qr_public_token;'`, { cwd: root }).toString();
  const token = (out.match(/TOKEN=(\S+)/) || [])[1];
  if (token) {
    const page = await f.sessions.mahasiswa.context.newPage();
    const prev = f.page;
    f.page = page;
    await page.goto(`${TENANT}/verify/letter/${token}`, { waitUntil: 'networkidle' });
    await f.shot('Halaman verifikasi publik (scan QR)', `Siapa pun dapat membuka ${TENANT}/verify/letter/{token} (dari QR pada surat) untuk memastikan surat asli dan masih berlaku.`, { role: 'publik' });
    await f.checkErrors();
    f.page = prev;
  } else {
    f.issues.push('Token verifikasi surat tidak ditemukan setelah generate.');
  }
});
