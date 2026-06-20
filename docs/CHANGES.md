# CHANGES — Revisão de Frontend (2026-06-19)

Mudanças aplicadas a partir do `FRONTEND_AUDIT.md` (neste mesmo diretório). Stack: Laravel 12 + Blade + Alpine + Tailwind.
Trabalhadas **dentro** do design system existente (`ui-*` / tokens `--ui-*`) — sem sistema de CSS
paralelo. Build validado com `npm run build` (✓ 54 módulos).

## 🔴 C1 — Desbloqueio do zoom de página (WCAG 2.1 AA · 1.4.4)

Removido `maximum-scale=1, user-scalable=0` do `<meta viewport>`, que impedia pinch-zoom.

- `resources/views/layouts/app.blade.php` — viewport → `width=device-width, initial-scale=1`
- `resources/views/layouts/guest.blade.php` — idem
- (`welcome.blade.php` já estava correto)

## 🟡 M2 — Inputs em 16px (evita zoom automático do iOS)

Pré-requisito para C1 ser seguro: abaixo de 16px o iOS dá zoom ao focar campos.

- `resources/css/app.css` — `.ui-input`: `text-[15px]` → `text-base` (16px) + comentário do porquê.
  Cobre todos os inputs/selects/textarea do app que usam `ui-input`.
- Inputs "bespoke" das telas de auth (layout guest escuro), que usavam `text-sm` (14px), agora
  `text-base`:
  - `auth/login.blade.php`, `auth/forgot-password.blade.php`, `auth/confirm-password.blade.php`,
    `auth/reset-password.blade.php`, `auth/register-invite.blade.php` (inclui o email readonly).

## 🟠 A1 — Endurecimento da "Saída Suave" (navegação com fade)

`resources/js/app.js` — handler global de clique reescrito:

- **Respeita `prefers-reduced-motion`:** com movimento reduzido, pula o fade e deixa o navegador
  navegar normalmente.
- **Protege `href` nulo:** lê `getAttribute("href")` uma vez e ignora se ausente/vazio — antes
  podia lançar `TypeError` em `<a>` sem `href` (ex.: gatilhos de menu Alpine).
- **Ignora esquemas externos:** `mailto:` e `tel:` além de âncoras, `target`, `download` e host
  diferente.
- **Rede de segurança contra tela branca:** se a navegação não concluir em 1200ms, restaura
  `body.opacity = 1` (o timer é cancelado no `pagehide` quando a saída realmente acontece).

## 🟡 M1 — Typo de UI

`resources/views/layouts/app.blade.php` — "Atas **Reunões**" → "Atas de **Reuniões**" (submenu
Documentos).

## 🟢 B1 — Meta description

`resources/views/layouts/guest.blade.php` — adicionada `<meta name="description">` (página de
login é a face pública). `welcome.blade.php` já possuía.

## Verificados — sem mudança necessária

- **M3 (gradiente `text-gradient-dbv` no nome do clube):** contraste OK. Modo claro
  `from-[#002F6C]`(~13:1) → `to-[#D9222A]`(~4.9:1), ambos passam AA sobre fundo claro; modo escuro
  usa `blue-400`/`red-400` sobre fundo escuro. Mantido como está (branding, texto grande).
- **B2 (pesos da fonte Inter 400–900):** todos os 6 pesos são usados (`font-medium`/600/`font-bold`/
  `font-extrabold`/`font-black`); já há `display=swap` + `preconnect`. Nada a enxugar com segurança.

## Não aplicável (checklist genérico vs. realidade do projeto)

Reescrita mobile-first, criação de `:root{--color-*}`/`.btn`/`.card` BEM, normalize/reset e
`loading="lazy"` em galerias — já cobertos pelo Tailwind + tokens `ui-*` existentes ou inexistentes
no escopo. Detalhes no `FRONTEND_AUDIT.md`.

## Teste manual recomendado

- iOS/Safari: focar um campo de login e de cadastro — **não** deve dar zoom; pinch-zoom deve
  funcionar em qualquer tela.
- Navegação entre telas com "movimento reduzido" ligado no SO — deve navegar sem fade.
- Clicar num link interno e voltar (botão Voltar) — sem tela branca presa.

---

# CHANGES — Fase 2 (semântica HTML) + Fase 5 (teste visual)

## 🟠 Páginas sem título de página (heading ausente) — corrigido

**Diagnóstico:** 53 views definiam `<x-slot name="header">`, mas `layouts/app.blade.php`
**nunca renderiza** `{{ $header }}` (foi customizado e removeu esse render). Resultado: **25 páginas
dependiam só do header e renderizavam sem nenhum `<h1>`/título** (confirmado no caixa, nos forms de
cadastro/edição, em unidades/show, relatórios, backups, etc.). As outras 28 tinham título no corpo
(funcionavam).

**Correção:**
- Novo componente `resources/views/components/page-title.blade.php` — `<x-page-title title=""
  :subtitle="" :back="" />` que renderiza um `<h1>` consistente (com seta de voltar opcional e
  `aria-label`).
- Injetado o `<x-page-title>` no início do conteúdo das **25 páginas órfãs**, e removido o
  `<x-slot name="header">` morto delas. Onde havia link "Voltar" redundante no corpo (usuários,
  patrimônio/create), ele foi dobrado no `:back` do componente.
  - Arquivos: admin/backups/index, admin/invites/index, desbravadores/{create,edit},
    especialidades/{create,edit,historico}, eventos/{create,edit}, financeiro/caixa/{index,create},
    frequencia/columns, patrimonio/{create,edit}, ranking/snapshot, relatorios/index,
    secretaria/atas/{create,edit}, secretaria/atos/{create,edit}, unidades/{create,edit,show},
    usuarios/{create,edit}.
- **Hierarquia de headings:** o nome do clube na sidebar (`layouts/app.blade.php`) era `<h1>` em
  toda página, competindo com o `<h1>` da página. Rebaixado para `<p>` (é branding). Agora há
  **um único `<h1>` por página** = o título da página.

> As 28 páginas que já tinham título no corpo **não foram tocadas** (evita duplicar). O slot
> `header` permanece definido nelas, mas é inofensivo (ignorado pelo layout) — limpeza opcional
> futura.

## ✅ Fase 5 — teste visual real (Playwright)

- Novo script `scripts/visual-audit.mjs`: para cada página × breakpoint **320/375/768/1024/1440**
  mede overflow horizontal real (`scrollWidth > clientWidth`) e salva screenshots.
- Executado contra um **SQLite isolado e semeado** (o `.env` aponta para um Postgres/Supabase —
  **não foi tocado**; usei overrides de env só para o teste).
- **Resultado: 105 combinações página×breakpoint, 0 overflow horizontal, 0 erros de navegação.**
- Inspeção visual (eu mesmo abri os PNGs) de ~20 telas mobile (dashboard, listas, tabelas, forms,
  login, ranking, fluxo de caixa) — layout polido, sem quebras. Os títulos restaurados foram
  confirmados via `main h1` em todas as páginas corrigidas acessíveis.
- Artefatos descartáveis (DB de teste + screenshots) removidos ao final; `scripts/visual-audit.mjs`
  mantido como ferramenta.
- **Modo escuro:** varredura adicional (90 combinações, `theme=dark`) → 0 overflow, 0 telas sem
  dark aplicado; inspeção visual de ~7 telas confirmou contraste/legibilidade.

## Fase 4 — boas práticas (verificação)

Aplicado/conferido: viewport permite zoom (C1), inputs ≥16px (M2), `lang` correto, sem
`console.log`, `loading=lazy`/`alt` OK, scroll/resize com throttle, `!important` mínimo, tokens em
vez de valores mágicos. Itens de baixa prioridade pendentes registrados no `FRONTEND_AUDIT.md`.
