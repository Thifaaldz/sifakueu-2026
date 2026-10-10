const fs = require('fs');
const path = require('path');

const { chromium } = require('/home/kumadz/.npm/_npx/e41f203b7505f1fb/node_modules/playwright-core');

const root = path.resolve(__dirname, '..', '..');
const outDir = path.join(root, 'docs', 'manual-guides', 'v2');
const shotDir = path.join(outDir, 'screenshots');

const TENANT = 'https://fasilkom.sifakueu.test';
const CENTRAL = 'https://sifakueu.test';

const accounts = {
  superadmin: { base: CENTRAL, panel: 'admin', email: 'admin@admin.com', name: 'Super Admin', role: 'super_admin' },
  admin_fakultas: { base: TENANT, panel: 'admin', email: 'admin.fasilkom@sifak.local', name: 'Admin Fakultas / TU', role: 'admin_fakultas' },
  admin_prodi: { base: TENANT, panel: 'admin', email: 'admin.prodi.fasilkom@sifak.local', name: 'Admin Prodi', role: 'admin_prodi' },
  dosen: { base: TENANT, panel: 'dosen', email: 'dosen.fasilkom@sifak.local', name: 'Dosen (PA, Pembimbing, Penguji)', role: 'dosen, dosen_pa, dosen_pembimbing, dosen_penguji' },
  mahasiswa: { base: TENANT, panel: 'mahasiswa', email: 'mahasiswa.fasilkom@sifak.local', name: 'Mahasiswa', role: 'mahasiswa' },
  mahasiswa_demo: { base: TENANT, panel: 'mahasiswa', email: 'mahasiswa.demo@sifak.local', name: 'Mahasiswa (Rina Demo QA)', role: 'mahasiswa', qaOnly: true },
  mahasiswa_demo2: { base: TENANT, panel: 'mahasiswa', email: 'mahasiswa.demo2@sifak.local', name: 'Mahasiswa (Budi Demo QA)', role: 'mahasiswa', qaOnly: true },
  kaprodi: { base: TENANT, panel: 'pimpinan', email: 'kaprodi.fasilkom@sifak.local', name: 'Kaprodi', role: 'kaprodi' },
  dekan: { base: TENANT, panel: 'pimpinan', email: 'dekan.fasilkom@sifak.local', name: 'Dekan', role: 'dekan' },
  wd: { base: TENANT, panel: 'pimpinan', email: 'wd.fasilkom@sifak.local', name: 'Wakil Dekan', role: 'wd' },
  kbk: { base: TENANT, panel: 'pimpinan', email: 'kbk.fasilkom@sifak.local', name: 'Koordinator KBK', role: 'kbk' },
  lpm: { base: TENANT, panel: 'pimpinan', email: 'lpm.fasilkom@sifak.local', name: 'LPM / Gugus Mutu', role: 'lpm' },
  baak: { base: TENANT, panel: 'pimpinan', email: 'baak.fasilkom@sifak.local', name: 'BAAK', role: 'baak' },
  kalab: { base: TENANT, panel: 'pimpinan', email: 'kalab.fasilkom@sifak.local', name: 'Kepala Laboratorium', role: 'kepala_laboratorium' },
};

function ensureDirs() {
  fs.mkdirSync(shotDir, { recursive: true });
}

async function launch() {
  return chromium.launch({ headless: true, executablePath: '/usr/bin/google-chrome' });
}

async function newSession(browser, key) {
  const acc = accounts[key];
  const context = await browser.newContext({
    ignoreHTTPSErrors: true,
    viewport: { width: 1600, height: 950 },
    deviceScaleFactor: 1.5,
  });
  const page = await context.newPage();
  page.on('dialog', (d) => d.accept());
  await page.goto(`${acc.base}/${acc.panel}/login`, { waitUntil: 'networkidle', timeout: 60000 });
  return { context, page, acc };
}

async function login(page, acc) {
  await page.locator('input[type=email]').fill(acc.email);
  await page.locator('input[type=password]').first().fill('password');
  await page.locator('form button[type=submit]').first().click();
  await page.waitForFunction(() => !location.pathname.endsWith('/login'), null, { timeout: 60000 });
  await settle(page);
}

async function settle(page, extra = 600) {
  await page.waitForLoadState('networkidle', { timeout: 20000 }).catch(() => {});
  await page.waitForTimeout(extra);
}

async function goto(page, acc, p) {
  const resp = await page.goto(`${acc.base}/${acc.panel}${p}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await settle(page);
  return resp;
}

module.exports = { root, outDir, shotDir, accounts, ensureDirs, launch, newSession, login, settle, goto, TENANT, CENTRAL };
