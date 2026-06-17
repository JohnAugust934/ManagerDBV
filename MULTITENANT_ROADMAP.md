# Roadmap Multi-Tenant — ManagerDBV

> Implementação própria de multi-tenancy (banco único compartilhado, isolamento por `club_id`)
> com painel de plataforma (super admin) e impersonação de clube.

## Status Geral
- [x] Fase 1: Fundação e correção de lacunas
- [x] Fase 2: ClubContext e atualização dos Scopes
- [x] Fase 3: Painel de Plataforma (Super Admin)
- [x] Fase 4: Onboarding de novo clube
- [x] Fase 5: Backup completo + Exportação por clube
- [x] Fase 6: Clube de teste e suite de testes multi-tenant
- [ ] Fase 7: Checklist de produção

---

## Decisões de Arquitetura
- **Especialidades e Classes:** catálogo GLOBAL compartilhado (não recebem `club_id`).
- **Abordagem:** banco único compartilhado com isolamento por `club_id` via Global Scopes.
- **Super admin:** coluna `is_platform_admin` na tabela `users` (`club_id = null`).
- **Contexto ativo:** `session('club_context')` para impersonação de clube pelo platform admin.
- **Sem pacote externo de multi-tenancy** — implementação própria via `App\Services\ClubContext`.
- **Backup completo** (`spatie/laravel-backup`) é responsabilidade da plataforma (platform admin).
  Masters de clube usam apenas a **exportação por clube** (JSON via `ClubExportService`).
- **Regra do Gate `master`:** continua sendo o "dono do clube" (`role === master && ! is_platform_admin`),
  distinto do platform admin. Platform admin é cross-tenant; master é dono de um único clube.
- **Fail-closed:** usuário autenticado **sem** clube ativo e **sem** ser platform admin não enxerga
  nenhum dado escopado (`whereRaw('1=0')`). Seeders/console (sem auth) continuam vendo tudo.
  Consequência: vários testes que criavam usuários sem `club_id` foram ajustados para vincular
  usuário e dados ao mesmo clube.
- **Cache de especialidades:** catálogo é global, mas `withCount('desbravadores')` é escopado por
  clube — a chave de cache da listagem passou a incluir `ClubContext::currentClubId()`.

---

## FASE 1 — Fundação: correção das lacunas existentes
**Objetivo:** fechar as lacunas de isolamento já existentes antes de introduzir o super admin.

### Tarefas
- [x] Migration: adicionar `is_platform_admin` (boolean, default false) em `users`
- [x] Migration: adicionar `club_id` a `ranking_snapshots` + trocar unique `(year,scope)` → `(year,scope,club_id)`
- [x] `RankingSnapshot`: adicionar `club_id` ao fillable + relação `club()`
- [x] `Mensalidade`: adicionar GlobalScope de clube (via `desbravador.unidade.club_id`) mantendo `scopeDoClube`
- [x] `InvitationController`: remover `Club::first()`, usar `auth()->user()->club` e filtrar `index()` por clube
- [x] `AppServiceProvider::snapshotRankingYear()`: aceitar `int $clubId` e filtrar queries
- [x] `routes/console.php` / chamadas do snapshot: iterar por clube
- [x] `RankingController`: snapshot ao vivo grava/lê `club_id` (corrige unique e leitura cross-tenant)
- [x] `UserFactory`: default `is_platform_admin => false` + state `platformAdmin()`
- [x] `User`: adicionar `is_platform_admin` ao fillable/casts + método `isPlatformAdmin()`

### Arquivos
- `database/migrations/*_add_is_platform_admin_to_users_table.php` (novo)
- `database/migrations/*_fix_ranking_snapshots_for_multitenant.php` (novo)
- `app/Models/RankingSnapshot.php`, `app/Models/Mensalidade.php`, `app/Models/User.php`
- `app/Http/Controllers/InvitationController.php`
- `app/Providers/AppServiceProvider.php`, `routes/console.php`
- `database/factories/UserFactory.php`

### Testes
- `tests/Feature/MultiTenant/IsolamentoBasicoTest.php`

### Verificação
`php artisan test` verde; usuário do clube A não vê dados (Caixa/Ata/Evento/Patrimônio/Mensalidade/Snapshot) do clube B.

---

## FASE 2 — ClubContext e atualização dos Scopes
**Objetivo:** centralizar a resolução do clube ativo e habilitar impersonação segura.

### Tarefas
- [x] Criar `app/Services/ClubContext.php` (`currentClubId`, `isPlatformAdmin`, `isImpersonating`, `currentClub`)
- [x] `ClubScope`: usar `ClubContext::currentClubId()` + regra fail-open (platform admin) / fail-closed (comum)
- [x] `DesbravadorClubScope`: mesma lógica
- [x] `MensalidadeClubScope`: mesma lógica
- [x] `AppServiceProvider`: gate `platform-admin`; `master` distinto de platform admin
- [x] Controllers que leem `club_id` cru (Dashboard/Ranking) passam a usar `ClubContext`

### Arquivos
- `app/Services/ClubContext.php` (novo)
- `app/Models/Scopes/ClubScope.php`, `app/Models/Scopes/DesbravadorClubScope.php`
- `app/Providers/AppServiceProvider.php`
- `app/Http/Controllers/DashboardController.php`, `RankingController.php`

### Testes
- `tests/Feature/MultiTenant/ClubContextTest.php`

### Verificação
Platform admin sem contexto vê tudo; com contexto vê só o clube da sessão; usuário comum jamais cruza tenant.

---

## FASE 3 — Painel de Plataforma (Super Admin)
**Objetivo:** painel cross-tenant com impersonação.

### Tarefas
- [ ] Grupo de rotas `platform.*` (`can:platform-admin`)
- [ ] `PlatformController` (`index`, `enterClub`, `exitClub`, `exportClub`, `createClub`, `storeClub`)
- [ ] Views `platform/index` e `platform/create-club`
- [ ] Banner de impersonação no layout `app.blade.php`
- [ ] Link de menu para `/platform` quando platform admin

### Testes
- `tests/Feature/MultiTenant/PlatformAdminTest.php`

### Verificação
`/platform` 200 p/ admin, 403 p/ comum; enter/exit alteram filtro de queries. ✔ (PlatformAdminTest)

> Nota de implementação: as rotas `backups.*` foram mantidas com o mesmo nome, apenas
> trocando o middleware de `can:master` para `can:platform-admin` (em vez de aninhar sob
> `platform.*`), evitando renomear rotas referenciadas no layout. O efeito é o exigido:
> backup completo só para platform admin; master de clube perde o acesso.

---

## FASE 4 — Onboarding de novo clube
**Objetivo:** criar clube + master a partir do painel.

### Tarefas
- [x] `PlatformController@storeClub`: cria `Club` + master inicial, redireciona
- [x] `MasterOnlySeeder`: platform admin (`admin@plataforma.com`, `club_id=null`) + clube base + dados mínimos
- [x] `DatabaseSeeder`: `admin@clube.com` vira platform admin + 2º clube (Aurora) com dados distintos

### Verificação
Criar clube pelo painel gera clube + master vinculado isolado.

---

## FASE 5 — Backup completo + exportação por clube
### Tarefas
- [x] `backups.*` restrito a platform admin (middleware `can:platform-admin`)
- [x] Master de clube perde acesso ao backup completo
- [x] `ClubExportService` (JSON por clube)
- [x] `PlatformController@exportClub` (download)
- [x] Rota `GET /clube/exportar-dados` (`club.export`) p/ master exportar o próprio clube
- [x] Botões na UI (painel + config do clube)

### Testes
- `tests/Feature/MultiTenant/BackupExportTest.php`

---

## FASE 6 — Suite multi-tenant + clube de teste
### Tarefas
- [x] `ClubFactory` criado; factories de Caixa/Evento/Patrimonio/Ata/Ato/Unidade com `club_id` + state `forClube()`
- [x] Helper `criarClubeComDados()` em `tests/Pest.php`
- [x] `tests/Feature/MultiTenant/IsolamentoTenantTest.php`
- [x] `tests/Feature/MultiTenant/SuperAdminImpersonacaoTest.php`
- [x] `database/seeders/TestClubSeeder.php` (Clube Beta: 5 usuários, 3 unidades, 15 desbravadores)
- [x] Suite completa verde (288 passando)

---

## FASE 7 — Checklist de produção
- [x] Migrations sobem em banco limpo (`migrate:fresh`), com `down()` funcional (`migrate:rollback` testado)
- [x] Constraints únicas multi-tenant corretas (`ranking_snapshots` unique `(year, scope, club_id)`)
- [x] Índices em `club_id` (caixas/patrimonios/eventos/atas/atos + ranking_snapshots; unidades via FK)
- [x] Isolamento garantido em rotas autenticadas (fail-closed p/ usuário sem clube)
- [x] `platform-admin` bloqueia `/platform` p/ comum (PlatformAdminTest)
- [x] Exportação restrita ao próprio clube (master) — `club.export` com `can:master`
- [x] `php artisan test` 100% sem skips (288 passando)
- [x] Seeder com platform admin + clube base (MasterOnlySeeder)
- [x] `DEPLOY.md` atualizado (seção multi-tenant)
- [x] Cache de especialidades com namespace de clube; `config:cache`/`migrate:fresh` ok

## Status Final
✅ Implementação completa. Suite: **297 testes / 951 asserções, verde**.
Baseline antes da missão: 259 testes. Adicionados 38 testes (7 arquivos em `tests/Feature/MultiTenant/`
+ ajustes em RankingTest).

## Pós-revisão — endurecimento de produção
Auditoria dos caminhos **fora dos global scopes** (criação, gestão, registro, impersonação)
revelou e corrigiu:

1. **`UsuarioController` (cross-tenant — segurança):** usava `isMaster()` para "ver/gerenciar
   todos". Agora só `isPlatformAdmin()` é cross-tenant; master de clube fica restrito ao próprio
   clube (`index`, `store`, `ensureCanManageTargetUser` usam `ClubContext`).
2. **`RegisteredUserController` (segurança/correção):** vinculava o convidado a `Club::first()`.
   Agora herda o `club_id` do **convite** (cada convite carrega seu clube).
3. **`ClubController` onboarding:** o "sweep" `whereNull('club_id')` passou a excluir
   `is_platform_admin` para não arrastar o super admin para um clube.
4. **Impersonação completa:** ~12 controllers liam `auth()->user()->club_id` direto (criação
   gerava registros órfãos e telas vinham vazias no modo suporte). Todos passaram a usar
   `ClubContext::currentClubId()` (Caixa/Ato/Ata/Evento/Patrimonio/Unidade/Desbravador/
   Mensalidade/Frequencia/AttendanceColumn/Relatorio/Invitation). `ClubController@exportarDados`
   permanece em `auth()->user()->club` por ser exclusivo do master do clube.

Testes adicionados em `tests/Feature/MultiTenant/IsolamentoControllersTest.php` (5).

## Pós-revisão 2 — pontos opcionais tratados
1. **Platform admin sem clube ativo:** novo middleware `EnsureClubContextForPlatformAdmin`
   (aplicado ao grupo autenticado) redireciona o platform admin sem contexto para `/platform`,
   evitando que telas escopadas mostrem dados de todos os clubes mesclados (fail-open). Libera
   `platform.*`, `backups.*`, perfil e ajuda. Em modo suporte (impersonando) tudo é liberado.
2. **Índices em `club_id`:** migration `2026_06_17_000003` adiciona índices em
   `attendance_columns` e `invitations` (as demais tabelas já tinham).
3. **`no_ranking` consistente:** `snapshotRankingYear` agora filtra `no_ranking = true` (unidades
   e desbravadores), igual ao ranking ao vivo. CLAUDE.md atualizado (coluna `no_ranking=true`
   significa **participa** do ranking).

Testes adicionais: middleware (3 em `PlatformAdminTest`) e exclusão por `no_ranking`
(1 em `RankingTest`). Suite: **297 testes / 951 asserções, verde**.

## Migração single-tenant → multi-tenant (upgrade in-place)
- Comando `tenant:upgrade-legacy` (`app/Console/Commands/UpgradeLegacyTenant.php`):
  backfill de `club_id` em todas as tabelas escopadas + mapeamento de papéis
  (`--platform-admin`), transacional, idempotente, com `--dry-run` e `--club=ID`.
  Nunca arrasta platform admins para um clube; avisa sobre desbravadores sem unidade.
- Procedimento completo documentado em `UPGRADE-MULTITENANT.md` (backup → migrate →
  dry-run → upgrade → validar; + rollback e nota sobre consolidar várias instalações).
- Testes: `tests/Feature/MultiTenant/UpgradeLegacyTenantTest.php` (6). Suite:
  **303 testes / 975 asserções, verde**.

### Consolidação de instalações: export/import por clube (par completo)
- `ClubExportService` agora exporta **tudo** de um clube (usuários com hash de senha,
  unidades, desbravadores, frequências + valores de coluna, financeiro, eventos +
  inscrições, documentos, patrimônio + manutenções, snapshots, especialidades e
  progresso de classe), com seção `_catalogo` (chaves naturais de classes/
  especialidades/requisitos). `FORMAT_VERSION = 2`.
- `ClubImportService` importa o JSON como **novo clube**, em transação, remapeando todas
  as PKs/FKs e resolvendo o catálogo por chave natural; não duplica usuários por e-mail;
  retorna relatório (contagens + avisos).
- Comando `tenant:import-club {file} {--name=}` (`app/Console/Commands/ImportClub.php`).
- Teste de ida-e-volta cobrindo todas as tabelas:
  `tests/Feature/MultiTenant/ClubExportImportRoundTripTest.php` (2).
- Procedimento documentado em `UPGRADE-MULTITENANT.md` (seção "Consolidar várias
  instalações"). Limite conhecido: binários (logo/fotos) não viajam no JSON.
- Suite: **305 testes / 1002 asserções, verde**.
