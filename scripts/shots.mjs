// Gera as capturas de tela do README a partir da aplicação em execução.
//
// Uso:
//   1. php artisan migrate:fresh --seed
//   2. php artisan serve --port=8123
//   3. node scripts/shots.mjs
//
// Requer Playwright (devDependency): npx playwright install chromium

import { chromium } from 'playwright';
import { mkdirSync } from 'fs';

const BASE = process.env.SHOTS_BASE ?? 'http://127.0.0.1:8123';
const OUT = 'public/images/manual';
mkdirSync(OUT, { recursive: true });

// Páginas acessíveis ao diretor (visão de um clube real).
const asDirector = [
  ['dashboard', '/dashboard'],
  ['desbravadores-index', '/desbravadores'],
  ['unidades-index', '/unidades'],
  ['frequencia-index', '/frequencia'],
  ['classes-index', '/classes'],
  ['especialidades-index', '/especialidades'],
  ['eventos-index', '/eventos'],
  ['caixa-index', '/caixa'],
  ['mensalidades-index', '/mensalidades'],
  ['patrimonio-index', '/patrimonio'],
  ['relatorios-index', '/relatorios'],
  ['ranking-index', '/ranking/unidades'],
  ['atas-index', '/atas'],
  ['atos-index', '/atos'],
];

// Páginas restritas (master).
const asMaster = [
  ['usuarios-index', '/usuarios'],
  ['invites-index', '/invites'],
  ['backups-index', '/backups/clube'],
];

const browser = await chromium.launch();

async function capture(email, list) {
  const ctx = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 2,
  });
  const page = await ctx.newPage();
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', 'password');
  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.click('button[type="submit"]'),
  ]);
  console.log('logged in as', email, '->', page.url());

  for (const [name, path] of list) {
    try {
      await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' });
      await page.waitForTimeout(700);
      await page.screenshot({ path: `${OUT}/${name}.png`, fullPage: true });
      console.log('ok', name);
    } catch (e) {
      console.log('FAIL', name, e.message);
    }
  }
  await ctx.close();
}

await capture('diretor.orion@clube.com', asDirector);
await capture('master.orion@clube.com', asMaster);

await browser.close();
console.log('done');
