# Reestruturação Multi-Tenant — Plano de Endurecimento

> **Status: Fases 0–6 (código) CONCLUÍDAS, testadas e revisadas.** Fase 7 é o
> cutover operacional (deploy). Suíte: **346 testes verdes**; gate de integridade
> e rollback OK no SQLite; Pint limpo nos arquivos tocados.
> Decisões base: **banco de produção = MySQL**; **`club_id` direto em todas as
> tabelas de tenant** (desnormalização); **catálogo pedagógico permanece global
> read-only**.
>
> | Fase | Tema | Migration | Status |
> |---|---|---|---|
> | 0 | Rede de segurança (`tenant:check-integrity` + gate CI) | — | ✅ |
> | 1 | `club_id` direto (desnormalização) + trait `BelongsToTenant` | 000001 | ✅ |
> | 2 | FK `cascade` + `NOT NULL` (com cura/abort pré-DDL) | 000002 | ✅ |
> | 3 | CPF único por clube + índices compostos | 000003 | ✅ |
> | 4 | Consolidação do scope (Unidade/AttendanceColumn/RankingSnapshot + pivôs) | — | ✅ |
> | 5 | `ClubContext::actAs` (tenant fora do HTTP) + 100% dos models no trait | — | ✅ |
> | 6 | Ciclo de vida: desativar/excluir clube + catálogo read-only | 000004 | ✅ |
> | 7 | Validação e cutover (deploy) | — | ⏳ operacional |

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

## Fase 0 — Rede de segurança (pré-schema) ✅ FEITO

- [x] Comando `tenant:check-integrity` (`app/Console/Commands/CheckTenantIntegrity.php`):
  varre `club_id` nulo em tabela de tenant, `club_id` apontando para clube
  inexistente, usuário comum sem clube, `desbravador` sem unidade / com unidade
  pendente, divergência pai/filho e **pivô cross-club** (Fase 4). Retorna exit 1
  se achar problema (`--json` para CI). Coberto por `TenantIntegrityCommandTest`.
- [x] **Gate de CI**: passo em `.github/workflows/laravel.yml` roda
  `migrate:fresh --seed` + `tenant:check-integrity` (valida que os seeders
  produzem banco consistente por tenant). Rodado também contra o dev Supabase: OK.
- [x] Cobertura de vazamento ampliada ao longo das fases: `Frequencia` ganhou
  scope (Fase 1), pivôs ganharam check de integridade cross-club (Fase 4), e há
  18 arquivos de teste em `tests/Feature/MultiTenant/`. O vazamento por relação
  está coberto; o de pivô é capturado pelo `tenant:check-integrity`.
- [ ] `backup:run` + congelar mudanças de schema na janela — **operacional, no
  cutover** (ver Fase 7).

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

## Fase 4 — Trait único `BelongsToTenant` ✅ FEITO

O trait em si nasceu na Fase 1 (scope direto + auto-fill de `club_id` + `club()`).
A Fase 4 fecha os pontos cegos restantes — modelos com `club_id` que ainda eram
filtrados À MÃO passam a ter o global scope:

- [x] **`Unidade`** passa a usar `BelongsToTenant` — era o gap principal: sem scope,
  o route-model binding não isolava (dependia de `abort(403)` manual nos
  controllers). Agora unidade de outro clube dá **404** (isolamento mais forte que
  403, que revelava a existência). `AttendanceColumn` e `RankingSnapshot` idem
  (removidos os `club()` redundantes — o trait fornece).
- [x] **Pontos cross-tenant ajustados** para `withoutGlobalScopes()`: contagem de
  unidades em `PlatformController` e o export de `unidades`/`attendance_columns`/
  `ranking_snapshots` em `ClubExportService` (rodam para um clube específico,
  possivelmente sem impersoná-lo). `Desbravador::resolveClubIdFromParent` também
  usa `withoutGlobalScopes()` na busca da unidade-pai.
- [x] **Pivôs sem `club_id` (decisão mantida):** `desbravador_especialidade/
  requisito/evento` e `frequencia_column_values` continuam SEM `club_id` próprio
  (são sempre acessados via pai já escopado, e `attach()` não popula colunas
  extras). Em vez de coluna, ganharam **verificação de integridade**:
  `tenant:check-integrity` agora detecta pivô ligando entidades de clubes
  diferentes (inscrição cross-club, valor de coluna cross-club).
- [x] Testes: `ConsolidacaoScopeTest` + `UnidadeTest` ajustado (403→404). Suíte: 328.

> `Invitation` permanece SEM scope: seu `club_id` é nullable (bootstrap) e já é
> filtrado à mão; um global scope com fail-closed esconderia convites de bootstrap.

> **Revisão da Fase 4 (achado):** dar scope ao `Unidade` fez todo `whereHas('unidade')`
> / `whereHas('desbravador.unidade')` passar a aplicar o ClubScope do Unidade dentro
> da subquery. Nos caminhos cross-tenant (`ClubExportService`, `PlatformController`),
> isso filtraria errado **sob impersonação** (frágil — em produção funcionava só
> porque export/painel são acessados sem impersonar). Corrigido trocando a travessia
> de relação por `club_id` direto (disponível desde a Fase 1) com `withoutGlobalScopes()`
> — mais robusto e rápido. Regressão coberta por
> `ConsolidacaoScopeTest::test_export_traz_o_clube_alvo_mesmo_impersonando_outro`.

## Fase 5 — Contexto de tenant fora do HTTP ✅ FEITO

- [x] **`ClubContext::actAs(int $clubId, Closure $fn)`** — fixa um tenant via
  override estático (set/restore em `finally`, aninhável, restaura em exceção).
  `currentClubId()` retorna o override quando ativo (vence o auth).
- [x] **`ClubScope` respeita o override:** filtra por `club_id` mesmo SEM `auth()`
  (antes, sem auth, via tudo). Default sem auth e sem override segue vendo tudo
  (seeders/console intactos). Único ponto a mudar — só existe um `ClubScope`.
- [x] **Consolidação final no trait:** `Caixa`, `Evento`, `Patrimonio`, `Ata`,
  `Ato` migraram de `ClubScope` direto para `BelongsToTenant` — assim ganham o
  **auto-fill** de `club_id`, e um job dentro de `actAs` pode criá-los sem informar
  o clube (antes daria `NOT NULL`). Agora 100% dos models de tenant usam o trait.
- [x] Testes: `TenantContextActAsTest` (filtra sem auth, auto-fill, aninhamento,
  restauração em exceção). Suíte: 333. Sem migration (só código).

> **Padrão para jobs/comandos futuros:** envolva o processamento de um clube em
> `ClubContext::actAs($clubId, fn () => ...)` — dentro dele, leitura e criação de
> qualquer model de tenant ficam isoladas automaticamente, sem filtrar à mão.
> Sem isso, código de console enxerga TODOS os clubes (os scopes só filtram com
> auth ou override).
>
> **⚠️ Job em fila NÃO herda o `actAs`** (achado da revisão): o override é estático
> e some quando o job é serializado e processado depois, noutro processo. O job
> deve **carregar o `club_id` no payload** e re-envolver o próprio `handle()` em
> `ClubContext::actAs($this->clubId, fn () => ...)`. Idem para chamadas de console
> que processam vários clubes: um `actAs` por clube.

## Fase 6 — Ciclo de vida do clube + catálogo ✅ FEITO

Reorganizada em torno da **desativação** (mais valiosa/segura que exclusão dura):

- [x] **Desativação/suspensão de clube** (uso comercial + admin): coluna
  `clubs.is_active`; middleware `EnsureClubIsActive` desloga e bloqueia usuários
  de clube desativado (platform admin é isento — reativa/dá suporte);
  `PlatformController::toggleActive` (gate platform-admin) + UI (badge + botão).
- [x] **Exclusão definitiva** (`PlatformController::destroy` + `ClubLifecycleService`):
  cascade EXPLÍCITO por club_id em ordem de dependência, em transação — portável
  nos 3 bancos (não depende do FK cascade ausente no SQLite). Trava: só exclui
  clube já DESATIVADO. Teste valida zero-órfãos (`tenant:check-integrity`).
- [x] **Catálogo global read-only para clubes** (decisão confirmada): a EDIÇÃO de
  especialidades/requisitos (compartilhados) migrou de `can:pedagogico` para
  `can:platform-admin`; clubes só LEEM o catálogo e registram o progresso dos
  desbravadores (que continua deles). Botões de escrita escondidos via
  `@can('platform-admin')`. Gotcha resolvido: `especialidades/{especialidade}`
  com `->whereNumber()` para não capturar `/especialidades/create`.
- [x] Testes: `ClubDeactivationTest`, `ClubDeletionTest`, `ClubCatalogReadOnlyTest`;
  `EspecialidadeTest`/`ClassesSystemTest` ajustados (escrita de catálogo agora via
  admin em modo suporte). Suíte: 346.

> Nota: o platform admin gerencia o catálogo entrando em modo suporte a um clube
> (as telas de catálogo são club-styled; ele edita o catálogo GLOBAL de lá).

### Fase 6 — detalhes originais do plano

- [x] `PlatformController::destroy` com cascade — feito em nível de aplicação
  (`ClubLifecycleService`, portável) em vez de depender só do FK cascade.
  LGPD-export já existe (`ClubExportService`). Em vez de "arquivar antes de
  apagar", o fluxo é **desativar → excluir** (a desativação é a forma reversível).
- [x] Catálogo (`classes`, `requisitos`, `especialidades`,
  `especialidade_requisitos`): **mantido global read-only**. Escrita migrou para
  `can:platform-admin` (+ `Gate::authorize` interno nos controllers) e os botões
  escondidos via `@can`. Progresso do desbravador continua por clube via
  `desbravador_*`.

## Fase 7 — Validação e cutover ⏳ OPERACIONAL (pendente do deploy)

Esta fase não é código — é o procedimento de deploy. As Fases 0–6 (código) estão
**100% concluídas, testadas e revisadas**.

- [x] `tenant:check-integrity` já é gate de CI.
- [ ] Aplicar `php artisan migrate` no **dev Postgres** (faltam as migrations 000003
  da Fase 3 e 000004 da Fase 6) + `tenant:check-integrity`.
- [ ] Rollout em staging (Clube Beta / `TestClubSeeder`) → produção.
- [ ] `backup:run` antes de cada `migrate` com FK/NOT NULL em produção.
- [ ] Em produção legada single-tenant: `backup:run` → `migrate --force`
  (a Fase 2 cura os nulos) → `tenant:upgrade-legacy --platform-admin=email`
  (define o super admin e vincula usuários órfãos) → `config:cache`/`route:cache`.

---

## Compatibilidade de banco (SQLite / PostgreSQL / MySQL)

Três bancos em jogo simultâneo:

- **Testes Pest (CI/local):** SQLite `:memory:`
- **Dev local (`.env` → Supabase):** **PostgreSQL**
- **Produção:** **MySQL**

Vantagem: o dev local roda em Postgres, então as FKs/cascade da Fase 2 são
enforçados no dia a dia (violação aparece cedo). O SQLite dos testes é o único
ponto cego de FK — coberto pelo `tenant:check-integrity`.

Regras seguidas em toda migration desta reestruturação:

- [x] **Apenas o Schema Builder** — zero SQL específico de dialeto. O único raw
  SQL é o backfill correlacionado da Fase 1, que é SQL padrão (roda nos três).
- [x] **FK + NOT NULL (Fase 2):** MySQL e Postgres aplicam in-place. **SQLite não
  adiciona FK a tabela existente via ALTER** → a FK é **pulada por driver** no
  SQLite (`DB::getDriverName()`), e a integridade real é validada de forma
  **agnóstica de banco** pelo `tenant:check-integrity`. As FKs valem em Postgres/MySQL.
- [x] Toda migration testada no SQLite (suíte + gate + rollback). Aplicação no dev
  Postgres confirmada pelo usuário nas Fases 1–2; Fases 3 e 6 pendentes de `migrate`
  no dev (ver Fase 7).

## Migração do legado single-tenant v4.0.0 → multi-tenant

Suportada via comando existente `tenant:upgrade-legacy` (transacional, idempotente,
`--dry-run`). Fluxo:

```
backup:run → migrate --force → tenant:upgrade-legacy --platform-admin=email → caches
```

Ajustes desta reestruturação (todos resolvidos):

- [x] **`TABELAS_COM_CLUB_ID` estendida** com `desbravadores`, `frequencias`,
  `mensalidades`. Os pivôs (`desbravador_*`, `frequencia_column_values`)
  **não** entram — por decisão eles ficaram sem `club_id` (são limpos pelas FKs
  naturais do pai). 
- [x] **Conflito do NOT NULL resolvido por um caminho melhor que o planejado:** em
  vez de o `NOT NULL` virar passo do `tenant:upgrade-legacy`, a própria migration
  da Fase 2 (`enforce_club_id_integrity`) **cura** os nulos de um banco de clube
  único antes de aplicar o `NOT NULL` (ou aborta com orientação se houver
  ambiguidade). Assim instalações novas E legado single-tenant ganham `NOT NULL`
  no `migrate`, sem depender do comando. O `tenant:upgrade-legacy` segue cuidando
  de papéis de usuário (platform admin, órfãos) e do backfill no cenário
  multi-clube de recuperação.
- [x] Com `club_id` direto em `desbravadores`, "desbravador sem unidade" deixou de
  ser bloqueante — o backfill dá `club_id` direto, independente da unidade.

## Ordem de execução recomendada

`Fase 0` → `Fase 1` → `Fase 2` → `Fase 3` → `Fase 4` → `Fase 5` → `Fase 6` → `Fase 7`.

As Fases 1+2 sozinhas eliminam ~80% do risco de estabilidade (órfãos, vazamento,
performance da tabela mais consultada).
