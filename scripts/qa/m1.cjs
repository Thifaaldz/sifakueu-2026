// M1 Sidang Sempro & TA — alur end-to-end: daftar -> verifikasi -> plotting -> jadwal -> nilai -> hasil -> revisi -> berita acara.
const { run } = require('./flow.cjs');

const meta = {
  code: 'M1',
  title: 'Sidang Sempro & Tugas Akhir',
  purpose: 'Mengelola pendaftaran Seminar Proposal (SEMPRO) dan Sidang TA mulai dari pengecekan syarat, verifikasi, plotting penguji, penjadwalan, penilaian, keputusan, revisi, hingga berita acara.',
  actors: ['Mahasiswa', 'Admin Prodi', 'Dosen Penguji', 'Kaprodi'],
  testCase: 'Mahasiswa Rina Demo QA (NIM 2026001002) mendaftar SEMPRO, diverifikasi Admin Prodi, diuji oleh Dr. Dosen FASILKOM, lalu hasil dan berita acara diterbitkan.',
};

const today = new Date();
const iso = (d) => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
const monday = new Date(today);
monday.setDate(today.getDate() + ((8 - today.getDay()) % 7 || 7));
const tgl = iso(monday);
const tglBaru = iso(new Date(monday.getTime() + 4 * 86400000));

run('m1', meta, async (f) => {
  // ---- Mahasiswa: cek syarat & daftar ----
  await f.as('mahasiswa_demo');
  await f.shot('Beranda Mahasiswa', 'Setelah login, mahasiswa masuk ke Beranda. Menu Sidang ada di sidebar kiri.');
  await f.clickMenu('Sidang Requirements', 'Buka menu M1 Sidang > Sidang Requirements', 'Mahasiswa dapat melihat daftar persyaratan setiap jenis sidang sebelum mendaftar.');
  await f.shot('Daftar persyaratan SEMPRO & Sidang TA', 'Syarat sistem (mahasiswa aktif, minimal SKS, pembimbing valid, kesiapan dokumen TA) dicek otomatis; syarat manual (BERKAS_ADMIN) diverifikasi admin.', { fullPage: true });
  await f.clickMenu('Sidang Registrations', 'Buka menu Sidang Registrations', 'Halaman pendaftaran sidang milik mahasiswa.');
  await f.create('Klik tombol New sidang registration', 'Klik tombol New untuk membuat pendaftaran baru.');
  await f.select('sidang_type_id', 'Seminar Proposal');
  await f.select('tugas_akhir_id', 'Prediksi Risiko');
  await f.select('semester_id', '2026');
  await f.select('tahun_akademik_id', '2026');
  await f.submitForm('Isi form pendaftaran lalu klik Buat', 'Pilih Jenis Sidang = Seminar Proposal, Tugas Akhir, Semester dan Tahun Akademik. Field mahasiswa terisi otomatis sesuai akun login. Status awal Draft.');
  await f.open('/sidang-registrations');
  const reg = (await f.row('Rina Demo QA').innerText()).match(/SIDANG-\d{8}-[A-Z0-9]+/)[0];
  console.log('registration', reg);
  await f.shot('Pendaftaran tersimpan (status draft)', 'Nomor pendaftaran dibuat otomatis (SIDANG-YYYYMMDD-xxxxxx). Sistem langsung menjalankan validasi syarat otomatis.');
  await f.rowAction('Rina Demo QA', 'Submit', 'Klik Submit untuk mengajukan pendaftaran', 'Submit hanya bisa dilakukan jika semua syarat wajib otomatis sudah valid.');
  await f.shot('Status menjadi submitted', 'Pendaftaran sudah diajukan dan menunggu verifikasi Admin Prodi.');

  // ---- Admin Prodi: verifikasi ----
  await f.as('admin_prodi');
  await f.clickMenu('Sidang Registrations', 'Admin Prodi membuka M1 Sidang > Sidang Registrations', 'Admin Prodi melihat semua pendaftaran sidang di prodinya.');
  await f.rowAction('Rina Demo QA', 'Waive Manual', 'Verifikasi berkas manual (Waive Manual)', 'Syarat manual BERKAS_ADMIN ditandai sudah diperiksa dengan catatan.', { modal: { note: 'Berkas administrasi fisik sudah diperiksa TU.' } });
  await f.rowAction('Rina Demo QA', 'Verifikasi', 'Klik Verifikasi pendaftaran', 'Jika semua syarat valid/waived, status berubah menjadi ready_for_plotting.', { modal: { note: 'Syarat lengkap, lanjut plotting penguji.' } });
  await f.shot('Status ready_for_plotting', 'Pendaftaran terverifikasi dan siap diplot penguji.');
  await f.rowAction('Rina Demo QA', 'Rekomendasi Penguji', 'Lihat rekomendasi penguji (integrasi M5)', 'Sistem menampilkan kandidat penguji berdasarkan kecocokan keahlian dan beban dosen.');
  await f.shot('Notifikasi top kandidat penguji', 'Notifikasi menampilkan nama dosen dan skor kecocokan.');

  // ---- Plotting penguji ----
  await f.clickMenu('Sidang Assignments', 'Buka menu Sidang Assignments', 'Tempat menetapkan pembimbing/penguji untuk pendaftaran sidang.');
  await f.create('Klik New sidang assignment', 'Tambah penugasan penguji.');
  await f.select('sidang_registration_id', reg);
  await f.select('dosen_id', 'Dr. Dosen FASILKOM');
  await f.select('role', 'Penguji 1');
  await f.fill('justification', 'Keahlian sesuai topik machine learning (rekomendasi M5).');
  await f.submitForm('Isi penugasan Penguji 1 lalu klik Buat', 'Pilih nomor pendaftaran, dosen penguji, peran Penguji 1, dan justifikasi.');
  await f.open('/sidang-assignments');
  await f.rowAction([reg, 'Dr. Dosen FASILKOM'], 'Set Assignment', 'Klik Set Assignment', 'Sistem memvalidasi dosen aktif & bukan pembimbing, lalu menetapkan penugasan.');
  await f.shot('Penugasan penguji tersimpan', 'Dosen penguji tercatat pada pendaftaran sidang.');

  // ---- Jadwal ----
  await f.clickMenu('Sidang Schedules', 'Buka menu Sidang Schedules', 'Penjadwalan sidang (tanggal, jam, ruang, mode).');
  await f.create('Klik New sidang schedule', 'Buat jadwal sidang baru.');
  await f.select('sidang_registration_id', reg);
  await f.fill('tanggal', tgl);
  await f.fill('jam_mulai', '09:00');
  await f.fill('jam_selesai', '10:30');
  await f.select('ruangan_id', 'Ruang Kuliah 301');
  await f.submitForm('Isi jadwal lalu klik Buat', `Tanggal ${tgl} (Senin), 09:00–10:30, Ruang Kuliah 301, mode Onsite.`);
  await f.open('/sidang-schedules');
  await f.rowAction(reg, 'Finalisasi', 'Klik Finalisasi jadwal', 'Sistem mengecek bentrok ruang, penguji, dan jadwal kuliah dosen (M4) sebelum jadwal dikunci.', { expectError: true });
  await f.shot('Sistem mendeteksi bentrok jadwal', 'Hari Senin 09:00 dosen penguji sedang mengajar (jadwal kuliah M4), sehingga status jadwal menjadi conflict dan muncul notifikasi merah.');
  await f.dismissNotifications();
  await f.edit(reg, 'Klik Edit untuk menjadwalkan ulang', 'Ubah tanggal ke hari yang tidak bentrok.');
  await f.fill('tanggal', tglBaru);
  await f.submitForm('Ganti tanggal lalu klik Simpan perubahan', `Tanggal diganti menjadi ${tglBaru} (Jumat).`);
  await f.open('/sidang-schedules');
  await f.rowAction(reg, 'Finalisasi', 'Klik Finalisasi lagi', 'Tidak ada bentrok: status jadwal menjadi final dan pendaftaran menjadi scheduled.');
  await f.rowAction(reg, 'Mulai', 'Hari H: klik Mulai', 'Menandai sidang sedang berlangsung (in_progress).');
  await f.shot('Sidang berlangsung', 'Status jadwal in_progress.');

  // ---- Dosen penguji: nilai ----
  await f.as('dosen');
  await f.shot('Beranda Dosen', 'Dosen penguji login ke panel dosen.');
  await f.clickMenu('Sidang Schedules', 'Dosen membuka jadwal menguji', 'Daftar jadwal sidang di mana dosen menjadi penguji.');
  await f.shot('Jadwal menguji', 'Jadwal sidang mahasiswa yang diuji tampil di sini.');
  for (const [i, rubric] of ['Penguasaan Materi', 'Metodologi', 'Presentasi'].entries()) {
    await f.clickMenu('Sidang Scores', `Buka menu Sidang Scores (rubrik ${rubric})`, 'Input nilai per rubrik.');
    await f.create('Klik New sidang score', 'Tambah nilai rubrik.');
    await f.select('sidang_registration_id', reg);
    await f.select('sidang_rubric_id', rubric);
    await f.fill('score', [85, 80, 88][i]);
    await f.fill('note', `Catatan ${rubric}: baik.`);
    await f.submitForm(`Isi nilai rubrik ${rubric} lalu klik Buat`, 'Penguji terisi otomatis sesuai akun dosen. Nilai 0–100.');
  }
  await f.open('/sidang-scores');
  for (let i = 0; i < 3; i += 1) {
    const row = f.page.locator('table tbody tr', { hasText: reg }).filter({ has: f.page.locator('.fi-ta-actions button', { hasText: 'Submit' }) }).first();
    if (!(await row.count())) break;
    await f.rowAction([reg, (await row.innerText()).match(/Penguasaan Materi|Metodologi|Presentasi/)[0]], 'Submit', 'Klik Submit pada nilai', 'Nilai dikunci (submitted_at terisi).');
  }
  await f.shot('Semua nilai sudah disubmit', 'Nilai rubrik siap direkap.');

  await f.clickMenu('Sidang Revisions', 'Penguji mencatat revisi: buka Sidang Revisions', 'Catatan revisi dari penguji.');
  await f.create('Klik New sidang revision', 'Tambah butir revisi.');
  await f.select('sidang_registration_id', reg);
  await f.select('examiner_id', 'Dr. Dosen FASILKOM');
  await f.fill('description', 'Perjelas metode evaluasi model dan tambahkan confusion matrix.');
  await f.fill('category', 'Metodologi');
  await f.fill('deadline', new Date(today.getTime() + 17 * 86400000).toISOString().slice(0, 10));
  await f.submitForm('Isi revisi lalu klik Buat', 'Deskripsi revisi, kategori dan deadline revisi.');

  // ---- Admin Prodi: selesai, finalisasi hasil, publish, berita acara ----
  await f.as('admin_prodi', { showLogin: false });
  await f.open('/sidang-schedules');
  await f.rowAction(reg, 'Selesai', 'Admin menandai sidang Selesai', 'Status jadwal completed.');
  await f.open('/sidang-registrations');
  await f.rowAction('Rina Demo QA', 'Finalisasi Hasil', 'Klik Finalisasi Hasil', 'Sistem merekap nilai berbobot rubrik dan menentukan nilai akhir, grade, dan keputusan.', { modal: { note: 'Lulus dengan perbaikan minor.' } });
  await f.clickMenu('Sidang Results', 'Buka Sidang Results', 'Hasil sidang hasil rekap otomatis.');
  await f.rowAction(reg, 'Publish', 'Klik Publish hasil sidang', 'Hasil dipublikasikan sehingga terlihat oleh mahasiswa.');
  await f.shot('Hasil sidang dipublish', 'Kolom published_at terisi.');
  await f.open('/sidang-registrations');
  await f.rowAction('Rina Demo QA', 'Berita Acara', 'Generate Berita Acara', 'Berita acara sidang dibuat otomatis.');
  await f.clickMenu('Sidang Minutes', 'Buka Sidang Minutes', 'Daftar berita acara.');
  await f.shot('Berita acara tergenerate', 'Dokumen berita acara tersimpan dengan status generated.');

  // ---- Dosen: validasi revisi ----
  await f.as('dosen', { showLogin: false });
  await f.open('/sidang-revisions');
  await f.rowAction(reg, 'Validasi', 'Penguji memvalidasi revisi', 'Setelah mahasiswa memperbaiki, penguji klik Validasi.');

  // ---- Mahasiswa: lihat hasil ----
  await f.as('mahasiswa_demo', { showLogin: false });
  await f.open('/sidang-registrations');
  await f.shot('Mahasiswa melihat status pendaftaran', 'Status pendaftaran terbaru.');
  await f.clickMenu('Sidang Schedules', 'Mahasiswa melihat jadwal sidang', 'Jadwal, ruang, dan status sidang.');
  await f.shot('Jadwal sidang mahasiswa', 'Informasi jadwal sidang.');
  await f.clickMenu('Sidang Results', 'Mahasiswa melihat hasil sidang', 'Nilai akhir, grade dan keputusan.');
  await f.shot('Hasil sidang mahasiswa', 'Hasil yang sudah dipublish.');
  await f.clickMenu('Sidang Revisions', 'Mahasiswa melihat daftar revisi', 'Butir revisi beserta deadline dan status.');
  await f.shot('Revisi sidang mahasiswa', 'Status revisi validated setelah divalidasi penguji.');

  // ---- Kaprodi: monitoring ----
  await f.as('kaprodi');
  await f.clickMenu('Sidang Registrations', 'Kaprodi memantau pendaftaran sidang', 'Monitoring pendaftaran sidang di panel pimpinan.');
  await f.shot('Monitoring sidang oleh Kaprodi', 'Kaprodi melihat seluruh status pendaftaran sidang prodi.');
});
