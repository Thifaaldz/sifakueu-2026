// M3 Monitoring & Alert — rule -> evaluasi -> snapshot & indikator -> alert -> acknowledge -> tindak lanjut -> eskalasi -> resolve.
const { run } = require('./flow.cjs');

const meta = {
  code: 'M3',
  title: 'Monitoring & Alert Akademik',
  purpose: 'Memantau kondisi akademik mahasiswa secara otomatis (KRS, SKS, IPK, progres TA, Sempro/Sidang, revisi, CPL/PLO), mengklasifikasikan risiko Hijau/Kuning/Merah, membangkitkan alert, serta mencatat acknowledgement, tindak lanjut, eskalasi, dan penyelesaian.',
  actors: ['Admin Prodi', 'Mahasiswa', 'Dosen PA', 'Kaprodi'],
  testCase: 'Admin Prodi menjalankan evaluasi untuk Mahasiswa FASILKOM (NIM 2026001001). Alert "Progress SKS tertinggal" dan "Belum Sempro" muncul, mahasiswa melakukan acknowledge, Dosen PA memberi konseling dan mengeskalasi ke Kaprodi, Kaprodi menyelesaikan alert.',
};

const NIM = '2026001001';

run('m3', meta, async (f) => {
  // ---- Admin Prodi: rule & evaluasi ----
  await f.as('admin_prodi');
  await f.shot('Beranda Admin Prodi (ringkasan risiko)', 'Widget ringkasan risiko monitoring tampil di dashboard.', { fullPage: true });
  await f.clickMenu('Monitoring Rules', 'Buka M3 Monitoring > Monitoring Rules', 'Daftar aturan monitoring yang aktif.');
  await f.shot('Daftar rule monitoring', 'Contoh rule: KRS belum final, Progress SKS tertinggal, IPK rendah, Progress TA rendah, Belum Sempro/Sidang TA, Revisi overdue, Gap CPL/PLO — lengkap dengan severity.', { fullPage: true });
  await f.clickMenu('Mahasiswas', 'Buka Master Data > Mahasiswas', 'Evaluasi dapat dijalankan per mahasiswa (scheduler juga menjalankannya otomatis).');
  await f.rowAction(NIM, 'Evaluasi alert', 'Klik Evaluasi alert pada mahasiswa', 'Sistem menghitung semua indikator, menyimpan snapshot, lalu membuat/memperbarui alert.');
  await f.shot('Notifikasi hasil evaluasi', 'Jumlah alert aktif hasil evaluasi ditampilkan.');
  await f.clickMenu('Monitoring Snapshots', 'Buka Monitoring Snapshots', 'Snapshot kondisi akademik per evaluasi.');
  await f.shot('Snapshot monitoring', 'Tingkat risiko (green/yellow/red) dan skor per mahasiswa.');
  await f.clickMenu('Monitoring Indicator Results', 'Buka Monitoring Indicator Results', 'Hasil per indikator.');
  await f.shot('Hasil indikator', 'Setiap rule menghasilkan status indikator beserta nilai aktual vs ambang.');
  await f.clickMenu('Alerts', 'Buka Alerts', 'Daftar alert aktif.');
  await f.shot('Daftar alert', 'Alert tampil dengan severity, status, dan mahasiswa terkait.');

  // ---- Mahasiswa: acknowledge ----
  await f.as('mahasiswa');
  await f.clickMenu('Alerts', 'Mahasiswa membuka M3 Monitoring > Alerts', 'Mahasiswa hanya melihat alert miliknya sendiri.');
  await f.rowAction('Progress SKS', 'Acknowledge', 'Klik Acknowledge', 'Mahasiswa menandai alert sudah dibaca.');
  await f.shot('Status alert: acknowledged', 'Dosen PA dapat melihat bahwa mahasiswa sudah membaca peringatan.');

  // ---- Dosen PA: tindak lanjut & eskalasi ----
  await f.as('dosen');
  await f.clickMenu('Alerts', 'Dosen PA membuka Alerts', 'Alert mahasiswa bimbingan akademik.');
  await f.rowAction('Progress SKS', 'Tindak lanjut', 'Klik Tindak lanjut pada alert "Progress SKS tertinggal"', 'Catat tindakan yang dilakukan terhadap mahasiswa: jenis (konseling/reminder/koordinasi), catatan, tanggal aksi berikutnya.', { modal: { action_type: 'counseling', note: 'Konseling: rencana ambil 21 SKS semester depan + semester pendek.' } });
  await f.rowAction('Belum Sempro', 'Eskalasi', 'Klik Eskalasi pada alert "Belum Sempro"', 'Alert yang butuh keputusan pimpinan dieskalasi: pilih tujuan (Kaprodi) dan alasan.', { modal: { to_role: 'kaprodi', reason: 'Mahasiswa semester 8 belum Sempro, perlu keputusan Kaprodi.' } });
  await f.shot('Alert tereskalasi', 'Status alert menjadi escalated.');
  await f.clickMenu('Alert Followups', 'Buka Alert Followups', 'Riwayat tindak lanjut.');
  await f.shot('Riwayat tindak lanjut', 'Setiap tindak lanjut tercatat dengan jenis, catatan, dan pelaku.');

  // ---- Kaprodi: eskalasi & resolve ----
  await f.as('kaprodi');
  await f.shot('Beranda Kaprodi', 'Ringkasan risiko prodi pada panel pimpinan.', { fullPage: true });
  await f.clickMenu('Alert Escalations', 'Kaprodi membuka Alert Escalations', 'Daftar eskalasi yang ditujukan ke Kaprodi.');
  await f.shot('Daftar eskalasi', 'Eskalasi berisi alasan, asal role, dan tujuan role.');
  await f.clickMenu('Alerts', 'Kaprodi membuka Alerts', 'Kaprodi dapat menyelesaikan alert.');
  await f.rowAction('Progress SKS', 'Resolve', 'Klik Resolve', 'Alert ditutup setelah tindak lanjut dinilai cukup.');
  await f.shot('Alert resolved', 'Status alert menjadi resolved; histori tetap tersimpan.');

  // ---- Mahasiswa: lihat status ----
  await f.as('mahasiswa', { showLogin: false });
  await f.open('/alerts');
  await f.shot('Mahasiswa melihat status alert terbaru', 'Satu alert resolved, satu alert escalated.');
});
