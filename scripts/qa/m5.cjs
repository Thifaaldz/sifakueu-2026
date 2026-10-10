// M5 Profiling Dosen — dosen melengkapi profil -> sistem hitung kesesuaian & rekomendasi -> KBK/Kaprodi validasi -> pimpinan memantau.
const { run } = require('./flow.cjs');

const meta = {
  code: 'M5',
  title: 'Profiling Dosen & Rekomendasi Pengajaran',
  purpose: 'Membangun profil kompetensi dosen (keahlian, pendidikan, sertifikasi, publikasi, pengalaman industri, riwayat mengajar, preferensi MK), menghitung beban dan matriks kesesuaian dosen–MK, menghasilkan rekomendasi dosen pengampu/pembimbing/penguji, serta mengelola jadwal konsultasi & ketersediaan dosen.',
  actors: ['Dosen', 'Koordinator KBK', 'Admin Prodi', 'Dekan'],
  testCase: 'Dr. Dosen FASILKOM menambah sertifikasi, publikasi, preferensi MK, dan jadwal konsultasi; KBK menghitung ulang rekomendasi pengampu MK IF101 dan menerima rekomendasi; Dekan memantau beban & kesesuaian dosen.',
};

const tag = Date.now().toString().slice(-4);

run('m5', meta, async (f) => {
  // ---- Dosen: lengkapi profil ----
  await f.as('dosen');
  await f.clickMenu('Dosen Profils', 'Buka M5 Profiling Dosen > Dosen Profils', 'Ringkasan profil dosen.');
  await f.shot('Profil dosen', 'Status profil, fokus keahlian, dan ringkasan pengalaman.');
  await f.clickMenu('Keahlians', 'Buka Keahlians', 'Bidang keahlian dosen (terhubung Rumpun Ilmu & KBK).');
  await f.shot('Daftar keahlian', 'Keahlian menjadi dasar perhitungan kesesuaian dosen–MK.');

  await f.clickMenu('Dosen Sertifikasis', 'Buka Dosen Sertifikasis', 'Sertifikasi kompetensi dosen.');
  await f.create('Klik New dosen sertifikasi', 'Tambah sertifikasi baru.');
  await f.fill('name', `AWS Certified Machine Learning ${tag}`);
  await f.fill('issuer', 'Amazon Web Services');
  await f.fill('field', 'Machine Learning');
  await f.fill('certificate_number', `AWS-MLS-${tag}`);
  await f.fill('issued_on', '2026-03-10');
  await f.fill('expires_on', '2029-03-10');
  await f.submitForm('Isi data sertifikasi lalu klik Buat', 'Dosen otomatis terisi akun sendiri; status validasi diatur oleh Admin/KBK (bukan oleh dosen).');

  await f.clickMenu('Dosen Publikasis', 'Buka Dosen Publikasis', 'Publikasi ilmiah dosen.');
  await f.create('Klik New dosen publikasi', 'Tambah publikasi.');
  await f.fill('title', `Early Warning System Akademik Berbasis Gradient Boosting ${tag}`);
  await f.fill('year', '2026');
  await f.fill('type', 'Jurnal');
  await f.fill('publisher', 'Jurnal Teknologi Informasi (SINTA 2)');
  await f.fill('field', 'Machine Learning');
  await f.submitForm('Isi data publikasi lalu klik Buat', 'Judul, tahun, jenis, jurnal, dan bidang.');

  await f.clickMenu('Dosen Preferensi Mks', 'Buka Dosen Preferensi Mks', 'Preferensi mata kuliah yang ingin diampu.');
  await f.shot('Daftar preferensi MK', 'Setiap kombinasi dosen–MK–semester hanya boleh satu (sistem menolak duplikat dengan pesan validasi).');
  await f.edit('Analisis dan Perancangan', 'Klik Edit pada preferensi Analisis dan Perancangan Sistem', 'Perbarui tingkat preferensi atau catatan.');
  await f.fill('notes', 'Berpengalaman mengampu APS 3 tahun; siap mengampu kelas paralel.');
  await f.submitForm('Ubah catatan lalu klik Simpan perubahan', 'Preferensi dipakai sebagai salah satu bobot rekomendasi pengampu.');
  await f.clickMenu('Jadwal Konsultasis', 'Buka M5 Availability > Jadwal Konsultasis', 'Jadwal konsultasi/bimbingan dosen.');
  await f.create('Klik New jadwal konsultasi', 'Tambah slot konsultasi.');
  await f.select('day_of_week', 'Kamis');
  await f.fill('starts_at', '13:00');
  await f.fill('ends_at', '15:00');
  await f.fill('room', `Ruang Dosen 2.${tag.slice(-2)}`);
  await f.submitForm('Isi hari, jam, ruang lalu klik Buat', 'Slot ini terlihat oleh mahasiswa dan dipakai cek ketersediaan sidang.');
  await f.clickMenu('Beban Dosens', 'Dosen melihat Beban Dosens', 'Beban mengajar, membimbing, dan menguji.');
  await f.shot('Beban dosen', 'Total SKS dan jumlah bimbingan/penguji per semester.');

  // ---- KBK: hitung & validasi rekomendasi ----
  await f.as('kbk');
  await f.clickMenu('Mata Kuliahs', 'KBK membuka Master Data > Mata Kuliahs', 'Rekomendasi pengampu dihitung per mata kuliah.');
  await f.rowAction('IF101', 'Hitung rekomendasi', 'Klik Hitung rekomendasi pada IF101', 'Sistem menilai setiap dosen berdasarkan keahlian, pendidikan, sertifikasi, publikasi, riwayat mengajar, preferensi, dan beban.');
  await f.clickMenu('Matriks Kesesuaians', 'Buka Matriks Kesesuaians', 'Skor kesesuaian dosen–MK.');
  await f.shot('Matriks kesesuaian dosen–MK', 'Skor tiap komponen dan skor total.', { fullPage: true });
  await f.clickMenu('Rekomendasi Pengampus', 'Buka Rekomendasi Pengampus', 'Daftar kandidat pengampu per MK beserta peringkat.');
  await f.shot('Rekomendasi pengampu', 'Kandidat diurutkan berdasar skor; status generated menunggu keputusan.', { fullPage: true });
  const row = f.page.locator('table tbody tr').filter({ has: f.page.locator('.fi-ta-actions button', { hasText: 'Accept' }) }).first();
  const rowText = (await row.innerText()).split('\n').filter(Boolean)[0];
  await f.rowAction(rowText, 'Accept', 'Klik Accept pada kandidat teratas', 'KBK/Kaprodi menerima rekomendasi; justifikasi wajib bila skor di bawah threshold.', { modal: { justification: 'Kompetensi sesuai CPL mata kuliah.' } });
  await f.shot('Rekomendasi diterima', 'Status rekomendasi accepted dan tersimpan di histori.');

  // ---- Dekan: monitoring ----
  await f.as('dekan');
  await f.clickMenu('Beban Dosens', 'Dekan membuka M5 Analytics > Beban Dosens', 'Peta beban dosen fakultas (read-only).');
  await f.shot('Peta beban dosen', 'Dekan memantau distribusi beban.');
  await f.clickMenu('Matriks Kesesuaians', 'Dekan melihat Matriks Kesesuaian', 'Kesesuaian keahlian dosen dengan mata kuliah (read-only).');
  await f.shot('Matriks kesesuaian (Dekan)', 'Dekan memantau kecocokan dosen–MK untuk perencanaan SDM.');

  // ---- Mahasiswa: lihat jadwal konsultasi ----
  await f.as('mahasiswa');
  await f.open('/jadwal-konsultasis');
  await f.shot('Mahasiswa melihat jadwal konsultasi dosen', 'Mahasiswa dapat melihat slot konsultasi dosen pembimbing/PA.');
});
