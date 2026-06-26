// Verificação e2e das Fases 1 (PWA) e 2 (atualização parcial sem reload).
// Uso: php artisan serve --port=8123  &&  node scripts/verify-pwa.mjs
import { chromium } from 'playwright';

const BASE = process.env.SHOTS_BASE ?? 'http://127.0.0.1:8123';
const results = [];
const ok = (n, c, extra = '') => results.push(`${c ? 'PASS' : 'FAIL'}  ${n}${extra ? ' — ' + extra : ''}`);

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
const page = await ctx.newPage();

// --- Login (master do clube orion: tem financeiro + secretaria + pedagogico) ---
await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
await page.fill('input[name="email"]', 'master.orion@clube.com');
await page.fill('input[name="password"]', 'password');
await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"]')]);
ok('login', page.url().includes('/dashboard'), page.url());

// === FASE 1: PWA ===
const manifestHref = await page.getAttribute('link[rel="manifest"]', 'href');
ok('manifest linkado', !!manifestHref, manifestHref || '');
const mres = await page.request.get(`${BASE}/manifest.webmanifest`);
const mjson = mres.ok() ? await mres.json() : {};
ok('manifest 200 + standalone', mres.ok() && mjson.display === 'standalone', `display=${mjson.display}`);
ok('manifest tem ícones 192/512', (mjson.icons || []).length >= 2);
ok('apple-touch-icon presente', !!(await page.getAttribute('link[rel="apple-touch-icon"]', 'href').catch(() => null)));
const swReg = await page.evaluate(async () => {
    if (!('serviceWorker' in navigator)) return false;
    const r = await navigator.serviceWorker.getRegistration();
    return !!r;
});
ok('service worker registrado', swReg);
const swRes = await page.request.get(`${BASE}/sw.js`);
ok('sw.js servido', swRes.ok());
const offRes = await page.request.get(`${BASE}/offline.html`);
ok('offline.html servido', offRes.ok());

// === FASE 2a: pagamento de mensalidade sem reload ===
await page.goto(`${BASE}/mensalidades`, { waitUntil: 'networkidle' });
// Garante pelo menos uma mensalidade pendente: se não houver card, gera o lote.
let pendentes = await page.locator('[id^="mensalidade-card-"] button:has-text("Confirmar Recebimento")').count();
if (pendentes === 0) {
    await page.click('button:has-text("Gerar Carnê do Mês")');
    await page.locator('[role="dialog"]:visible button:has-text("Disparar Cobranças")').click();
    await page.waitForLoadState('networkidle');
    pendentes = await page.locator('[id^="mensalidade-card-"] button:has-text("Confirmar Recebimento")').count();
}
ok('há mensalidade pendente para testar', pendentes > 0, `qtd=${pendentes}`);

if (pendentes > 0) {
    // Marca um sentinela: se a página recarregar, ele some.
    await page.evaluate(() => (window.__noReload = 'mantido'));
    const card = page.locator('[id^="mensalidade-card-"]').filter({ hasText: 'Confirmar Recebimento' }).first();
    const cardId = await card.getAttribute('id');
    await card.locator('button:has-text("Confirmar Recebimento")').click(); // abre modal
    await page.locator('[role="dialog"]:visible form button[type="submit"]:has-text("Confirmar Recebimento")').click();
    // Espera o card virar "Quitada"
    await page.locator(`#${cardId}:has-text("Quitada")`).waitFor({ timeout: 5000 }).catch(() => {});
    const virouPago = await page.locator(`#${cardId}`).innerText();
    const sentinela = await page.evaluate(() => window.__noReload);
    ok('pagamento sem reload (sentinela mantida)', sentinela === 'mantido');
    ok('card atualizado para Quitada', /Quitada/.test(virouPago));
}

// === FASE 2b: toggle de ranking sem reload ===
await page.goto(`${BASE}/unidades`, { waitUntil: 'networkidle' });
await page.locator('a:has-text("Editar")').first().click();
await page.waitForLoadState('networkidle');
const toggleBtn = page.locator('button[form="toggle-ranking-form"]');
const antes = (await toggleBtn.innerText()).trim();
await page.evaluate(() => (window.__noReload2 = 'mantido'));
await toggleBtn.click();
await page.waitForTimeout(1200);
const depois = (await toggleBtn.innerText()).trim();
const sentinela2 = await page.evaluate(() => window.__noReload2);
ok('toggle ranking sem reload', sentinela2 === 'mantido');
ok('rótulo do botão mudou', antes !== depois, `"${antes}" -> "${depois}"`);

await browser.close();
console.log('\n=== RESULTADO ===');
console.log(results.join('\n'));
const falhas = results.filter((r) => r.startsWith('FAIL'));
console.log(`\n${results.length - falhas.length}/${results.length} OK`);
process.exit(falhas.length ? 1 : 0);
