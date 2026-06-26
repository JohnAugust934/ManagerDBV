# Automações Claude Code — ManagerDBV

Guia de uso de tudo que configuramos para acelerar e endurecer o desenvolvimento do projeto.
Cobre **hooks** (automáticos), **subagents** (revisores especializados), **skills** (comandos
`/nome`), **MCP servers** (integrações externas) e a configuração de **convenções**.

---

## 0. Ativação (faça isto uma vez)

1. **Reinicie a sessão do Claude Code** na raiz do projeto. Isso carrega os hooks, subagents,
   skills e MCP servers novos.
2. **Aprove os MCP servers** quando o Claude Code perguntar (eles vêm do `.mcp.json` versionado):
   - `context7` e `playwright` rodam via `npx` (baixam na 1ª execução — precisa de Node/npm).
   - `github` é HTTP e pedirá **autenticação** (OAuth/token) no primeiro uso.
3. **Confira**: rode `/mcp` para ver os 3 servers conectados. Os subagents aparecem ao pedir uma
   revisão; as skills aparecem digitando `/`.
4. **Pré-requisitos**: PHP no PATH (os hooks chamam `php`), `vendor/bin/pint` instalado
   (`composer install`), Node/npm para os MCP via `npx`, e `gh` autenticado ajuda no GitHub MCP.

> Plugin opcional **frontend-design**: instale pelo marketplace (`/plugin`) se quiser ajuda de
> direção visual ao criar telas novas. Não é um arquivo deste repo.

---

## 1. Hooks — rodam sozinhos a cada edição

Configurados em `.claude/settings.local.json`; scripts em `.claude/hooks/`. Você não invoca nada.

| Hook | Quando dispara | O que faz |
|------|----------------|-----------|
| `pint-on-edit.php` | PostToolUse, ao editar `.php` | Formata **só o arquivo tocado** com Laravel Pint. Nunca bloqueia. |
| `related-tests.php` | PostToolUse, ao editar arquivo em `app/` | Acha `<Nome>Test.php` correspondente e roda **só esse teste** (`--compact`). Feedback de regressão em segundos. |
| `warn-sensitive.php` | PreToolUse, ao editar arquivo sensível | **Avisa** (não bloqueia) ao tocar `.env*`, `composer.lock`, `package-lock.json`, `config/backup.php`. |

**Proveito**: você nunca mais acumula débito de Pint nos arquivos que mexe, descobre testes
quebrados na hora, e é lembrado das armadilhas de backup/segredos antes de errar.

---

## 2. Subagents — revisores especializados (você pede a revisão)

Em `.claude/agents/`. São read-only: analisam e reportam, não editam. Peça em linguagem natural
("revise o isolamento multi-tenant deste diff") ou deixe o Claude acioná-los pelo contexto.

| Subagent | Acione ao mexer em… | Caça |
|----------|---------------------|------|
| `tenant-scope-reviewer` | Models, `app/Models/Scopes`, queries | global scope ausente/errado, vazamento cross-tenant, unique não-escopado |
| `ranking-sync-checker` | regras de pontuação, `AppServiceProvider`, `RankingController`, `Frequencia` | divergência entre a lógica de ranking **duplicada** |
| `backup-reviewer` | `config/backup.php`, `BackupController`, `BackupIntegrityVerifier`, comandos `backup:*` | violação das 7 invariantes de backup que já quebraram produção |
| `financeiro-reviewer` | `caixa`, `mensalidades`, models/controllers financeiros | autoria, precisão monetária, transações, isolamento `club_id` |
| `seguranca-lgpd-reviewer` | `Desbravador`, campos cifrados, convites, export/PDF | PII de menores vazando, cifragem em repouso, segredos |
| `ui-acessibilidade-reviewer` | `resources/views` (Blade) | desvio do `guia-visual-ui.md`, contraste WCAG AA, acessibilidade |

**Proveito**: cada uma das maiores fontes de bug do app tem um auditor dedicado que conhece as
regras específicas do ManagerDBV.

---

## 3. Skills — comandos sob demanda (digite `/nome`)

Em `.claude/skills/`. As de efeito colateral são **user-only** (só você invoca).

| Skill | Invocação | Para quê |
|-------|-----------|----------|
| `/pr-check` | user-only | Portão pré-PR: Pint `--test` no diff + `composer test` + gate `tenant:check-integrity`. Mesmo que o CI. |
| `/gen-test <alvo>` | user-only | Gera teste Pest no padrão multi-tenant (SQLite `:memory:`, dois clubes, autorização). |
| `/commit` | user-only | Commit em Conventional Commits pt_BR + trailer `Co-Authored-By`. Não faz push sozinho. |
| `/nova-migration` | user-only | Migration multi-tenant (FK/cascade, índices, unique por tenant, matriz 3 bancos). |
| `/restore` | user-only | Guia a restauração de backup pelo runbook `docs/RESTORE.md` com validação de integridade. |
| `/release` | user-only | Monta notas de release a partir dos commits desde a última tag. |
| `project-conventions` | Claude-only | Conhecimento de fundo das convenções; o Claude carrega sozinho quando relevante. |

**Proveito**: fluxos repetitivos e procedimentos críticos viram um comando, no padrão certo.

---

## 4. MCP servers — integrações externas

Em `.mcp.json` (versionado, todo o time recebe).

| Server | Use para |
|--------|----------|
| `context7` | Docs **ao vivo** de Laravel 12 / spatie-backup / dompdf — em vez de eu chutar de memória. Ex.: "como configurar retention no spatie/laravel-backup?" |
| `playwright` | Abrir páginas, tirar screenshot (mobile/desktop), validar a UI do `guia-visual-ui.md`. |
| `github` | PRs, issues e **logs de Actions** como ferramentas nativas, sem montar URLs da API na mão. |

---

## 5. Fluxo recomendado de uma feature

```
1. Codar               → Pint formata + teste relacionado roda (hooks, automático)
2. /gen-test <Novo>    → cobertura no padrão tenant
3. "revise multi-tenant deste diff"  → subagent apropriado (tenant/financeiro/lgpd/ui/backup)
4. Playwright: screenshot da tela nova (se UI)  → confere visual
5. /pr-check           → Pint + testes + integridade, tudo verde localmente
6. /commit             → mensagem no padrão; depois git push (peça explicitamente)
```

Para release: `/release` desde a última tag. Para desastre: `/restore`.

---

## 6. Manutenção

- **Adicionou um hook?** edite `.claude/settings.local.json` (bloco `hooks`) e valide com
  `php -l .claude/hooks/<arquivo>.php`.
- **Novo subagent/skill?** crie o `.md` em `.claude/agents/` ou `.claude/skills/<nome>/SKILL.md`.
- **Convenção mudou?** atualize o `CLAUDE.md` (fonte canônica) e, se for regra sutil, a skill
  `project-conventions`.
- Os scripts de hook são em **PHP** de propósito: portáveis entre Windows/Linux/CI, sem depender
  do shell.
