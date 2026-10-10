// M4 KRS & Penjadwalan — periode -> penawaran & peminat -> kelas & plotting (M5) -> jadwal & konflik -> KRS mahasiswa -> approval PA -> final.
const { run } = require('./flow.cjs');

const meta = {
  code: 'M4',
  title: 'KRS & Penjadwalan',
  purpose: 'Mengelola periode KRS, penawaran mata kuliah, perhitungan peminat, pembentukan kelas, plotting dosen (rekomendasi M5), penjadwalan dan pengecekan konflik, serta pengisian KRS mahasiswa dengan validasi otomatis dan approval Dosen PA.',
  actors: ['Admin Prodi', 'Mahasiswa', 'Dosen PA', 'Kaprodi'],
  testCase: 'Admin Prodi menyiapkan penawaran semester 2026-GANJIL; Rina Demo QA (NIM 2026001002) mengisi KRS 2 mata kuliah (6 SKS), Dr. Dosen FASILKOM sebagai Dosen PA menyetujui, lalu Admin Prodi memfinalkan KRS.',
};

run('m4', meta, async (f) => {
  // ---- Admin Prodi: persiapan ----
  await f.as('admin_prodi');
  await f.clickMenu('Periode Krs', 'Buka M4 KRS & Penjadwalan > Periode Krs', 'Periode pengisian KRS per semester.');
  await f.shot('Periode KRS aktif', 'Periode 2026-GANJIL berstatus open — mahasiswa dapat mengisi KRS.');
  await f.edit('2026-GANJIL', 'Klik Edit untuk mengatur tanggal periode', 'Pastikan rentang tanggal pengisian KRS mencakup hari ini. (Temuan QA: periode seed berakhir 30 Sep 2026 sehingga KRS tidak bisa disubmit.)');
  await f.fill('tanggal_selesai', '2026-10-31T23:59');
  await f.fill('tanggal_revisi_mulai', '2026-11-01T00:00');
  await f.fill('tanggal_revisi_selesai', '2026-11-07T23:59');
  await f.submitForm('Perpanjang periode lalu klik Simpan perubahan', 'Tanggal selesai diubah menjadi 31 Okt 2026; masa revisi 1–7 Nov 2026.');
  await f.clickMenu('Penawaran Mata Kuliahs', 'Buka Penawaran Mata Kuliahs', 'Mata kuliah yang ditawarkan pada semester.');
  await f.rowAction('IF101', 'Hitung Peminat', 'Klik Hitung Peminat', 'Sistem menghitung jumlah peminat dari KRS mahasiswa.');
  await f.rowAction('IF101', 'Generate Kelas', 'Klik Generate Kelas', 'Kelas dibentuk otomatis berdasarkan peminat dan kuota.');
  await f.shot('Penawaran setelah dihitung', 'Peminat & jumlah kelas terbarui.');
  await f.clickMenu('Kelas Kuliahs', 'Buka Kelas Kuliahs', 'Daftar kelas kuliah.');
  await f.rowAction('IF101-A', 'M5 Kandidat', 'Klik M5 Kandidat', 'Meminta rekomendasi dosen pengampu dari modul M5 (kecocokan keahlian, beban, preferensi).');
  await f.shot('Kandidat dosen dari M5', 'Rekomendasi tersimpan dan dapat dipakai untuk plotting.');
  await f.clickMenu('Plotting Dosens', 'Buka Plotting Dosens', 'Penetapan dosen pengampu per kelas.');
  await f.shot('Plotting dosen pengampu', 'Dosen pengampu yang ditetapkan untuk setiap kelas.');
  await f.clickMenu('Jadwal Kuliahs', 'Buka Jadwal Kuliahs', 'Jadwal kuliah per kelas (hari, jam, ruang, dosen).');
  await f.rowAction('IF101', 'Cek Konflik', 'Klik Cek Konflik', 'Sistem memeriksa bentrok ruang, dosen, dan kelas.');
  await f.shot('Hasil cek konflik', 'Jika ada bentrok, tercatat di menu Jadwal Conflicts.');
  await f.clickMenu('Jadwal Conflicts', 'Buka Jadwal Conflicts', 'Daftar konflik jadwal & tombol Resolve.');
  await f.shot('Daftar konflik jadwal', 'Konflik yang sudah diselesaikan ditandai resolved.');

  // ---- Mahasiswa: isi KRS ----
  await f.as('mahasiswa_demo');
  await f.clickMenu('Krs', 'Mahasiswa membuka M4 KRS & Penjadwalan > Krs', 'Halaman KRS mahasiswa.');
  await f.create('Klik New krs', 'Buat KRS untuk semester berjalan.');
  await f.select('semester_id', '2026-GANJIL');
  await f.select('tahun_akademik_id', '2026');
  await f.select('dosen_pa_id', 'Dr. Dosen FASILKOM');
  await f.submitForm('Isi semester & Dosen PA lalu klik Buat', 'Mahasiswa otomatis sesuai akun login. Status, SKS total, dan approval tidak dapat diubah mahasiswa.');
  await f.open('/krs');
  for (const mk of ['IF101', 'SI201']) {
    await f.rowAction('2026001002', 'Tambah MK', `Klik Tambah MK (${mk})`, 'Pilih mata kuliah dari daftar penawaran semester ini.', { modal: { penawaran_mata_kuliah_id: mk === 'IF101' ? '1' : '2' } });
  }
  await f.shot('KRS berisi 2 MK (6 SKS)', 'Total SKS dihitung otomatis.');
  await f.rowAction('2026001002', 'Validasi', 'Klik Validasi', 'Validasi otomatis: batas SKS, prasyarat, kurikulum, dan bentrok jadwal.');
  await f.clickMenu('Krs Validation Results', 'Lihat Krs Validation Results', 'Hasil validasi per aturan.');
  await f.shot('Hasil validasi KRS', 'Setiap aturan berstatus valid/warning/invalid beserta pesan.');
  await f.clickMenu('Krs', 'Kembali ke menu Krs', 'Ajukan KRS setelah valid.');
  await f.rowAction('2026001002', 'Submit', 'Klik Submit', 'KRS dikirim ke Dosen PA (status Menunggu PA) dan Dosen PA menerima notifikasi.');
  await f.shot('Status: waiting_pa', 'KRS menunggu persetujuan Dosen PA.');

  // ---- Dosen PA: approve ----
  await f.as('dosen');
  await f.clickMenu('Krs', 'Dosen PA membuka Krs', 'KRS mahasiswa bimbingan akademik.');
  await f.rowAction('2026001002', 'Approve', 'Klik Approve', 'Dosen PA menyetujui KRS (atau Minta Revisi dengan catatan).', { modal: { note: 'Beban studi sesuai, disetujui.' } });
  await f.shot('KRS disetujui', 'Status KRS approved.');

  // ---- Admin Prodi: final ----
  await f.as('admin_prodi', { showLogin: false });
  await f.open('/krs');
  await f.rowAction('2026001002', 'Final', 'Admin Prodi klik Final', 'KRS dikunci (final) dan menjadi dasar kelas & monitoring.');
  await f.shot('KRS final', 'Status final.');

  // ---- Mahasiswa: lihat hasil ----
  await f.as('mahasiswa_demo', { showLogin: false });
  await f.open('/krs');
  await f.shot('Mahasiswa melihat KRS final', 'KRS sudah final.');
  await f.clickMenu('Krs Details', 'Buka Krs Details', 'Rincian mata kuliah yang diambil.');
  await f.shot('Rincian KRS', 'Daftar MK, SKS, dan status approved.');
  await f.clickMenu('Jadwal Kuliahs', 'Buka Jadwal Kuliahs', 'Jadwal kuliah mahasiswa.');
  await f.shot('Jadwal kuliah', 'Hari, jam, ruang, dan dosen pengampu.');

  // ---- Kaprodi: monitoring ----
  await f.as('kaprodi');
  await f.clickMenu('Krs', 'Kaprodi memantau KRS', 'Rekap status KRS seluruh mahasiswa prodi.');
  await f.shot('Monitoring KRS oleh Kaprodi', 'Kaprodi melihat status KRS (draft/menunggu PA/final).');
});
