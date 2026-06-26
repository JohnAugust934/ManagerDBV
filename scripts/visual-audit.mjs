// Auditoria visual de responsividade (Fase 5).
// Mede overflow horizontal real por breakpoint e salva screenshots para revisão.
//
// Uso: node scripts/visual-audit.mjs  (servidor em http://127.0.0.1:8123, banco SQLite de teste)

import { chromium } from "playwright";
import { mkdirSync, writeFileSync } from "fs";

const BASE = process.env.SHOTS_BASE ?? "http://127.0.0.1:8123";
const OUT = "storage/visual-audit";
mkdirSync(OUT, { recursive: true });

const BREAKPOINTS = [320, 375, 768, 1024, 1440];
const SHOT_BPS = new Set([375, 1280]); // larguras que viram PNG p/ revisão
const HEIGHT = 900;

// Páginas autenticadas (diretor do clube Orion).
const PAGES = [
  ["dashboard", "/dashboard"],
  ["desbravadores", "/desbravadores"],
  ["desbravadores-create", "/desbravadores/create"],
  ["unidades", "/unidades"],
  ["frequencia", "/frequencia"],
  ["frequencia-create", "/frequencia/create"],
  ["classes", "/classes"],
  ["especialidades", "/especialidades"],
  ["eventos", "/eventos"],
  ["caixa", "/caixa"],
  ["mensalidades", "/mensalidades"],
  ["patrimonio", "/patrimonio"],
  ["relatorios", "/relatorios"],
  ["ranking-unidades", "/ranking/unidades"],
  ["ranking-desbravadores", "/ranking/desbravadores"],
  ["atas", "/atas"],
  ["atos", "/atos"],
  ["profile", "/profile"],
];

// Páginas públicas (sem login).
const PUBLIC_PAGES = [
  ["login", "/login"],
  ["forgot-password", "/forgot-password"],
  ["welcome", "/"],
];

const report = [];
const browser = await chromium.launch();

async function overflowOf(page) {
  return page.evaluate(() => {
    const de = document.documentElement;
    return {
      scrollW: de.scrollWidth,
      clientW: de.clientWidth,
      innerW: window.innerWidth,
    };
  });
}

async function runSet(label, list, { login = null } = {}) {
  for (const bp of BREAKPOINTS) {
    const ctx = await browser.newContext({
      viewport: { width: bp, height: HEIGHT },
      deviceScaleFactor: 1,
    });
    const page = await ctx.newPage();

    if (login) {
      await page.goto(`${BASE}/login`, { waitUntil: "networkidle" });
      await page.fill('input[name="email"]', login.email);
      await page.fill('input[name="password"]', login.password);
      await Promise.all([
        page.waitForLoadState("networkidle"),
        page.click('button[type="submit"]'),
      ]);
    }

    for (const [name, path] of list) {
      try {
        await page.goto(`${BASE}${path}`, { waitUntil: "networkidle" });
        await page.waitForTimeout(400);
        const o = await overflowOf(page);
        const overflow = o.scrollW - o.clientW;
        const bad = overflow > 1;
        report.push({ set: label, page: name, bp, overflow, bad });
        if (bad) console.log(`  ⚠ OVERFLOW ${name} @${bp}px  (+${overflow}px)`);

        if (SHOT_BPS.has(bp) || bad) {
          await page.screenshot({
            path: `${OUT}/${name}@${bp}.png`,
            fullPage: true,
          });
        }
      } catch (e) {
        report.push({ set: label, page: name, bp, error: e.message });
        console.log(`  ✖ FAIL ${name} @${bp}px ${e.message}`);
      }
    }
    await ctx.close();
  }
}

console.log("== público ==");
await runSet("public", PUBLIC_PAGES);
console.log("== diretor (orion) ==");
await runSet("director", PAGES, {
  login: { email: "diretor.orion@clube.com", password: "password" },
});

await browser.close();

// Resumo
const bad = report.filter((r) => r.bad);
const fails = report.filter((r) => r.error);
writeFileSync(`${OUT}/report.json`, JSON.stringify(report, null, 2));
console.log("\n===== RESUMO =====");
console.log(`páginas×breakpoints testados: ${report.length}`);
console.log(`overflow horizontal: ${bad.length}`);
console.log(`erros de navegação: ${fails.length}`);
if (bad.length)
  console.log(
    "overflow em:",
    [...new Set(bad.map((b) => `${b.page}@${b.bp}`))].join(", ")
  );
console.log("done");
