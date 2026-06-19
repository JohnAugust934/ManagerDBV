// Gera os ícones do PWA (instalável + maskable + Apple touch) a partir do
// logo vetorial em public/favicon.svg, renderizando-o centralizado sobre o
// fundo da marca (#002F6C) com a "safe zone" exigida por ícones maskable.
//
// Uso:
//   node scripts/pwa-icons.mjs
//
// Requer Playwright (devDependency): npx playwright install chromium

import { chromium } from 'playwright';
import { mkdirSync } from 'fs';

const OUT = 'public/icons';
mkdirSync(OUT, { recursive: true });

const BG = '#002F6C'; // mesmo theme-color do layout

// O logo (triângulo + chevron) no viewBox 0 0 100 100.
const LOGO = `
  <path d="M 50 15 L 90 80 L 10 80 Z" fill="#facc15" stroke="#1e40af" stroke-width="6" stroke-linejoin="round"/>
  <path d="M 35 68 L 35 45 L 50 60 L 65 45 L 65 68" fill="none" stroke="#1e40af" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
`;

// size: lado do PNG em px. logoScale: fração do lado ocupada pelo logo
// (≈0.6 dá a margem de segurança para o recorte maskable do Android).
const targets = [
  { name: 'icon-192.png', size: 192, logoScale: 0.62 },
  { name: 'icon-512.png', size: 512, logoScale: 0.62 },
  { name: 'apple-touch-icon.png', size: 180, logoScale: 0.66 },
];

const browser = await chromium.launch();

for (const { name, size, logoScale } of targets) {
  const logoPx = Math.round(size * logoScale);
  const html = `<!doctype html><html><head><meta charset="utf-8">
    <style>
      html,body{margin:0;padding:0}
      .wrap{width:${size}px;height:${size}px;background:${BG};
        display:flex;align-items:center;justify-content:center}
      svg{width:${logoPx}px;height:${logoPx}px;display:block}
    </style></head>
    <body><div class="wrap">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">${LOGO}</svg>
    </div></body></html>`;

  const ctx = await browser.newContext({
    viewport: { width: size, height: size },
    deviceScaleFactor: 1,
  });
  const page = await ctx.newPage();
  await page.setContent(html, { waitUntil: 'networkidle' });
  await page.screenshot({ path: `${OUT}/${name}`, omitBackground: false });
  await ctx.close();
  console.log('ok', name, `${size}x${size}`);
}

await browser.close();
console.log('done');
