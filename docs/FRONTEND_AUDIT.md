# FRONTEND_AUDIT — ManagerDBV

> Auditoria de frontend em **2026-06-19**, branch `multi-tenant`.
> Stack real: **Laravel 12 + Blade + Alpine.js 3 + Tailwind 3** (Vite). Não há HTML/CSS/SCSS
> avulso nem React/Vue. Já existe um design system Tailwind-native (`resources/css/app.css`
> com classes `ui-*` e tokens `--ui-*`) e um guia (`docs/guia-visual-ui.md`).
>
> Por isso esta auditoria **não** recomenda introduzir um sistema paralelo de `:root{--color-*}`
> nem classes `.btn`/`.card` BEM (como sugere um checklist genérico de frontend): isso brigaria
> com o Tailwind e duplicaria os tokens `ui-*` já existentes. As recomendações abaixo trabalham
> **dentro** das convenções atuais.

## Resumo

O projeto **já está em bom estado** de frontend: mobile-first, dark mode, foco visível,
`prefers-reduced-motion`, skip-link, e tabelas com scroll horizontal. Os achados são pontuais —
não há uma reescrita mobile-first a fazer. Há **1 problema crítico de acessibilidade** (zoom
bloqueado) e alguns ajustes de robustez/qualidade.

## O que já está correto (verificado)

- **Mobile-first real:** estilo base é mobile; breakpoints `sm:`/`lg:` progressivos; sidebar vira
  drawer + bottom-nav fixa no mobile (`layouts/app.blade.php`).
- **Viewport meta presente**, `lang` dinâmico correto, `charset`, `theme-color`, CSRF meta.
- **Tokens de design centralizados** em `app.css` (`--ui-*`, claro + escuro) e botões/inputs/cards
  padronizados (`ui-btn-*`, `ui-input`, `ui-card`, `ui-table-*`).
- **Áreas tocáveis:** `.ui-btn` tem `min-h-[48px]`; itens de bottom-nav são `w-16 h-16` (64px).
- **Acessibilidade:** `focus-visible` global com `outline`; skip-link "Pular para o conteúdo";
  override AA do texto muted (`-400`→`-500`) no modo claro dentro de `.ui-app`; meta de contraste
  documentada no guia visual.
- **`prefers-reduced-motion: reduce`** desliga animações/transições.
- **Tabelas responsivas:** todas as telas com `<table>` do app estão dentro de
  `.ui-table-wrapper`/`overflow-x-auto` (verificado em usuários, unidades, frequência, patrimônio,
  atos, ranking, caixa, desbravadores). As demais `<table>` são templates de PDF (`relatorios/*`).
- **JS limpo:** sem `console.log`/`debug`; scroll da sidebar já usa `requestAnimationFrame`
  (throttle); SW PWA é best-effort.
- **`!important`:** uso mínimo e legítimo (`[x-cloak]`, reduced-motion, `@media print`).

## Achados priorizados

### 🔴 CRÍTICO

**C1 — Zoom de página bloqueado (WCAG 2.1 AA · 1.4.4 Resize Text)**
`resources/views/layouts/app.blade.php:16`
```html
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
```
`maximum-scale=1, user-scalable=0` impede o usuário de dar pinch-zoom — reprova WCAG e prejudica
baixa visão. **Correção:** `content="width=device-width, initial-scale=1"`.
(O motivo provável de existir é evitar o zoom automático do iOS em inputs; isso já é resolvido por
fontes ≥16px nos inputs — `.ui-input` usa `text-[15px]`; ver M2.)

### 🟠 ALTO

**A1 — "Saída Suave" atrasa 300ms toda navegação interna e arrisca tela branca**
`resources/js/app.js:76-107`
O handler global de clique faz `body.opacity=0` e navega após `setTimeout(300ms)`. Implicações:
- **Performance percebida:** +300ms em todo clique de link interno.
- **Risco de tela branca:** se a navegação não concluir (link 4xx/cancelado, abrir teclado, etc.),
  o `body` fica em `opacity:0`. Já existe a rede de segurança `pageshow`/`DOMContentLoaded`, mas o
  efeito não respeita `prefers-reduced-motion`.
- **Robustez:** `link.getAttribute("href").startsWith("#")` lança `TypeError` se um `<a>` não tiver
  `href` (ex.: âncoras de menu Alpine). Faltam guardas para `mailto:`/`tel:`.

**Correção sugerida:** respeitar `prefers-reduced-motion` (pular o fade), proteger `href` nulo, e
reduzir/condicionar o delay. (Relacionado à memória `saida-suave-intercepta-downloads`.)

### 🟡 MÉDIO

**M1 — Typo de UI:** `app.blade.php:153` "Atas **Reunões**" → "Atas de **Reuniões**".

**M2 — Inputs e zoom no iOS:** `.ui-input` usa `text-[15px]` (`app.css:198`). Abaixo de 16px o
iOS dá zoom automático ao focar — foi provavelmente o motivo de C1. Subir para 16px
(`text-base`/`text-[16px]`) permite remover o bloqueio de zoom de C1 com segurança.

**M3 — `text-gradient-dbv` no nome do clube** (`app.blade.php:76`): texto com `bg-clip-text` +
`text-transparent`. Em nome longo/tela estreita o gradiente azul→vermelho pode cair abaixo de
4.5:1 em parte do texto. É branding e grande, mas vale conferir o contraste mínimo do gradiente.

### 🟢 BAIXO

**B1 — `<meta name="description">` e Open Graph ausentes.** Baixa prioridade: app é atrás de login.
Vale apenas para as páginas públicas (`welcome`, `sobre`) se houver intenção de compartilhamento.

**B2 — Fonte Inter com 6 pesos via bunny.net** (`app.blade.php:24`). Já usa `display=swap` e
`preconnect`. Possível enxugar para os pesos realmente usados (400/600/700/800/900) — ganho marginal.

## Itens do checklist genérico que NÃO se aplicam aqui

- Reescrever media queries para `min-width`: já é mobile-first.
- Criar `:root{--color-*}` / `.btn` / `.card` BEM: já existem tokens `--ui-*` e classes `ui-*`.
- `<img max-width:100%>` global, `loading="lazy"`: poucas imagens (logo do clube, favicon);
  todas têm `alt`. Sem galerias.
- Reset/normalize: provido pelo `@tailwind base` (Preflight).

## Recomendação

Aplicar **C1 + M2 juntos** (fix de acessibilidade seguro), **M1** (typo trivial) e **A1**
(robustez da Saída Suave). Os demais (M3, B1, B2) são opcionais/refinamento.

---

## Fase 2.2 — Auditoria semântica de HTML (resultados)

### 🟠 H1 — Páginas sem título/heading (CORRIGIDO)
53 views definem `<x-slot name="header">`, mas `layouts/app.blade.php` **não renderiza** o slot
`$header`. Consequência: **25 páginas não exibiam nenhum `<h1>`** (caixa, forms de cadastro/edição,
unidades/show, relatórios, backups, convites, etc.). Corrigido com o componente `<x-page-title>` em
cada uma (ver `CHANGES.md`). As 28 páginas com título no corpo não foram tocadas.

### 🟡 H2 — Hierarquia de headings (CORRIGIDO)
O nome do clube na sidebar era `<h1>` em todas as telas, competindo com o título da página.
Rebaixado para `<p>`. Agora: **1 `<h1>` por página** = o título da página.

### ✔ Conformidades verificadas
- Tags semânticas presentes (`<aside>`, `<nav>`, `<main id="app-content">`, `<header>`, `<section>`).
- `<html lang>` resolve para `pt-BR`.
- Labels: componentes `x-input-label`/`ui-input-label` usados nos formulários (21 telas).
- `<a>` para navegação e `<button>` para ações/submes — uso correto (inclui o FAB e confirmações).

### 🟢 Pendência baixa
Campos de **busca** dependem só de `placeholder` (sem `<label for>`/`aria-label` em alguns casos) —
melhoria de a11y de baixa prioridade.

## Fase 4 — 100 dicas (itens aplicáveis, status)
Zoom liberado ✔ · inputs ≥16px ✔ · `lang` ✔ · sem `console.log` ✔ · `alt`/`loading=lazy` ✔ ·
throttle em scroll ✔ · `!important` mínimo ✔ · variáveis/tokens no lugar de valores mágicos ✔.
Não aplicáveis: BEM, normalize manual, OG/preload de fontes críticas (app atrás de login).

## Fase 5 — Teste visual real (Playwright)
- `scripts/visual-audit.mjs`: overflow horizontal + screenshots em 320/375/768/1024/1440px.
- Banco **SQLite isolado e semeado** (o Postgres/Supabase do `.env` **não foi tocado**).
- **105 combinações página×breakpoint → 0 overflow horizontal, 0 erros.**
- Inspeção visual de ~20 telas mobile: layout polido, sem quebras; títulos restaurados confirmados.
- **Modo escuro:** varredura adicional (18 telas × 5 breakpoints = 90 combinações) forçando
  `localStorage.theme='dark'` → **0 overflow, 0 telas sem o tema aplicado**. Inspeção visual
  (dashboard, listas, tabelas, formulários, ranking, relatórios, frequência) confirmou contraste e
  legibilidade; títulos restaurados também corretos no escuro.
