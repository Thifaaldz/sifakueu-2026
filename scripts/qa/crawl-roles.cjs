// QA crawler: login sebagai setiap role, catat menu sidebar, buka setiap halaman list + create, catat error.
const fs = require('fs');
const path = require('path');
const { outDir, shotDir, accounts, ensureDirs, launch, newSession, login, settle } = require('./lib.cjs');

const only = process.argv.slice(2);

async function readNav(page) {
  return page.evaluate(() => {
    const groups = [];
    document.querySelectorAll('nav .fi-sidebar-group, .fi-sidebar-nav-groups > li').forEach((g) => {
      const label = g.querySelector('.fi-sidebar-group-label')?.textContent.trim() || '(Tanpa grup)';
      const items = [...g.querySelectorAll('a.fi-sidebar-item-button')].map((a) => ({
        label: a.querySelector('.fi-sidebar-item-label')?.textContent.trim() || a.textContent.trim(),
        href: a.getAttribute('href'),
      }));
      if (items.length) groups.push({ label, items });
    });
    return groups;
  });
}

async function checkPage(page, url) {
  const errors = [];
  const onConsole = (m) => { if (m.type() === 'error') errors.push(m.text().slice(0, 200)); };
  page.on('console', onConsole);
  let status = 0;
  try {
    const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 60000 });
    status = resp ? resp.status() : 0;
    await settle(page, 300);
  } catch (e) {
    errors.push(`NAV: ${e.message.slice(0, 150)}`);
  }
  page.off('console', onConsole);
  const body = await page.locator('body').innerText().catch(() => '');
  const bad = /Internal Server Error|Server Error|Whoops|SQLSTATE|ErrorException|Undefined (variable|array key|property)|Call to (a )?(undefined|member)/i.exec(body);
  if (bad) errors.push(`BODY: ${bad[0]}`);
  if (status >= 400) errors.push(`HTTP ${status}`);
  const title = await page.locator('h1').first().innerText().catch(() => '');
  const hasCreate = await page.locator('.fi-header a[href$="/create"]').first().isVisible().catch(() => false);
  const rows = await page.locator('table tbody tr').count().catch(() => 0);
  const rowActions = await page.locator('table tbody tr').first().locator('.fi-ta-actions a, .fi-ta-actions button').allInnerTexts().catch(() => []);
  return { status, errors, title, hasCreate, rows, rowActions: rowActions.map((t) => t.trim()).filter(Boolean) };
}

(async () => {
  ensureDirs();
  const browser = await launch();
  const result = {};
  const keys = only.length ? only : Object.keys(accounts).filter((k) => !accounts[k].qaOnly);
  for (const key of keys) {
    const { context, page, acc } = await newSession(browser, key);
    const entry = { account: acc, nav: [], pages: [] };
    try {
      await login(page, acc);
      await page.screenshot({ path: path.join(shotDir, `role-${key}-dashboard.png`) });
      entry.nav = await readNav(page);
      for (const g of entry.nav) {
        for (const item of g.items) {
          const url = new URL(item.href, acc.base).toString();
          const r = await checkPage(page, url);
          const rec = { group: g.label, label: item.label, url, ...r };
          if (r.hasCreate) {
            const c = await checkPage(page, url.replace(/\/$/, '') + '/create');
            rec.create = { status: c.status, errors: c.errors };
          }
          entry.pages.push(rec);
          const flag = r.errors.length || rec.create?.errors.length ? 'FAIL' : 'ok';
          console.log(`[${key}] ${flag} ${g.label} > ${item.label} rows=${r.rows} ${r.errors.concat(rec.create?.errors || []).join(' | ')}`);
        }
      }
    } catch (e) {
      entry.fatal = e.message;
      console.log(`[${key}] FATAL ${e.message}`);
    }
    result[key] = entry;
    await context.close();
  }
  await browser.close();
  const file = path.join(outDir, only.length ? `crawl-${only.join('-')}.json` : 'crawl.json');
  fs.writeFileSync(file, JSON.stringify(result, null, 2));
  console.log(`saved ${file}`);
})();
