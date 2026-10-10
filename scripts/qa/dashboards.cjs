// Screenshot beranda setiap role + cek menu sidebar + cek halaman di luar fitur role ditolak (403).
const fs = require('fs');
const path = require('path');
const { outDir, shotDir, accounts, ensureDirs, launch, newSession, login, settle } = require('./lib.cjs');

// Contoh halaman yang BUKAN fitur role tersebut (harus 403).
const forbidden = {
  mahasiswa: ['/sidang-types', '/cpls', '/kelas-kuliahs', '/mahasiswas'],
  mahasiswa_demo: [],
  dosen: ['/rekomendasi-pengampus', '/mahasiswas', '/matriks-kesesuaians'],
  admin_prodi: ['/users', '/jenis-surats', '/tenants'],
  admin_fakultas: ['/krs', '/rekomendasi-pengampus', '/tenants'],
  kaprodi: ['/sidang-scores'],
  dekan: ['/krs', '/sidang-registrations'],
  wd: ['/krs'],
  kbk: ['/surats', '/alerts'],
  lpm: ['/surats', '/krs'],
  baak: ['/surats', '/dosen-profils'],
  kalab: ['/surats', '/alerts', '/mahasiswa-profiles'],
};

(async () => {
  ensureDirs();
  const browser = await launch();
  const report = {};
  const only = process.argv.slice(2);
  const keys = only.length ? only : Object.keys(accounts).filter((k) => k !== 'superadmin' && k !== 'mahasiswa_demo2');
  for (const key of keys) {
    const { context, page, acc } = await newSession(browser, key);
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message.slice(0, 160)));
    try {
      await login(page, acc);
      await settle(page, 1200);
      const nav = await page.evaluate(() => [...document.querySelectorAll('.fi-sidebar-nav .fi-sidebar-group, .fi-sidebar-nav > ul > li')]
        .map((g) => ({ group: g.querySelector('.fi-sidebar-group-label')?.textContent.trim() || '', items: [...g.querySelectorAll('.fi-sidebar-item-label')].map((i) => i.textContent.trim()) }))
        .filter((g) => g.items.length));
      const body = await page.locator('body').innerText();
      if (/Internal Server Error|SQLSTATE|ErrorException/.test(body)) errors.push('server error on dashboard');
      await page.screenshot({ path: path.join(shotDir, `dash-${key}.png`), fullPage: true });
      const blocked = [];
      for (const p of forbidden[key] || []) {
        const r = await page.goto(`${acc.base}/${acc.panel}${p}`, { waitUntil: 'domcontentloaded' });
        blocked.push({ path: p, status: r ? r.status() : 0 });
      }
      report[key] = { nav, errors, blocked };
      console.log(`[${key}] groups=${nav.map((g) => `${g.group || '-'}(${g.items.length})`).join(', ')} errors=${errors.length} blocked=${blocked.map((b) => `${b.path}:${b.status}`).join(' ')}`);
    } catch (e) {
      report[key] = { fatal: e.message };
      console.log(`[${key}] FATAL ${e.message.split('\n')[0]}`);
    }
    await context.close();
  }
  await browser.close();
  const file = path.join(outDir, 'dashboards.json');
  const prev = fs.existsSync(file) ? JSON.parse(fs.readFileSync(file, 'utf8')) : {};
  fs.writeFileSync(file, JSON.stringify({ ...prev, ...report }, null, 2));
})();
