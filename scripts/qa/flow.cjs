// Helper untuk menjalankan alur modul lewat UI Filament dan mengambil screenshot di setiap langkah.
const fs = require('fs');
const path = require('path');
const { outDir, shotDir, accounts, ensureDirs, launch, newSession, login, settle, goto } = require('./lib.cjs');

class Flow {
  constructor(moduleId, browser) {
    this.moduleId = moduleId;
    this.browser = browser;
    this.steps = [];
    this.issues = [];
    this.sessions = {};
    this.n = 0;
  }

  // Buka sesi untuk role tertentu; screenshot halaman login + dashboard pada login pertama.
  async as(key, { showLogin = true } = {}) {
    if (this.sessions[key]) {
      this.page = this.sessions[key].page;
      this.acc = this.sessions[key].acc;
      return this.page;
    }
    const s = await newSession(this.browser, key);
    this.sessions[key] = s;
    this.page = s.page;
    this.acc = s.acc;
    this.page.on('pageerror', (e) => this.issues.push(`[${key}] JS error: ${e.message.slice(0, 200)}`));
    if (showLogin) {
      await this.page.locator('input[type=email]').fill(s.acc.email);
      await this.page.locator('input[type=password]').first().fill('password');
      await this.shot(`Login sebagai ${s.acc.name}`, `Buka ${s.acc.base}/${s.acc.panel}/login, isi email ${s.acc.email} dan password "password", lalu klik Masuk.`, { highlight: this.page.locator('form button[type=submit]').first(), role: key });
      await this.page.locator('form button[type=submit]').first().click();
      await this.page.waitForFunction(() => !location.pathname.endsWith('/login'), null, { timeout: 60000 });
      await settle(this.page);
    } else {
      await login(this.page, s.acc);
    }
    return this.page;
  }

  async open(p) {
    const resp = await goto(this.page, this.acc, p);
    const status = resp ? resp.status() : 0;
    if (status >= 400) this.issues.push(`[${this.acc.role}] HTTP ${status} pada ${p}`);
    return resp;
  }

  // Menu dicari lewat URL resource (label sidebar per role berbahasa Indonesia).
  static SLUGS = {
    'Form Field Surat': 'letter-form-fields', 'Approval Step': 'approval-flow-steps', 'Template Surat': 'letter-templates',
    'Approval Surat': 'surat-approvals', 'Dokumen Generated': 'generated-letters', 'Arsip Surat': 'letter-archives',
    'Token Verifikasi': 'letter-verification-tokens', 'Sertifikasi Mahasiswa': 'mahasiswa-certifications',
    'Portofolio Mahasiswa': 'mahasiswa-portfolios', 'Organisasi Mahasiswa': 'mahasiswa-organizations', 'MBKM/Magang': 'mahasiswa-mbkms',
    'Skor Profil Lulusan': 'mahasiswa-graduate-profile-scores', 'Riwayat Rekomendasi': 'recommendation-histories',
  };

  async menu(label) {
    const slug = Flow.SLUGS[label] || label.toLowerCase().trim().replace(/\s+/g, '-');
    const link = this.page.locator(`a.fi-sidebar-item-button[href$="/${this.acc.panel}/${slug}"]`).first();
    await link.scrollIntoViewIfNeeded().catch(() => {});
    return link;
  }

  async clickMenu(label, title, desc) {
    const link = await this.menu(label);
    await link.waitFor({ timeout: 15000 });
    const info = await link.evaluate((a) => ({
      label: a.querySelector('.fi-sidebar-item-label')?.textContent.trim(),
      group: a.closest('.fi-sidebar-group')?.querySelector('.fi-sidebar-group-label')?.textContent.trim(),
    }));
    const menuTitle = `Buka menu ${info.group ? `${info.group} › ` : ''}${info.label}`;
    await this.shot(menuTitle, desc, { highlight: link });
    await link.click();
    await settle(this.page, 900);
  }

  async highlight(locator) {
    try {
      await locator.first().scrollIntoViewIfNeeded({ timeout: 3000 });
      await locator.first().evaluate((el) => {
        el.dataset.qaOutline = el.style.outline || '';
        el.style.outline = '3px solid #e11d48';
        el.style.outlineOffset = '2px';
        el.style.borderRadius = el.style.borderRadius || '6px';
      });
      return true;
    } catch {
      return false;
    }
  }

  async unhighlight() {
    await this.page.evaluate(() => document.querySelectorAll('[data-qa-outline]').forEach((el) => {
      el.style.outline = el.dataset.qaOutline;
      delete el.dataset.qaOutline;
    })).catch(() => {});
  }

  async shot(title, desc, { highlight, fullPage = false, role } = {}) {
    this.n += 1;
    const file = `${this.moduleId}-${String(this.n).padStart(2, '0')}.png`;
    if (highlight) await this.highlight(highlight);
    if (fullPage) await this.page.addStyleTag({ content: '.fi-topbar, .fi-topbar > nav { position: static !important; }' }).catch(() => {});
    await this.page.waitForTimeout(250);
    await this.page.screenshot({ path: path.join(shotDir, file), fullPage });
    if (fullPage) await this.page.evaluate(() => document.querySelectorAll('style').forEach((el) => { if (el.textContent.includes('.fi-topbar, .fi-topbar > nav')) el.remove(); })).catch(() => {});
    if (highlight) await this.unhighlight();
    this.steps.push({ n: this.n, title, desc, file, role: role || this.currentRoleKey(), url: this.page.url() });
    console.log(`  [${this.moduleId} #${this.n}] ${title}`);
  }

  currentRoleKey() {
    return Object.keys(this.sessions).find((k) => this.sessions[k].page === this.page);
  }

  // ---------- form helpers ----------
  wrapper(name) {
    return this.page.locator('.fi-fo-field-wrp').filter({ has: this.page.locator(`[id="data.${name}"], [id="mountedTableActionsData.0.${name}"], [id="mountedActionsData.0.${name}"]`) }).first();
  }

  async select(name, text) {
    const w = this.wrapper(name);
    const choices = w.locator('.choices');
    if (await choices.count()) {
      await choices.first().click();
      const input = w.locator('input.choices__input--cloned');
      if (await input.count()) await input.fill(String(text).slice(0, 20));
      await this.page.waitForTimeout(900);
      const opt = w.locator('.choices__list--dropdown .choices__item--selectable', { hasText: text }).first();
      await opt.click({ timeout: 10000 });
    } else {
      const sel = w.locator('select').first();
      await sel.selectOption({ label: text }).catch(async () => sel.selectOption(text));
    }
    await this.page.waitForTimeout(400);
  }

  async fill(name, value) {
    const el = this.page.locator(`[id="data.${name}"], [id="mountedTableActionsData.0.${name}"]`).first();
    await el.fill(String(value));
  }

  async upload(name, filePath) {
    const w = this.wrapper(name);
    await w.locator('input[type=file]').setInputFiles(filePath);
    await this.page.waitForTimeout(2500);
  }

  async create(title, desc) {
    const btn = this.page.locator('.fi-header a[href$="/create"]').first();
    const text = (await btn.innerText().catch(() => '')).trim();
    await this.shot(text ? `Klik tombol "${text}"` : title, desc, { highlight: btn });
    await btn.click();
    await settle(this.page, 1000);
  }

  async submitForm(title, desc) {
    const btn = this.page.locator('.fi-form-actions button[type=submit], form .fi-ac button[type=submit]').first();
    await this.shot(title, desc, { highlight: btn, fullPage: true });
    await btn.click();
    await settle(this.page, 1500);
    await this.checkErrors();
  }

  row(text) {
    let loc = this.page.locator('table tbody tr');
    for (const t of [].concat(text)) loc = loc.filter({ hasText: t });
    return loc.first();
  }

  // Klik action pada baris tabel; jika muncul modal, isi field lalu konfirmasi.
  async rowAction(rowText, label, title, desc, { modal, expectError = false } = {}) {
    this.expectError = expectError;
    const row = this.row(rowText);
    await row.waitFor({ timeout: 15000 });
    const btn = row.locator('.fi-ta-actions a, .fi-ta-actions button', { hasText: label }).first();
    await this.shot(title, desc, { highlight: btn });
    await btn.click();
    await settle(this.page, 900);
    const dialog = this.page.locator('.fi-modal-window:visible').last();
    if (await dialog.count()) {
      if (modal) {
        for (const [k, v] of Object.entries(modal)) {
          const field = dialog.locator(`[id$=".${k}"]`).first();
          const tag = await field.evaluate((el) => el.tagName).catch(() => '');
          if (tag === 'SELECT') await field.selectOption(String(v));
          else await field.fill(String(v));
        }
      }
      const confirm = dialog.locator('.fi-modal-footer-actions button, .fi-modal-footer button').filter({ hasNotText: /cancel|batal/i }).first();
      await this.shot(`${title} — konfirmasi`, 'Isi catatan bila diminta lalu klik tombol konfirmasi pada dialog.', { highlight: confirm });
      await confirm.click();
      await settle(this.page, 1300);
    }
    await this.page.waitForTimeout(1200);
    await this.checkErrors();
    this.expectError = false;
  }

  async dismissNotifications() {
    const closers = this.page.locator('.fi-no-notification button[type=button], .fi-no-notification .fi-icon-btn');
    for (let i = await closers.count(); i > 0; i -= 1) await closers.first().click().catch(() => {});
    await this.page.waitForTimeout(600);
  }

  async edit(rowText, title, desc) {
    const btn = this.row(rowText).locator('.fi-ta-actions a', { hasText: /^\s*(Edit|Ubah)\s*$/ }).first();
    await this.shot(title, desc, { highlight: btn });
    await btn.click();
    await settle(this.page, 1000);
  }

  async checkErrors() {
    const body = await this.page.locator('body').innerText().catch(() => '');
    const m = /(Internal Server Error|SQLSTATE[^\n]{0,160}|Call to (a )?(undefined|member)[^\n]{0,160}|Undefined (variable|array key|property)[^\n]{0,120})/i.exec(body);
    if (m) this.issues.push(`[${this.currentRoleKey()}] ${m[0]} @ ${this.page.url()}`);
    const notif = await this.page.locator('.fi-no-notification').allInnerTexts().catch(() => []);
    for (const t of notif) {
      if (!this.expectError && /tidak dapat dilanjutkan|error|gagal/i.test(t)) this.issues.push(`[${this.currentRoleKey()}] notifikasi: ${t.replace(/\s+/g, ' ').slice(0, 250)} @ ${this.page.url()}`);
    }
    const errs = await this.page.locator('.fi-fo-field-wrp-error-message, [data-validation-error]').allInnerTexts().catch(() => []);
    if (errs.length) this.issues.push(`[${this.currentRoleKey()}] validasi: ${errs.join('; ').slice(0, 300)} @ ${this.page.url()}`);
    return { notif, errs };
  }

  async cellText(rowText) {
    return this.row(rowText).innerText().catch(() => '');
  }

  async close() {
    for (const s of Object.values(this.sessions)) await s.context.close();
  }

  save(meta) {
    const file = path.join(outDir, `${this.moduleId}.json`);
    fs.writeFileSync(file, JSON.stringify({ ...meta, id: this.moduleId, steps: this.steps, issues: this.issues }, null, 2));
    console.log(`saved ${file} (${this.steps.length} steps, ${this.issues.length} issues)`);
  }
}

async function run(moduleId, meta, fn) {
  ensureDirs();
  const browser = await launch();
  const flow = new Flow(moduleId, browser);
  try {
    await fn(flow);
  } catch (e) {
    flow.issues.push(`FATAL: ${e.message.split('\n')[0]}`);
    console.error(e);
    if (flow.page) await flow.shot('Error saat menjalankan alur', e.message.split('\n')[0]).catch(() => {});
  } finally {
    flow.save(meta);
    await flow.close();
    await browser.close();
  }
}

module.exports = { Flow, run, accounts };
