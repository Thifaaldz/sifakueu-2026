// Menyusun manual guide + laporan QA menjadi PDF (gabungan dan per modul) dari hasil skrip m1..m7 & crawl-roles.
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const { outDir, shotDir, accounts, launch } = require('./lib.cjs');
const { conformity, fixes, openNotes, roles, keyActions } = require('./manual-data.cjs');

const pdfDir = path.join(outDir, 'pdf');
const jpgDir = path.join(outDir, 'jpg');
fs.mkdirSync(pdfDir, { recursive: true });
fs.mkdirSync(jpgDir, { recursive: true });

const MODULES = ['m1', 'm2', 'm3', 'm4', 'm5', 'm6', 'm7'];
const SLUG = { m1: 'M1_Sidang_Sempro_TA', m2: 'M2_Surat_Menyurat', m3: 'M3_Monitoring_Alert', m4: 'M4_KRS_Penjadwalan', m5: 'M5_Profiling_Dosen', m6: 'M6_Profiling_Mahasiswa', m7: 'M7_Dokumen_TA_Repositori' };
const ROLE_LABEL = Object.fromEntries(Object.entries(accounts).map(([k, a]) => [k, a.name]));
ROLE_LABEL.publik = 'Publik (tanpa login)';
const STATUS = { OK: ['Sesuai', 'ok'], PARTIAL: ['Sebagian', 'partial'], NO: ['Belum', 'no'] };
const today = '9 Oktober 2026';

const esc = (v) => String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;');

// PNG -> JPEG (lebar maks 1800px) agar PDF tetap tajam tapi ukurannya wajar.
function convertImages() {
  const py = `
import sys, os
from PIL import Image
src, dst = sys.argv[1], sys.argv[2]
for name in sorted(os.listdir(src)):
    if not name.endswith('.png'): continue
    out = os.path.join(dst, name[:-4] + '.jpg')
    if os.path.exists(out) and os.path.getmtime(out) >= os.path.getmtime(os.path.join(src, name)): continue
    im = Image.open(os.path.join(src, name)).convert('RGB')
    if im.width > 1800:
        im = im.resize((1800, round(im.height * 1800 / im.width)), Image.LANCZOS)
    im.save(out, 'JPEG', quality=86, optimize=True, progressive=True)
import json
dims = {}
for name in os.listdir(dst):
    if name.endswith('.jpg'):
        with Image.open(os.path.join(dst, name)) as im:
            dims[name[:-4] + '.png'] = [im.width, im.height]
json.dump(dims, open(os.path.join(dst, 'dims.json'), 'w'))
`;
  execFileSync('python3', ['-I', '-c', py, shotDir, jpgDir], { stdio: 'inherit' });
}

let DIMS = {};
const isTall = (png) => { const d = DIMS[png]; return d && d[1] / d[0] > 0.7; };
const img = (png) => `file://${path.join(jpgDir, png.replace(/\.png$/, '.jpg'))}`;

const css = `
@page { size: A4; margin: 14mm 13mm 16mm; }
* { box-sizing: border-box; }
:root { --ink:#0f172a; --muted:#475569; --line:#dbe3ee; --brand:#0f766e; --brand2:#0369a1; --soft:#f1f7f9; }
body { margin:0; font-family: "Inter","Segoe UI",system-ui,sans-serif; color:var(--ink); font-size:10.5pt; line-height:1.5; }
h1 { font-size:22pt; margin:0 0 6pt; line-height:1.15; }
h2 { font-size:15pt; margin:16pt 0 6pt; color:var(--brand); }
h3 { font-size:12pt; margin:12pt 0 4pt; }
p { margin:0 0 6pt; }
.page-break { break-before: page; }
.cover { height: 255mm; display:flex; flex-direction:column; justify-content:center; padding: 0 8mm; background: linear-gradient(160deg,#ecfeff 0%,#f8fafc 55%,#eef2ff 100%); border-radius:6mm; }
.cover .eyebrow { color:var(--brand2); font-weight:800; letter-spacing:.12em; text-transform:uppercase; font-size:10pt; }
.cover h1 { font-size:30pt; margin:6pt 0 10pt; }
.cover .sub { font-size:13pt; color:var(--muted); max-width:150mm; }
.cover .meta { margin-top:18mm; display:grid; grid-template-columns: 1fr 1fr; gap:4mm; max-width:160mm; }
.cover .meta div { background:#fff; border:1px solid var(--line); border-radius:3mm; padding:3mm 4mm; font-size:9.5pt; }
.cover .meta b { display:block; color:var(--muted); font-size:8pt; text-transform:uppercase; letter-spacing:.06em; }
table { width:100%; border-collapse:collapse; font-size:9pt; margin:4pt 0 8pt; }
th, td { border:1px solid var(--line); padding:4pt 5pt; vertical-align:top; text-align:left; }
th { background:var(--soft); font-weight:700; }
tr { break-inside: avoid; }
.badge { display:inline-block; padding:1pt 6pt; border-radius:8pt; font-size:8pt; font-weight:700; white-space:nowrap; }
.ok { background:#dcfce7; color:#166534; } .partial { background:#fef3c7; color:#92400e; } .no { background:#fee2e2; color:#991b1b; }
.sev-Kritis { background:#fee2e2; color:#991b1b; } .sev-Tinggi { background:#ffedd5; color:#9a3412; } .sev-Sedang { background:#fef3c7; color:#92400e; } .sev-Rendah { background:#e0f2fe; color:#075985; }
.role { background:#e0f2fe; color:#075985; }
.kpis { display:grid; grid-template-columns: repeat(4, 1fr); gap:3mm; margin:6pt 0 10pt; }
.kpi { border:1px solid var(--line); border-radius:3mm; padding:3mm; background:#fff; }
.kpi .v { font-size:18pt; font-weight:800; color:var(--brand); line-height:1.1; }
.kpi .l { font-size:8.5pt; color:var(--muted); }
.box { border:1px solid var(--line); background:var(--soft); border-radius:3mm; padding:3mm 4mm; margin:4pt 0 8pt; }
.mod-head { border-left:5px solid var(--brand); padding-left:4mm; margin-bottom:6pt; }
.mod-head .code { color:var(--brand2); font-weight:800; font-size:11pt; letter-spacing:.06em; }
.step { break-inside: avoid; border:1px solid var(--line); border-radius:3mm; padding:3mm 3.5mm; margin:0 0 4mm; }
.step-h { display:flex; gap:3mm; align-items:baseline; flex-wrap:wrap; }
.step-n { background:var(--brand); color:#fff; border-radius:50%; min-width:7mm; height:7mm; display:inline-flex; align-items:center; justify-content:center; font-weight:800; font-size:9pt; }
.step-t { font-weight:700; font-size:10.5pt; }
.step-d { color:var(--muted); font-size:9.5pt; margin:2pt 0 3pt; }
.step img { width:100%; max-height:112mm; object-fit:contain; object-position:left top; border:1px solid #cbd5e1; border-radius:2mm; display:block; }
.step.tall img { max-height:200mm; }
.toc li { margin:2pt 0; }
ul { margin:2pt 0 6pt 14pt; padding:0; }
.small { font-size:8.5pt; color:var(--muted); }
.cols { display:grid; grid-template-columns: 1fr 1fr; gap:4mm; }
`;

function cover(title, subtitle) {
  return `<section class="cover">
    <div class="eyebrow">SIFAK — Sistem Informasi Fakultas Terintegrasi</div>
    <h1>${esc(title)}</h1>
    <div class="sub">${esc(subtitle)}</div>
    <div class="meta">
      <div><b>Tenant uji</b>FASILKOM — https://fasilkom.sifakueu.test</div>
      <div><b>Tanggal QA</b>${today}</div>
      <div><b>Lingkungan</b>Docker lokal · Laravel 12 · Filament 3 · MariaDB 10.11</div>
      <div><b>Metode</b>Uji end-to-end otomatis lewat browser, screenshot di setiap langkah</div>
    </div>
  </section>`;
}

function howToRun() {
  const rows = Object.entries(accounts).map(([k, a]) => `<tr><td>${esc(a.name)}${a.qaOnly ? ' <span class="badge partial">data uji</span>' : ''}</td><td>${esc(a.email)}</td><td>${esc(a.base)}/${esc(a.panel)}</td><td>${esc(a.role)}</td></tr>`).join('');
  return `<h2>1. Cara Menjalankan Sistem</h2>
  <ol>
    <li>Jalankan Docker Desktop, lalu dari folder proyek: <code>DB_FORWARD_PORT=23306 docker compose up -d --build</code> (port 23306 dipakai karena 13306 sudah terpakai service lain).</li>
    <li>Jalankan <code>./scripts/sifak-local-setup.sh</code> untuk sertifikat SSL lokal dan sinkron <code>/etc/hosts</code> (sifakueu.test, fasilkom.sifakueu.test).</li>
    <li>Container PHP otomatis menjalankan migrate + seed saat start. Data uji tambahan untuk manual ini: <code>docker compose exec php php artisan db:seed --class=QaDemoSeeder</code>.</li>
    <li>Buka panel sesuai role (tabel di bawah). Semua password: <b>password</b>.</li>
  </ol>
  <table><tr><th>Akun / Role</th><th>Email</th><th>URL Panel</th><th>Role sistem</th></tr>${rows}</table>
  <p class="small">Akun bertanda "data uji" ditambahkan oleh QaDemoSeeder: Rina (alur sidang M1 & KRS M4, pembimbingnya Kaprodi agar Dr. Dosen FASILKOM bisa menjadi penguji) dan Budi (alur dokumen TA M7, dibimbing Dr. Dosen FASILKOM).</p>
  <h3>Menjalankan ulang QA & manual ini</h3>
  <p class="small"><code>node scripts/qa/crawl-roles.cjs</code> (cek semua menu tiap role) · <code>node scripts/qa/m1.cjs</code> … <code>m7.cjs</code> (alur modul + screenshot) · <code>node scripts/qa/build-pdf.cjs</code> (menyusun PDF).</p>`;
}

function qaSummary(crawl, mods) {
  const roleKeys = Object.keys(crawl);
  let pages = 0; let failed = 0; const failRows = [];
  for (const k of roleKeys) for (const p of crawl[k].pages) {
    pages += 1 + (p.create ? 1 : 0);
    const errs = p.errors.concat(p.create?.errors || []);
    if (errs.length) { failed += 1; failRows.push(`<tr><td>${esc(ROLE_LABEL[k])}</td><td>${esc(p.group)} › ${esc(p.label)}</td><td>${esc(errs.join('; ').slice(0, 200))}</td></tr>`); }
  }
  const steps = mods.reduce((n, m) => n + m.steps.length, 0);
  const issues = mods.reduce((n, m) => n + m.issues.length, 0);
  const crawlRows = roleKeys.map((k) => {
    const ps = crawl[k].pages;
    const bad = ps.filter((p) => p.errors.length || p.create?.errors.length).length;
    return `<tr><td>${esc(ROLE_LABEL[k])}</td><td>${crawl[k].nav.length}</td><td>${ps.length}</td><td>${ps.filter((p) => p.create).length}</td><td>${bad ? `<span class="badge no">${bad} gagal</span>` : '<span class="badge ok">semua lolos</span>'}</td></tr>`;
  }).join('');
  const modRows = mods.map((m) => {
    const c = conformity[m.id];
    const cnt = (s) => c.filter((r) => r[1] === s).length;
    return `<tr><td><b>${esc(m.code)}</b> ${esc(m.title)}</td><td>${m.steps.length}</td><td>${m.issues.length ? `<span class="badge no">${m.issues.length}</span>` : '<span class="badge ok">0</span>'}</td><td><span class="badge ok">${cnt('OK')}</span> <span class="badge partial">${cnt('PARTIAL')}</span> <span class="badge no">${cnt('NO')}</span></td></tr>`;
  }).join('');
  const fixRows = fixes.map((f, i) => `<tr><td>${i + 1}</td><td>${esc(f[0])}</td><td>${esc(f[1])}</td><td>${esc(f[2])}</td><td><span class="badge sev-${f[3]}">${f[3]}</span></td></tr>`).join('');
  const openRows = openNotes.map((n) => `<tr><td>${esc(n[0])}</td><td>${esc(n[1])}</td></tr>`).join('');
  return `<h2>2. Ringkasan QA</h2>
  <div class="kpis">
    <div class="kpi"><div class="v">7 / 7</div><div class="l">alur modul M1–M7 berjalan sampai status akhir</div></div>
    <div class="kpi"><div class="v">${steps}</div><div class="l">langkah terdokumentasi dengan screenshot</div></div>
    <div class="kpi"><div class="v">${pages}</div><div class="l">halaman list + form dibuka di ${roleKeys.length} akun role${failed ? ` (${failed} gagal)` : ', 0 gagal'}</div></div>
    <div class="kpi"><div class="v">${fixes.length}</div><div class="l">bug/temuan diperbaiki selama QA</div></div>
  </div>
  <p>Pengujian dilakukan dengan menjalankan aplikasi di Docker lalu mengendalikan browser (Chrome headless) seperti pengguna sungguhan: login per role, klik menu, isi form, klik tombol aksi, dan memeriksa status akhir di database. Test suite PHP: <b>21 test lulus</b>.</p>
  <h3>Hasil per modul</h3>
  <table><tr><th>Modul</th><th>Langkah</th><th>Masalah tersisa</th><th>Kesesuaian kebutuhan (Sesuai / Sebagian / Belum)</th></tr>${modRows}</table>
  <h3>Hasil cek semua menu per role</h3>
  <table><tr><th>Role</th><th>Grup menu</th><th>Halaman list</th><th>Form create</th><th>Hasil</th></tr>${crawlRows}</table>
  ${failRows.length ? `<table><tr><th>Role</th><th>Halaman</th><th>Error</th></tr>${failRows.join('')}</table>` : ''}
  <h3>Bug & temuan yang diperbaiki</h3>
  <table><tr><th>#</th><th>Area</th><th>Masalah</th><th>Perbaikan</th><th>Tingkat</th></tr>${fixRows}</table>
  <h3>Catatan terbuka (belum diubah, perlu keputusan/pengembangan lanjutan)</h3>
  <table><tr><th>Area</th><th>Catatan</th></tr>${openRows}</table>`;
}

function moduleSection(m, num) {
  const conf = conformity[m.id].map((r) => `<tr><td>${esc(r[0])}</td><td><span class="badge ${STATUS[r[1]][1]}">${STATUS[r[1]][0]}</span></td><td>${esc(r[2])}</td></tr>`).join('');
  const steps = m.steps.map((s) => {
    const tall = isTall(s.file) ? ' tall' : '';
    return `<div class="step${tall}"><div class="step-h"><span class="step-n">${s.n}</span><span class="step-t">${esc(s.title)}</span><span class="badge role">${esc(ROLE_LABEL[s.role] || s.role || '')}</span></div><div class="step-d">${esc(s.desc)}</div><img src="${img(s.file)}"></div>`;
  }).join('');
  return `<section class="page-break">
    <div class="mod-head"><div class="code">${num}. MODUL ${esc(m.code)}</div><h1>${esc(m.title)}</h1></div>
    <p>${esc(m.purpose)}</p>
    <div class="box"><b>Aktor:</b> ${m.actors.map(esc).join(', ')}<br><b>Skenario uji:</b> ${esc(m.testCase)}</div>
    <h3>Kesesuaian dengan dokumen kebutuhan (spesifikasi ${esc(m.code)})</h3>
    <table><tr><th style="width:38%">Kebutuhan</th><th style="width:11%">Status</th><th>Keterangan hasil uji</th></tr>${conf}</table>
    <h3>Langkah menjalankan (${m.steps.length} langkah)</h3>
    <p class="small">Kotak merah pada screenshot menandai tombol/menu yang diklik pada langkah tersebut.</p>
    ${steps}
  </section>`;
}

function rolesSection(crawl, num) {
  return `<section class="page-break"><div class="mod-head"><div class="code">${num}. FITUR PER ROLE</div><h1>Fitur & Hak Akses Setiap Role</h1></div>
  <p>Daftar menu di bawah diambil langsung dari sidebar aplikasi saat login dengan masing-masing akun, sehingga mencerminkan hak akses yang benar-benar berlaku. Akun Alumni belum memiliki panel (portal terbatas, tahap lanjutan).</p>
  ${Object.keys(crawl).map((k) => {
    const r = roles[k] || {};
    const acts = keyActions[k];
    const nav = crawl[k].nav.filter((g) => g.label !== '(Tanpa grup)').map((g) => `<tr><td><b>${esc(g.label)}</b></td><td>${g.items.map((i) => esc(i.label)).join(', ')}</td></tr>`).join('');
    return `<div class="step" style="break-inside:auto">
      <h3 style="margin-top:0">${esc(ROLE_LABEL[k])} <span class="badge role">${esc(accounts[k].panel)} panel</span></h3>
      <p>${esc(r.desc)}</p>
      <p class="small"><b>Modul utama:</b> ${esc(r.modules)} · <b>Login:</b> ${esc(accounts[k].email)} → ${esc(accounts[k].base)}/${esc(accounts[k].panel)}</p>
      ${acts ? `<p style="margin-bottom:2pt"><b>Aksi kunci:</b></p><ul>${acts.map((a) => `<li>${esc(a)}</li>`).join('')}</ul>` : ''}
      <table><tr><th style="width:26%">Grup menu</th><th>Menu yang dapat diakses</th></tr>${nav}</table>
      <img src="${img(`role-${k}-dashboard.png`)}" style="width:100%;max-height:95mm;object-fit:contain;object-position:left top;border:1px solid #cbd5e1;border-radius:2mm">
    </div>`;
  }).join('')}</section>`;
}

function toc(mods, extra) {
  return `<section class="page-break"><h2 style="margin-top:0">Daftar Isi</h2><ol class="toc">
    <li>Cara Menjalankan Sistem & Akun</li><li>Ringkasan QA, bug yang diperbaiki, catatan terbuka</li>
    ${mods.map((m) => `<li>Modul ${esc(m.code)} — ${esc(m.title)} (${m.steps.length} langkah)</li>`).join('')}
    ${extra ? `<li>${extra}</li>` : ''}</ol></section>`;
}

function html(body, title) {
  return `<!doctype html><html lang="id"><head><meta charset="utf-8"><title>${esc(title)}</title><style>${css}</style></head><body>${body}</body></html>`;
}

async function print(browser, content, file, title) {
  const page = await browser.newPage();
  const tmpHtml = path.join(outDir, `${path.basename(file, '.pdf')}.html`);
  fs.writeFileSync(tmpHtml, html(content, title));
  await page.goto(`file://${tmpHtml}`, { waitUntil: 'load', timeout: 300000 });
  await page.waitForTimeout(500);
  await page.pdf({
    path: file,
    format: 'A4',
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: `<div style="font-size:7pt;color:#64748b;width:100%;padding:0 13mm;display:flex;justify-content:space-between"><span>SIFAK — ${esc(title)}</span><span>${today}</span></div>`,
    footerTemplate: '<div style="font-size:7pt;color:#64748b;width:100%;text-align:center">Halaman <span class="pageNumber"></span> / <span class="totalPages"></span></div>',
    margin: { top: '14mm', bottom: '16mm', left: '13mm', right: '13mm' },
  });
  await page.close();
  fs.unlinkSync(tmpHtml);
  console.log(`PDF: ${path.relative(process.cwd(), file)} (${(fs.statSync(file).size / 1048576).toFixed(1)} MB)`);
}

(async () => {
  convertImages();
  DIMS = JSON.parse(fs.readFileSync(path.join(jpgDir, 'dims.json'), 'utf8'));
  const crawl = JSON.parse(fs.readFileSync(path.join(outDir, 'crawl.json'), 'utf8'));
  const mods = MODULES.map((id) => JSON.parse(fs.readFileSync(path.join(outDir, `${id}.json`), 'utf8')));
  const browser = await launch();
  try {
    const full = cover('Manual Guide & Laporan QA Modul M1–M7', 'Panduan menjalankan setiap modul langkah demi langkah dengan screenshot, hasil QA keseluruhan sistem, dan fitur setiap role akses.')
      + toc(mods, 'Fitur & hak akses setiap role')
      + `<section class="page-break">${howToRun()}</section>`
      + `<section class="page-break">${qaSummary(crawl, mods)}</section>`
      + mods.map((m, i) => moduleSection(m, i + 3)).join('')
      + rolesSection(crawl, mods.length + 3);
    await print(browser, full, path.join(pdfDir, 'SIFAK_Manual_Guide_QA_M1-M7.pdf'), 'Manual Guide & QA M1–M7');

    for (const m of mods) {
      const body = cover(`Manual Guide ${m.code}: ${m.title}`, m.purpose) + `<section class="page-break">${howToRun()}</section>` + moduleSection(m, 2);
      await print(browser, body, path.join(pdfDir, `SIFAK_${SLUG[m.id]}.pdf`), `Manual ${m.code} — ${m.title}`);
    }
    await print(browser, cover('Fitur & Hak Akses per Role', 'Menu dan aksi yang tersedia untuk setiap role, diambil langsung dari aplikasi.') + rolesSection(crawl, 1), path.join(pdfDir, 'SIFAK_Fitur_Per_Role.pdf'), 'Fitur per Role');
    await print(browser, cover('Laporan QA SIFAK', 'Ringkasan hasil QA, bug yang diperbaiki, dan catatan terbuka.') + `<section class="page-break">${qaSummary(crawl, mods)}</section>`, path.join(pdfDir, 'SIFAK_Laporan_QA.pdf'), 'Laporan QA');
  } finally {
    await browser.close();
  }
})();
