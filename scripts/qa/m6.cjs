// M6 Profiling Mahasiswa — CPL/PLO & profil lulusan -> mahasiswa melengkapi data -> recalculate -> skor, gap, rekomendasi.
const { run } = require('./flow.cjs');

const meta = {
  code: 'M6',
  title: 'Profiling Mahasiswa & Rekomendasi Profil Lulusan',
  purpose: 'Membangun profil mahasiswa (akademik, minat, sertifikasi, portofolio, organisasi, MBKM/magang), menghitung capaian CPL/PLO dan skor profil lulusan, mendeteksi gap kompetensi, serta memberikan rekomendasi (MK, karier, topik TA, pembimbing) lengkap dengan penjelasan (explainability).',
  actors: ['Admin Prodi', 'Mahasiswa', 'Dosen PA', 'LPM'],
  testCase: 'Mahasiswa FASILKOM menambah minat, sertifikasi, portofolio, organisasi, dan MBKM; Admin Prodi menghitung ulang profil; mahasiswa melihat skor CPL/PLO, gap kompetensi dan rekomendasi; LPM memantau sebaran profil lulusan.',
};

const tag = Date.now().toString().slice(-4);
const NIM = '2026001001';

run('m6', meta, async (f) => {
  // ---- Admin Prodi: kerangka CPL/PLO ----
  await f.as('admin_prodi');
  await f.clickMenu('Cpls', 'Buka M6 Profiling Mahasiswa > Cpls', 'Capaian Pembelajaran Lulusan (CPL) prodi.');
  await f.shot('Daftar CPL', 'CPL menjadi acuan penilaian kompetensi.');
  await f.clickMenu('Plos', 'Buka Plos', 'Program Learning Outcome.');
  await f.shot('Daftar PLO', 'PLO dipetakan ke CPL.');
  await f.clickMenu('Pemetaan Mk Cpls', 'Buka Pemetaan Mk Cpls', 'Kontribusi mata kuliah terhadap CPL.');
  await f.shot('Pemetaan MK–CPL', 'Bobot kontribusi tiap MK ke CPL — nilai MK mahasiswa dikonversi menjadi skor CPL.');
  await f.clickMenu('Graduate Profiles', 'Buka Graduate Profiles', 'Profil lulusan yang dituju prodi.');
  await f.shot('Profil lulusan', 'Contoh: Software Engineer, Data Analyst.');

  // ---- Mahasiswa: lengkapi data ----
  await f.as('mahasiswa');
  await f.clickMenu('Mahasiswa Profiles', 'Mahasiswa membuka M6 > Mahasiswa Profiles', 'Ringkasan profil akademik & kompetensi.');
  await f.shot('Profil mahasiswa', 'Skor akademik, skor kompetensi, status perhitungan.');

  await f.clickMenu('Mahasiswa Interests', 'Buka Mahasiswa Interests', 'Minat bidang mahasiswa.');
  await f.create('Klik New mahasiswa interest', 'Tambah minat.');
  await f.fill('interest_area', `Cloud Computing ${tag}`);
  await f.select('level', 'High');
  await f.submitForm('Isi minat & tingkat lalu klik Buat', 'Mahasiswa otomatis sesuai akun login.');

  await f.clickMenu('Sertifikasi Mahasiswa', 'Buka Sertifikasi Mahasiswa', 'Sertifikasi yang dimiliki.');
  await f.create('Klik New sertifikasi', 'Tambah sertifikasi.');
  await f.fill('name', `Google Associate Cloud Engineer ${tag}`);
  await f.fill('issuer', 'Google Cloud');
  await f.fill('field', 'Cloud Computing');
  await f.fill('issued_at', '2026-06-15');
  await f.submitForm('Isi data sertifikasi lalu klik Buat', 'Status verifikasi diisi oleh Admin/Dosen PA, bukan oleh mahasiswa.');

  await f.clickMenu('Portofolio Mahasiswa', 'Buka Portofolio Mahasiswa', 'Portofolio proyek.');
  await f.create('Klik New portofolio', 'Tambah portofolio.');
  await f.fill('title', `Dashboard Monitoring Akademik ${tag}`);
  await f.fill('url', 'https://github.com/contoh/dashboard-akademik');
  const tags = f.wrapper('skills_json').locator('input').first();
  for (const t of ['Laravel', 'Filament', 'MySQL']) { await tags.fill(t); await tags.press('Enter'); }
  await f.fill('description', 'Proyek mata kuliah APS: dashboard monitoring KRS dan nilai.');
  await f.submitForm('Isi judul, URL, skill, deskripsi lalu klik Buat', 'Skill dari portofolio dipakai dalam perhitungan kekuatan kompetensi.');

  await f.clickMenu('Organisasi Mahasiswa', 'Buka Organisasi Mahasiswa', 'Pengalaman organisasi.');
  await f.create('Klik New organisasi', 'Tambah organisasi.');
  await f.fill('organization_name', `Himpunan Mahasiswa Informatika ${tag}`);
  await f.fill('role', 'Kepala Divisi Riset');
  await f.fill('start_date', '2025-09-01');
  await f.submitForm('Isi organisasi & peran lalu klik Buat', 'Soft skill/kepemimpinan.');

  await f.clickMenu('MBKM/Magang', 'Buka MBKM/Magang', 'Program MBKM atau magang.');
  await f.create('Klik New MBKM', 'Tambah pengalaman MBKM/magang.');
  await f.fill('program_type', 'Magang Bersertifikat');
  await f.fill('institution', `PT Teknologi Nusantara ${tag}`);
  await f.fill('role', 'Backend Developer Intern');
  await f.fill('field', 'Software Engineering');
  await f.fill('start_date', '2026-02-01');
  await f.fill('end_date', '2026-06-30');
  await f.submitForm('Isi data MBKM lalu klik Buat', 'Pengalaman industri memperkuat profil lulusan.');

  // ---- Admin Prodi: recalculate ----
  await f.as('admin_prodi', { showLogin: false });
  await f.open('/mahasiswa-profiles');
  await f.rowAction('Mahasiswa FASILKOM', 'Recalculate', 'Admin Prodi klik Recalculate', 'Sistem menghitung ulang skor CPL/PLO, profil lulusan, gap kompetensi, dan rekomendasi.');
  await f.shot('Profil terhitung ulang', 'Status calculated dan waktu recalculated terbarui.');

  // ---- Mahasiswa: lihat hasil ----
  await f.as('mahasiswa', { showLogin: false });
  await f.open('/mahasiswa-cpl-scores');
  await f.shot('Skor CPL mahasiswa', 'Capaian per CPL.');
  await f.clickMenu('Skor Profil Lulusan', 'Buka Skor Profil Lulusan', 'Kecocokan terhadap tiap profil lulusan.');
  await f.shot('Skor profil lulusan', 'Persentase kecocokan mahasiswa terhadap profil lulusan prodi.');
  await f.clickMenu('Competency Gaps', 'Buka Competency Gaps', 'Gap kompetensi.');
  await f.shot('Gap kompetensi', 'Kompetensi yang masih kurang dibanding target profil lulusan.');
  await f.clickMenu('Student Recommendations', 'Buka Student Recommendations', 'Rekomendasi personal.');
  await f.shot('Rekomendasi mahasiswa', 'Rekomendasi MK, karier, topik TA, dan pembimbing beserta alasan (explainability).', { fullPage: true });
  await f.clickMenu('Riwayat Rekomendasi', 'Buka Riwayat Rekomendasi', 'Histori rekomendasi.');
  await f.shot('Riwayat rekomendasi', 'Setiap perhitungan ulang tersimpan sebagai histori.');

  // ---- Dosen PA ----
  await f.as('dosen');
  await f.clickMenu('Student Recommendations', 'Dosen PA melihat rekomendasi mahasiswa bimbingan', 'Bahan diskusi saat perwalian.');
  await f.shot('Rekomendasi mahasiswa bimbingan', 'Dosen PA melihat rekomendasi mahasiswa yang dibimbing.');

  // ---- LPM ----
  await f.as('lpm');
  await f.clickMenu('Skor Profil Lulusan', 'LPM membuka Skor Profil Lulusan', 'Monitoring mutu: sebaran ketercapaian profil lulusan.');
  await f.shot('Sebaran profil lulusan (LPM)', 'Data mutu untuk akreditasi (read-only).');
  await f.clickMenu('Mahasiswa Cpl Scores', 'LPM membuka Mahasiswa Cpl Scores', 'Capaian CPL seluruh mahasiswa.');
  await f.shot('Capaian CPL (LPM)', 'Rekap capaian CPL untuk evaluasi kurikulum.');
});
