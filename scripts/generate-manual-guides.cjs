const fs = require('fs');
const path = require('path');

const { chromium } = require('/home/kumadz/.npm/_npx/e41f203b7505f1fb/node_modules/playwright-core');

const root = path.resolve(__dirname, '..');
const outDir = path.join(root, 'docs', 'manual-guides');
const screenshotDir = path.join(outDir, 'screenshots');
const pdfDir = path.join(outDir, 'pdf');

const accounts = {
  superadmin: {
    login: 'https://sifakueu.test/admin/login',
    email: 'admin@admin.com',
    panel: 'Super Admin',
  },
  admin: {
    login: 'https://fasilkom.sifakueu.test/admin/login',
    email: 'admin.fasilkom@sifak.local',
    panel: 'Admin Tenant',
  },
  dosen: {
    login: 'https://fasilkom.sifakueu.test/dosen/login',
    email: 'dosen.fasilkom@sifak.local',
    panel: 'Dosen',
  },
  mahasiswa: {
    login: 'https://fasilkom.sifakueu.test/mahasiswa/login',
    email: 'mahasiswa.fasilkom@sifak.local',
    panel: 'Mahasiswa',
  },
  pimpinan: {
    login: 'https://fasilkom.sifakueu.test/pimpinan/login',
    email: 'kaprodi.fasilkom@sifak.local',
    panel: 'Pimpinan',
  },
};

const manuals = [
  {
    id: '00-platform-multitenant',
    title: 'Manual Guide Platform dan Multi-Tenant',
    module: 'Platform Multi-Tenant',
    account: 'superadmin',
    path: '/admin/tenants',
    fallbackScreenshot: 'docs/screenshots/qa/06-superadmin-dashboard.png',
    purpose: 'Memandu Super Admin membuat dan memantau tenant/fakultas sehingga setiap tenant memakai domain dan database operasional sendiri.',
    flow: ['Login Super Admin', 'Buka Fakultas / Tenant', 'Isi data tenant', 'Generate database/domain', 'Seed aktor awal', 'Admin tenant mulai bekerja'],
    caseTitle: 'Case: membuat tenant fakultas baru',
    steps: [
      'Login melalui panel Super Admin.',
      'Buka menu Fakultas / Tenant.',
      'Klik Create dan isi nama fakultas, slug, domain, serta konfigurasi database.',
      'Simpan tenant lalu jalankan provisioning agar database tenant, role, dan user awal disiapkan.',
      'Akses tenant melalui subdomain yang terbentuk, misalnya https://fasilkom.sifakueu.test/admin/login.',
    ],
    result: 'Tenant aktif, metadata tersimpan di database pusat, dan operasional tenant berjalan di database tenant.',
  },
  {
    id: '00-master-data',
    title: 'Manual Guide Master Data',
    module: 'Master Data',
    account: 'admin',
    path: '/admin/fakultas',
    fallbackScreenshot: 'docs/screenshots/qa/07-admin-tenant-dashboard.png',
    purpose: 'Memandu Admin Tenant mengelola data dasar akademik yang dipakai oleh seluruh modul.',
    flow: ['Login Admin Tenant', 'Lengkapi fakultas/prodi', 'Atur tahun akademik', 'Kelola kurikulum dan MK', 'Kelola dosen/mahasiswa', 'Data dipakai modul M1-M7'],
    caseTitle: 'Case: menyiapkan data dasar program studi',
    steps: [
      'Login sebagai Admin Tenant.',
      'Buka master Fakultas, Program Studi, Tahun Akademik, Semester, Kurikulum, Mata Kuliah, Dosen, Mahasiswa, dan Ruangan.',
      'Pastikan relasi program studi, kurikulum, mata kuliah, dosen, dan mahasiswa sudah valid.',
      'Gunakan data ini sebagai referensi untuk KRS, Sidang, Surat, Monitoring, Profiling, dan Repository.',
    ],
    result: 'Dataset tenant siap menjadi sumber data untuk seluruh proses akademik.',
  },
  {
    id: '00-shared-services',
    title: 'Manual Guide Shared Services',
    module: 'Shared Services',
    account: 'admin',
    path: '/admin/approval-flows',
    fallbackScreenshot: 'docs/screenshots/qa/07-admin-tenant-dashboard.png',
    purpose: 'Memandu penggunaan layanan bersama seperti approval flow, audit, notifikasi, file storage, dan scheduled log.',
    flow: ['Definisikan approval flow', 'Tambahkan approval step', 'Modul membuat request', 'Approval diproses', 'Audit/notifikasi/file tercatat', 'Status dikembalikan ke modul'],
    caseTitle: 'Case: membuat alur approval surat',
    steps: [
      'Login sebagai Admin Tenant.',
      'Buka menu Approval Flow dan Approval Step.',
      'Buat flow sesuai kebutuhan modul, contohnya request surat mahasiswa.',
      'Tambahkan urutan approver dan aturan status.',
      'Saat modul memakai flow, sistem mencatat workflow history, audit log, dan notifikasi.',
    ],
    result: 'Proses approval menjadi konsisten dan bisa dipakai lintas modul.',
  },
  {
    id: 'm1-sidang-sempro-ta',
    title: 'Manual Guide M1 Sidang Sempro dan TA',
    module: 'M1 Sidang Sempro dan TA',
    account: 'mahasiswa',
    path: '/mahasiswa/sidang-registrations',
    fallbackScreenshot: 'docs/screenshots/qa/09-mahasiswa-dashboard.png',
    purpose: 'Memandu proses pendaftaran, verifikasi, penjadwalan, penilaian, dan hasil sidang.',
    flow: ['Mahasiswa daftar sidang', 'Upload syarat', 'Admin/Dosen verifikasi', 'Jadwal dan penguji ditetapkan', 'Dosen memberi nilai', 'Hasil dan revisi tercatat'],
    caseTitle: 'Case: mahasiswa mendaftar sidang TA',
    steps: [
      'Login sebagai Mahasiswa.',
      'Buka menu M1 Sidang > Pendaftaran Sidang atau Sidang Registrations.',
      'Pilih jenis sidang dan lengkapi data tugas akhir beserta berkas persyaratan.',
      'Admin atau dosen memverifikasi syarat.',
      'Setelah jadwal dan penguji ditetapkan, dosen mengisi rubric dan score.',
      'Mahasiswa melihat hasil, revisi, dan berita acara.',
    ],
    result: 'Pendaftaran sidang memiliki status, jadwal, assignment penguji, nilai, revisi, dan minutes.',
  },
  {
    id: 'm2-surat-menyurat',
    title: 'Manual Guide M2 Surat Menyurat',
    module: 'M2 Surat Menyurat',
    account: 'mahasiswa',
    path: '/mahasiswa/surats',
    fallbackScreenshot: 'docs/screenshots/qa/09-mahasiswa-dashboard.png',
    purpose: 'Memandu pengajuan surat, approval, penomoran, generate dokumen, distribusi, arsip, dan verifikasi publik.',
    flow: ['Pemohon mengajukan surat', 'Isi field template', 'Approval berjalan', 'Nomor surat dibuat', 'Dokumen digenerate', 'Surat didistribusikan dan diverifikasi'],
    caseTitle: 'Case: mahasiswa mengajukan surat aktif kuliah',
    steps: [
      'Login sebagai Mahasiswa.',
      'Buka menu M2 Surat > Surats.',
      'Klik Create dan pilih jenis surat.',
      'Isi field sesuai template surat.',
      'Kirim request untuk diproses approval.',
      'Setelah disetujui, sistem membuat nomor, dokumen generated, distribusi, arsip, dan token verifikasi.',
    ],
    result: 'Surat resmi dapat diunduh, diarsipkan, dan divalidasi melalui halaman verifikasi.',
  },
  {
    id: 'm3-monitoring-alert',
    title: 'Manual Guide M3 Monitoring dan Alert',
    module: 'M3 Monitoring dan Alert',
    account: 'pimpinan',
    path: '/pimpinan/alerts',
    fallbackScreenshot: 'docs/screenshots/qa/10-pimpinan-dashboard.png',
    purpose: 'Memandu pemantauan risiko akademik, alert, follow-up, eskalasi, dan override.',
    flow: ['Rule monitoring disiapkan', 'Snapshot akademik dihitung', 'Indicator result terbentuk', 'Alert muncul', 'Follow-up dilakukan', 'Eskalasi/override bila perlu'],
    caseTitle: 'Case: pimpinan menindaklanjuti mahasiswa risiko kuning',
    steps: [
      'Login sebagai Pimpinan.',
      'Buka menu M3 Monitoring > Alerts.',
      'Filter alert berdasarkan severity atau status.',
      'Buka alert mahasiswa yang membutuhkan perhatian.',
      'Tambahkan follow-up, eskalasi, atau override sesuai keputusan.',
      'Pantau perubahan status pada dashboard monitoring.',
    ],
    result: 'Risiko akademik terlihat, ditindaklanjuti, dan memiliki histori penanganan.',
  },
  {
    id: 'm4-krs-penjadwalan',
    title: 'Manual Guide M4 KRS dan Penjadwalan',
    module: 'M4 KRS dan Penjadwalan',
    account: 'mahasiswa',
    path: '/mahasiswa/krs',
    fallbackScreenshot: 'docs/screenshots/qa/09-mahasiswa-dashboard.png',
    purpose: 'Memandu proses periode KRS, pemilihan mata kuliah, validasi dosen PA/admin, plotting dosen, jadwal, dan konflik.',
    flow: ['Admin buka periode KRS', 'Penawaran MK dibuat', 'Mahasiswa isi KRS', 'Dosen PA/Admin validasi', 'Jadwal dan plotting ditetapkan', 'Konflik dicatat'],
    caseTitle: 'Case: mahasiswa mengisi KRS semester aktif',
    steps: [
      'Login sebagai Mahasiswa.',
      'Buka menu M4 KRS & Jadwal > KRS.',
      'Klik Create atau buka KRS aktif.',
      'Pilih kelas/mata kuliah yang tersedia.',
      'Simpan pengajuan KRS.',
      'Dosen PA atau admin melakukan validasi, lalu jadwal kuliah dapat dipantau.',
    ],
    result: 'KRS tervalidasi dan jadwal kuliah mahasiswa tersedia.',
  },
  {
    id: 'm5-profiling-dosen',
    title: 'Manual Guide M5 Profiling Dosen',
    module: 'M5 Profiling Dosen',
    account: 'dosen',
    path: '/dosen/dosen-profils',
    fallbackScreenshot: 'docs/screenshots/qa/08-dosen-dashboard.png',
    purpose: 'Memandu pengelolaan profil dosen, pendidikan, sertifikasi, publikasi, preferensi mengajar, beban, gap, dan rekomendasi pengampu.',
    flow: ['Dosen lengkapi profil', 'Isi pendidikan/sertifikasi/publikasi', 'Tetapkan preferensi MK', 'Sistem membaca kompetensi', 'Gap dihitung', 'Rekomendasi pengampu tersedia'],
    caseTitle: 'Case: dosen memperbarui profil kompetensi',
    steps: [
      'Login sebagai Dosen.',
      'Buka menu M5 Profiling Dosen > Dosen Profils.',
      'Lengkapi pendidikan, sertifikasi, publikasi, pengalaman industri, preferensi MK, dan lokasi.',
      'Admin atau pimpinan dapat melihat beban, gap kompetensi, dan rekomendasi pengampu.',
    ],
    result: 'Profil dosen menjadi dasar plotting pengampu dan evaluasi kompetensi.',
  },
  {
    id: 'm6-profiling-mahasiswa',
    title: 'Manual Guide M6 Profiling Mahasiswa',
    module: 'M6 Profiling Mahasiswa',
    account: 'mahasiswa',
    path: '/mahasiswa/mahasiswa-profiles',
    fallbackScreenshot: 'docs/screenshots/qa/09-mahasiswa-dashboard.png',
    purpose: 'Memandu pengelolaan profil mahasiswa, minat, portofolio, sertifikasi, organisasi, MBKM, skor CPL/PLO, gap, dan rekomendasi.',
    flow: ['Mahasiswa lengkapi profil', 'Input minat dan portofolio', 'Input sertifikasi/MBKM', 'CPL/PLO dievaluasi', 'Gap kompetensi dihitung', 'Rekomendasi diberikan'],
    caseTitle: 'Case: mahasiswa melengkapi profil karier',
    steps: [
      'Login sebagai Mahasiswa.',
      'Buka menu M6 Profiling Mahasiswa > Mahasiswa Profiles.',
      'Lengkapi profil, minat, portofolio, sertifikasi, organisasi, dan MBKM/magang.',
      'Sistem atau admin mencatat skor CPL/PLO dan profil lulusan.',
      'Mahasiswa melihat rekomendasi akademik atau karier.',
    ],
    result: 'Profil mahasiswa dapat dipakai untuk rekomendasi, monitoring, dan pemetaan capaian.',
  },
  {
    id: 'm7-dokumen-ta-repository',
    title: 'Manual Guide M7 Dokumen TA dan Repository',
    module: 'M7 Dokumen TA dan Repository',
    account: 'mahasiswa',
    path: '/mahasiswa/ta-documents',
    fallbackScreenshot: 'docs/screenshots/qa/09-mahasiswa-dashboard.png',
    purpose: 'Memandu pengelolaan dokumen TA, versi, section, komentar, review, approval, revisi, dan publish repository.',
    flow: ['Mahasiswa upload dokumen', 'Versi dokumen tersimpan', 'Dosen review dan komentar', 'Approval/revisi berjalan', 'Finalisasi dokumen', 'Publish repository'],
    caseTitle: 'Case: mahasiswa mengunggah draft TA',
    steps: [
      'Login sebagai Mahasiswa.',
      'Buka menu M7 Dokumen TA > TA Documents.',
      'Upload draft dokumen sesuai section atau versi.',
      'Dosen pembimbing memberi review dan komentar.',
      'Mahasiswa melakukan revisi dan upload versi baru.',
      'Setelah disetujui, dokumen final masuk Repository Items.',
    ],
    result: 'Dokumen TA memiliki histori versi, review, approval, progress, dan item repository final.',
  },
];

function ensureDirs() {
  fs.mkdirSync(screenshotDir, { recursive: true });
  fs.mkdirSync(pdfDir, { recursive: true });
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function fileUrl(filePath) {
  return `file://${filePath}`;
}

function htmlForManual(manual, screenshotPath) {
  const account = accounts[manual.account];
  const flow = manual.flow
    .map((item, index) => `<div class="flow-step"><strong>${index + 1}</strong><span>${escapeHtml(item)}</span></div>`)
    .join('<div class="arrow">→</div>');

  const steps = manual.steps
    .map((step, index) => `<li><strong>${index + 1}.</strong> ${escapeHtml(step)}</li>`)
    .join('');

  return `<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>${escapeHtml(manual.title)}</title>
  <style>
    @page { size: A4; margin: 16mm 14mm; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: #0f172a;
      background: #f8fafc;
      line-height: 1.5;
      font-size: 12px;
    }
    .page {
      background: #fff;
      border: 1px solid #dbeafe;
      border-radius: 10px;
      padding: 24px;
      min-height: 100%;
    }
    .eyebrow {
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #0369a1;
      font-weight: 800;
      font-size: 10px;
    }
    h1 {
      margin: 6px 0 10px;
      font-size: 26px;
      line-height: 1.12;
    }
    h2 {
      margin: 22px 0 8px;
      font-size: 16px;
      color: #0f766e;
    }
    p { margin: 0 0 10px; }
    .meta {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin: 16px 0;
    }
    .meta div, .result, .note {
      border: 1px solid #dbeafe;
      border-radius: 8px;
      padding: 10px;
      background: #f8fbff;
    }
    .meta b {
      display: block;
      font-size: 10px;
      color: #64748b;
      text-transform: uppercase;
      margin-bottom: 3px;
    }
    .flow {
      display: flex;
      align-items: stretch;
      gap: 6px;
      margin: 10px 0 14px;
    }
    .flow-step {
      flex: 1;
      min-height: 70px;
      display: flex;
      flex-direction: column;
      gap: 5px;
      justify-content: center;
      border: 1px solid #99f6e4;
      border-radius: 8px;
      padding: 9px;
      background: linear-gradient(180deg, #ecfeff, #ffffff);
    }
    .flow-step strong {
      width: 22px;
      height: 22px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      background: #0f766e;
      color: #fff;
      font-size: 11px;
    }
    .arrow {
      display: flex;
      align-items: center;
      color: #0284c7;
      font-weight: 900;
      font-size: 16px;
    }
    ol {
      padding: 0;
      margin: 10px 0 0;
      list-style: none;
      display: grid;
      gap: 7px;
    }
    li {
      border-left: 3px solid #38bdf8;
      background: #f8fafc;
      padding: 8px 10px;
      border-radius: 6px;
    }
    .screenshot {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      display: block;
      margin-top: 10px;
    }
    .result {
      background: #ecfdf5;
      border-color: #99f6e4;
      color: #134e4a;
    }
    .footer {
      margin-top: 18px;
      font-size: 10px;
      color: #64748b;
      display: flex;
      justify-content: space-between;
      border-top: 1px solid #e2e8f0;
      padding-top: 10px;
    }
  </style>
</head>
<body>
  <main class="page">
    <div class="eyebrow">SIFAK Manual Guide</div>
    <h1>${escapeHtml(manual.title)}</h1>
    <p>${escapeHtml(manual.purpose)}</p>

    <section class="meta">
      <div><b>Modul</b>${escapeHtml(manual.module)}</div>
      <div><b>Panel</b>${escapeHtml(account.panel)}</div>
      <div><b>Akun Demo</b>${escapeHtml(account.email)} / password</div>
    </section>

    <h2>Alur Proses</h2>
    <section class="flow">${flow}</section>

    <h2>${escapeHtml(manual.caseTitle)}</h2>
    <ol>${steps}</ol>

    <h2>Output yang Diharapkan</h2>
    <div class="result">${escapeHtml(manual.result)}</div>

    <h2>Screenshot Halaman</h2>
    <p class="note">Screenshot di bawah diambil dari environment lokal tenant FASILKOM sebagai contoh alur penggunaan modul.</p>
    <img class="screenshot" src="${fileUrl(screenshotPath)}" alt="Screenshot ${escapeHtml(manual.module)}">

    <div class="footer">
      <span>Generated: 9 Oktober 2026</span>
      <span>SIFAK Multi-Tenant Local QA</span>
    </div>
  </main>
</body>
</html>`;
}

async function login(page, account) {
  await page.goto(account.login, { waitUntil: 'networkidle', timeout: 45000 });
  await page.getByLabel(/email/i).fill(account.email);
  await page.getByLabel(/password/i).fill('password');
  await page.getByRole('button', { name: /sign in|masuk|log in/i }).click();
  await page.waitForFunction(() => !location.pathname.endsWith('/login'), null, { timeout: 45000 });
  await page.waitForLoadState('networkidle', { timeout: 20000 }).catch(() => {});
}

async function captureModuleScreenshot(browser, manual) {
  const account = accounts[manual.account];
  const base = new URL(account.login).origin;
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1440, height: 1000 },
  });
  const page = await context.newPage();

  await login(page, account);
  const url = `${base}${manual.path}`;
  const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
  await page.waitForLoadState('networkidle', { timeout: 20000 }).catch(() => {});
  await page.waitForTimeout(4500);

  const status = response ? response.status() : 0;
  const body = await page.locator('body').innerText().catch(() => '');
  if (status >= 400 || /Forbidden|Internal Server Error|Server Error/i.test(body)) {
    throw new Error(`${manual.id} failed at ${url} with status ${status}`);
  }

  const screenshotPath = path.join(screenshotDir, `${manual.id}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  await context.close();

  return screenshotPath;
}

function copyFallbackScreenshot(manual) {
  const fallbackPath = path.join(root, manual.fallbackScreenshot);
  if (!fs.existsSync(fallbackPath)) {
    throw new Error(`Missing fallback screenshot for ${manual.id}: ${manual.fallbackScreenshot}`);
  }

  const screenshotPath = path.join(screenshotDir, `${manual.id}.png`);
  fs.copyFileSync(fallbackPath, screenshotPath);

  return screenshotPath;
}

async function printManualPdf(browser, manual, screenshotPath) {
  const page = await browser.newPage({ viewport: { width: 1240, height: 1754 } });
  await page.setContent(htmlForManual(manual, screenshotPath), { waitUntil: 'networkidle' });
  const pdfPath = path.join(pdfDir, `${manual.id}.pdf`);
  await page.pdf({
    path: pdfPath,
    format: 'A4',
    printBackground: true,
    margin: { top: '10mm', right: '10mm', bottom: '10mm', left: '10mm' },
  });
  await page.close();
  return pdfPath;
}

async function main() {
  ensureDirs();
  const offline = process.argv.includes('--offline');
  const browser = await chromium.launch({
    headless: true,
    executablePath: '/usr/bin/google-chrome',
  });

  const generated = [];
  try {
    for (const manual of manuals) {
      const screenshotPath = offline
        ? copyFallbackScreenshot(manual)
        : await captureModuleScreenshot(browser, manual);
      const pdfPath = await printManualPdf(browser, manual, screenshotPath);
      generated.push(pdfPath);
      console.log(`Generated ${path.relative(root, pdfPath)}`);
    }
  } finally {
    await browser.close();
  }

  console.log(`Generated ${generated.length} manual guide PDFs.`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
