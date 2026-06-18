# Reestruturação Multi-Tenant — Plano de Endurecimento

> Status: proposta aprovada (decisões base) — pendente execução faseada.
> Decisões base: **banco de produção = MySQL**; **`club_id` direto em todas as
> tabelas de tenant** (desnormalização); **catálogo pedagógico permanece global
> read-only**.

O modelo conceitual (banco único compartilhado + isolamento por `club_id`) está
**correto e é mantido**. Não há reescrita do zero. Esta reestruturação corrige a
**camada de schema e de scope**, que foi montada de forma incremental e tem
buracos que impedem escalar com segurança.

---

## Diagnóstico (resumo)

| # | Severidade | Problema |
|---|---|---|
| 1 | 🔴 Crítico | `desbravadores`, `frequencias`, `mensalidades` sem `club_id` — isolamento indireto via `whereHas(unidade)`. Subquery em toda query; risco de órfão (`unidade_id` é `nullOnDelete`); vazamento se a relação for ignorada. |
| 2 | 🔴 Crítico | `club_id` sem FK e `nullable` em `caixas`, `patrimonios`, `eventos`, `atas`, `atos`, `ranking_snapshots` — sem integridade referencial, sem cascade, fail-open. |
| 3 | 🟠 Alto | Não existe exclusão de clube (offboarding/LGPD). Implementar sem FK espalharia órfãos. |
| 4 | 🟡 Médio | Três classes de scope divergentes (`ClubScope`, `DesbravadorClubScope`, `MensalidadeClubScope`) — fácil dessincronizar. |
| 5 | 🟡 Médio | Scopes curto-circuitam sem `auth()` → jobs/console enxergam todos os tenants. |
| 6 | 🟡 Médio | Catálogo global sem decisão explícita → **decidido: global read-only**. |
| 7 | 🟢 Menor | Uniques não escopadas por `club_id` (exceto `attendance_columns`, que é o modelo correto). |

---

## Fase 0 — Rede de segurança (pré-schema) ✅ EM ANDAMENTO

- [x] Comando `tenant:check-integrity` (`app/Console/Commands/CheckTenantIntegrity.php`):
  varre `club_id` nulo em tabela de tenant, `club_id` apontando para clube
  inexistente, usuário comum sem clube, e `desbravador` sem unidade / com unidade
  pendente. Retorna exit 1 se achar problema (`--json` para CI). Coberto por
  `tests/Feature/MultiTenant/TenantIntegrityCommandTest.php` (7 testes).
- [x] **Gate de CI**: passo em `.github/workflows/laravel.yml` roda
  `migrate:fresh --seed` + `tenant:check-integrity` (valida que os seeders
  produzem banco consistente por tenant). Rodado também contra o dev Supabase: OK.
- [ ] Expandir `tests/Feature/MultiTenant/` com teste de vazamento por **cada**
  model e **cada** pivô (inclusive via raw SQL e relacionamento). Parcial — os
  models principais já têm cobertura; faltam pivôs e `Frequencia` (ver achado).
- [ ] `backup:run` + congelar mudanças de schema na janela (no cutover).

> **Achado da Fase 0 (entra na Fase 1/4):** o model `Frequencia` **não tem global
> scope nenhum** — `Frequencia::all()` vaza entre clubes; só é protegido quando
> acessado via relação do desbravador. Idem para os pivôs
> (`desbravador_especialidade/requisito/evento`, `frequencia_column_values`), que
> não têm scope direto. A desnormalização da Fase 1 + o trait `BelongsToTenant`
> da Fase 4 fecham esse buraco ao dar `club_id` direto e scope próprio a essas
> tabelas.

## Fase 1 — `club_id` direto em todas as tabelas de tenant (desnormalização) ✅ FEITO

- [x] Migration `2026_06_18_000001_add_club_id_to_tenant_core_tables` adiciona
  `club_id` (nullable + índice) a `desbravadores`, `frequencias`, `mensalidades`,
  com **backfill** via UPDATE correlacionado portável (SQLite/MySQL/Postgres).
- [x] Trait `App\Models\Concerns\BelongsToTenant`: registra o `ClubScope` direto
  e **auto-preenche club_id ao criar** (ClubContext → fallback `resolveClubIdFromParent`).
  Aplicado a `Desbravador`, `Frequencia`, `Mensalidade`.
- [x] `DesbravadorClubScope` e `MensalidadeClubScope` **removidos** (substituídos
  pelo `ClubScope` direto via trait). `Mensalidade::scopeDoClube` agora usa
  `where club_id` direto. `Frequencia` ganhou global scope — antes **não tinha
  scope nenhum** (`Frequencia::all()` vazava). As telas atuais (Dashboard,
  FrequenciaController) já filtravam à mão via `whereHas`, então o scope é
  defesa-em-profundidade contra vazamento futuro, não correção de leak ativo.
  Esses `whereHas` ficaram redundantes — limpeza opcional na Fase 4.
- [x] Inserts em massa que pulam o auto-fill ajustados: `MensalidadeController`
  (`insert()` em massa) e `ClubImportService` (`DB::table()->insert`) agora setam
  `club_id` explicitamente.
- [x] `tenant:upgrade-legacy` estendido com as 3 tabelas; `tenant:check-integrity`
  agora cobre as 3 + **checks de divergência pai/filho** (club_id da filha precisa
  bater com o do pai).
- [x] Testes: `DesnormalizacaoClubIdTest` (5) + suíte completa verde (318 testes).

> **Revisão da Fase 1 (achados):**
> - 🔴 Corrigido (bug pré-existente): `App\Rules\UnidadePertenceAoClube` usava
>   `auth()->user()->club_id` em vez de `ClubContext::currentClubId()` — um
>   platform admin em **modo suporte** (club_id null) era rejeitado em toda
>   unidade e não conseguia criar/editar desbravador no clube atendido. Agora usa
>   `ClubContext`. Teste de regressão em `SuperAdminImpersonacaoTest`.
> - 🟢 `club_id` em `$fillable`: sem risco de mass-assignment — os controllers
>   usam `FormRequest::validated()` (club_id não é campo de formulário) e o
>   auto-fill seta via `setAttribute` (fora do fillable). Mantido por consistência
>   com Caixa/Evento/etc.
> - 🟢 Export/round-trip e ranking/snapshot revisados: corretos (import sobrescreve
>   club_id; snapshot em console usa short-circuit do scope sem auth).
>
> **Pivôs deferidos para a Fase 4:** `desbravador_especialidade/requisito/evento`
> e `frequencia_column_values` **não** receberam club_id nesta fase — são sempre
> acessados via pai já escopado (nunca query direta no app) e `attach()` não
> popula colunas extras facilmente. Serão tratados junto da consolidação do trait.
> Idem `Unidade`, que (descoberta) **não tem global scope** — é filtrada à mão nos
> controllers; receberá `ClubScope` na Fase 4.

## Fase 2 — Integridade referencial + cascade (MySQL/InnoDB) ✅ FEITO

Migration `2026_06_18_000002_enforce_club_id_integrity` (três fases internas):

- [x] **Cura ou aborta antes de qualquer DDL:** linhas com club_id nulo num banco
  de clube único são preenchidas com o único clube (legado single-tenant); se
  houver vários clubes com nulos, aborta com orientação (`tenant:upgrade-legacy`).
  Isto **substitui** a ideia anterior de tightening dentro do upgrade-legacy: a
  cura do single-tenant agora é da própria migration, e instalações novas também
  ganham NOT NULL (tabelas vazias no migrate → 0 nulos).
- [x] **`club_id` → NOT NULL** em: unidades, desbravadores, frequencias,
  mensalidades, caixas, patrimonios, eventos, atas, atos, ranking_snapshots.
- [x] **FK `club_id → clubs` com `cascadeOnDelete`** nas que ainda não tinham
  (todas as acima menos unidades). **Pulada no SQLite** (não adiciona FK via
  ALTER) — em MySQL/Postgres vale; a rede agnóstica continua sendo o
  `tenant:check-integrity`.
- [x] **`invitations.club_id` permanece NULLABLE** (descoberta na revisão): o
  convite de bootstrap do primeiro diretor é criado SEM clube. `users.club_id`
  idem (platform admin). O `tenant:check-integrity` separa tabelas estritas
  (null+pendente) de opcionais como invitations (só pendente).
- [x] Testes reenquadrados (cenários de club_id nulo agora são impossíveis por
  schema): `UpgradeLegacyTenantTest` foca em papéis de usuário; `RelatorioTest`
  e `TenantIntegrityCommandTest` ajustados; novo `EnforcedClubIdTest`. Suíte: 321.

> **Limitação consciente:** o `ON DELETE CASCADE` real só roda em MySQL/Postgres
> (dev/produção). O SQLite dos testes não tem a FK, então o cascade de exclusão de
> clube **não é exercido na suíte** — será coberto na Fase 6 por cascade em nível
> de aplicação (portável) + a FK como backstop nos bancos reais.
>
> `desbravadores.unidade_id` mantido `nullOnDelete` (com `club_id` próprio, perder
> a unidade não tira mais o desbravador do clube). Revisão para `restrict` fica
> como item aberto da Fase 6 (ciclo de vida).

## Fase 3 — Uniques e índices escopados por tenant ✅ FEITO

Migration `2026_06_18_000003_tenant_scoped_uniques_and_indexes`.

- [x] **CPF único POR CLUBE** (correção de correção, não só performance):
  `unique(club_id, cpf)` em `desbravadores` + regra de validação
  (`Store/UpdateDesbravadorRequest`) escopada por `ClubContext`. Antes o CPF era
  único global — impedia a mesma pessoa de existir em dois clubes. `cpf` nulo pode
  repetir (NULLs distintos no índice unique nos três bancos).
- [x] **Índices compostos `(club_id, <coluna quente>)`** substituindo os avulsos de
  `club_id` (a coluna líder também serve as consultas que filtram só por club_id):
  `desbravadores (club_id, ativo)`, `frequencias (club_id, data)`,
  `mensalidades (club_id, status)` e `(club_id, mes, ano)`,
  `caixas (club_id, data_movimentacao)`, `eventos (club_id, data_inicio)`.
  Os compostos são criados ANTES de remover o índice avulso — em MySQL a FK
  precisa de um índice com club_id na frente o tempo todo.
- [x] Testes: `TenantScopedUniqueTest` (CPF entre/no clube, nulo repetível).
  Suíte: 324. Gate + rollback/re-migração OK no SQLite.

> Uniques naturais já escopados por FK club-específica (ex.:
> `frequencias unique(desbravador_id, data)`, `mensalidades unique(desbravador_id,
> mes, ano)`) permanecem — desbravador_id já implica o clube. `invitations.email`
> segue único GLOBAL (e-mail é a chave de login; um usuário pertence a um clube).
> Índices avulsos secundários (`ativo`, `status`, `data`...) mantidos para as
> raras consultas cross-tenant (console/platform admin).

## Fase 4 — Trait único `BelongsToTenant`

- [ ] Trait que registra o global scope, **auto-preenche `club_id` no `creating`**
  via `ClubContext`, e expõe `club()`. Aposenta as 3 classes divergentes.
- [ ] Models de tenant passam a `use BelongsToTenant` (um ponto de verdade).

## Fase 5 — Contexto de tenant fora do HTTP

- [ ] `ClubContext::actAs(int $clubId, Closure $fn)` (set/restore) e scopes
  respeitando tenant explicitamente setado mesmo sem `auth()`.
- [ ] Jobs e comandos passam a rodar dentro de um tenant declarado.

## Fase 6 — Ciclo de vida do clube + catálogo

- [ ] `PlatformController::destroy` com cascade real (habilitado pela Fase 2);
  arquivar antes de apagar. LGPD-export já existe (`ClubExportService`).
- [ ] Catálogo (`classes`, `requisitos`, `especialidades`,
  `especialidade_requisitos`): **mantido global read-only**. Garantir que a UI
  não permita a clubes editar o catálogo; progresso do desbravador continua
  escopado por clube via `desbravador_*`.

## Fase 7 — Validação e cutover

- [ ] `tenant:check-integrity` como gate de deploy.
- [ ] Rollout em staging (Clube Beta / `TestClubSeeder`) → produção.
- [ ] `backup:run` antes de cada migration com FK.

---

## Compatibilidade de banco (SQLite / PostgreSQL / MySQL)

Três bancos em jogo simultâneo:

- **Testes Pest (CI/local):** SQLite `:memory:`
- **Dev local (`.env` → Supabase):** **PostgreSQL**
- **Produção:** **MySQL**

Vantagem: o dev local roda em Postgres, então as FKs/cascade da Fase 2 são
enforçados no dia a dia (violação aparece cedo). O SQLite dos testes é o único
ponto cego de FK — coberto pelo `tenant:check-integrity`.

Regras para toda migration desta reestruturação:

- [ ] Usar **apenas o Schema Builder** — zero SQL específico de dialeto. Se for
  inevitável, guardar por `DB::getDriverName()`.
- [ ] **FK + NOT NULL (Fase 2):** MySQL e Postgres aplicam in-place. **SQLite não
  adiciona FK a tabela existente via ALTER** (o Laravel recria a tabela; algumas
  FKs não são enforçadas). Portanto a integridade real é validada de forma
  **agnóstica de banco** pelo `tenant:check-integrity` (Fase 0), que roda igual
  nos três. As FKs continuam valendo em Postgres/MySQL.
- [ ] Testar cada migration de schema nos três drivers antes do cutover.

## Migração do legado single-tenant v4.0.0 → multi-tenant

Suportada via comando existente `tenant:upgrade-legacy` (transacional, idempotente,
`--dry-run`). Fluxo:

```
backup:run → migrate --force → tenant:upgrade-legacy --platform-admin=email → caches
```

Ajustes obrigatórios nesta reestruturação:

- [ ] **Estender `TABELAS_COM_CLUB_ID`** do comando com as tabelas desnormalizadas
  na Fase 1: `desbravadores`, `frequencias`, `mensalidades`,
  `desbravador_especialidade`, `desbravador_requisito`, `desbravador_evento`,
  `frequencia_column_values`. Sem isso, o upgrade do legado deixa essas tabelas
  órfãs.
- [ ] **Resolver conflito de ordem do NOT NULL (Fase 2):** num banco legado as
  linhas só recebem `club_id` quando o `tenant:upgrade-legacy` roda — DEPOIS do
  `migrate`. Logo, o `NOT NULL` **não pode** ser aplicado dentro do mesmo
  `migrate`. Desenho:
    1. `migrate` adiciona `club_id` + FK como **`nullable`**.
    2. O aperto para **`NOT NULL`** vira o **passo final do
       `tenant:upgrade-legacy`** (após o backfill, gated por zero-órfãos via
       `tenant:check-integrity`).
    3. Instalações novas: o seeder cria o clube antes, então não há nulos —
       mesmo caminho seguro vale para os dois cenários.
- [ ] Com `club_id` direto em `desbravadores`, o aviso atual de "desbravador sem
  unidade fica invisível" deixa de ser bloqueante: o backfill dá `club_id`
  direto, independente da unidade (melhoria de robustez).

## Ordem de execução recomendada

`Fase 0` → `Fase 1` → `Fase 2` → `Fase 3` → `Fase 4` → `Fase 5` → `Fase 6` → `Fase 7`.

As Fases 1+2 sozinhas eliminam ~80% do risco de estabilidade (órfãos, vazamento,
performance da tabela mais consultada).
