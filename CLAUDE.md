# CLAUDE.md

Este arquivo fornece orientação ao Claude Code (claude.ai/code) ao trabalhar com o código deste repositório.

## Visão Geral

O **Desbravadores Manager** (ManagerDBV) é uma aplicação web Laravel 12 / PHP 8.2+ para gestão
de clubes de Desbravadores: secretaria/membros, financeiro, pedagógico, eventos, patrimônio e
relatórios em PDF. A interface é Blade + Alpine.js + Tailwind CSS, com build via Vite. Banco
padrão é SQLite (também suporta PostgreSQL/MySQL).

O código e a linguagem de domínio são em **português (pt_BR)** — mantenha esse padrão ao nomear
controllers, rotas, models, colunas e textos de UI. O locale é forçado para `pt_BR` em
`AppServiceProvider`.

## Comandos

```bash
# Desenvolvimento
composer run dev        # Loop completo: php artisan serve + queue:listen + logs pail + vite (concurrently)
php artisan serve       # Apenas a aplicação em http://127.0.0.1:8000
npm run dev             # Servidor de dev do Vite
npm run build           # Build de frontend para produção
composer run setup      # Setup do zero: install + .env + key:generate + migrate + npm build

# Banco de dados
php artisan migrate --seed          # Migrar + dados de demonstração (DatabaseSeeder)
php artisan migrate:fresh --seed    # Reset completo com dados de demonstração

# Testes (Pest) — rodam em SQLite :memory:, não tocam o banco de dev
composer test                       # config:clear + artisan test (preferível — limpa config obsoleta)
php artisan test                    # Todos os testes
php artisan test --filter=BackupIntegrityVerifierTest   # Arquivo/classe único
php artisan test tests/Feature/RankingTest.php          # Teste único por caminho

# Formatação (Laravel Pint)
./vendor/bin/pint app/ database/ tests/          # Formatar
./vendor/bin/pint --test app/ database/ tests/   # Conferir sem escrever
```

### Logins de dev (seeder)

O `DatabaseSeeder` cria **5 clubes** (todos na cidade de São Paulo, um por Associação Paulista:
`orion`, `aurora`, `vega`, `sirius`, `antares`). Após `--seed`:

- **Platform admin (cross-tenant, sem clube):** `admin@plataforma.com`.
- **Por clube**, no padrão `<cargo>.<slug>@clube.com`: `master.`, `diretor.`, `secretaria.`,
  `tesoureiro.`, `instrutor.` e `conselheiro1.`–`conselheiro4.` (ex.: `diretor.orion@clube.com`).

Todos com a senha `password`. Cada clube tem 4 unidades, ~30 desbravadores distribuídos por todas
as classes, especialidades, 6 chamadas de frequência (ranking sem empates de pontuação), 5 eventos,
financeiro, patrimônio e 6 documentos.

## Arquitetura

Estrutura padrão do Laravel (Laravel 11/12 — sem `app/Console/Kernel.php` nem `app/Http/Kernel.php`;
tudo configurado em `bootstrap/app.php`). As partes não óbvias e transversais:

### Autorização: papel (role) + permissões de módulo via Gates
- Cada usuário (`App\Models\User`) tem um `role` (`master`, `diretor`, `secretario`,
  `tesoureiro`, `conselheiro`, `instrutor`) mais `extra_permissions` opcional (array JSON).
  `is_master` ainda existe por compatibilidade, mas a fonte da verdade é `role`.
- `User::temPermissao($modulo)` resolve o acesso: `master` → tudo; senão, os padrões do papel
  (`User::getPermissoesPadrao()`) combinados com `extra_permissions`. Os módulos são
  `gestao_acessos`, `financeiro`, `secretaria`, `unidades`, `pedagogico`, `eventos`, `relatorios`.
- Os Gates são definidos em `AppServiceProvider::boot()` e aplicados em `routes/web.php` via
  `middleware('can:<gate>')`. Para proteger uma funcionalidade, defina o Gate ali e adicione o
  middleware — não invente verificações ad-hoc.
- Gates especiais: `master` (só `role === 'master'`), `gerenciar-colunas-chamada`
  (`master`/`diretor`/`secretario`), e `gerir-unidade` (definido mas **ainda não ligado** a
  nenhuma rota — melhoria futura intencional; já suporta vínculo por `conselheiro_user_id` ou
  fallback pelo nome).

### Multi-tenancy: isolamento automático por `club_id` via global scopes
- Os dados de cada clube são isolados por `club_id`. Models com coluna `club_id` direta usam
  `App\Models\Scopes\ClubScope` (ex.: `Caixa`, `Evento`, `Patrimonio`, `Ata`, `Ato`, `Unidade`
  indireta); `Desbravador` **não** tem coluna direta e usa `DesbravadorClubScope` (filtra via
  `unidade.club_id`).
- Os scopes leem `auth()->user()->club_id` e fazem curto-circuito quando não há autenticação ou
  clube (assim seeders, factories e comandos de console sem usuário autenticado veem todas as
  linhas). O usuário `master` tem `club_id = null` → enxerga tudo.
- Ao adicionar um model com escopo de tenant, registre o global scope apropriado em `booted()`.

### Trilha de auditoria
- O trait `App\Models\Concerns\RegistraAutoria` preenche `created_by`/`updated_by` a partir do
  usuário autenticado nos eventos creating/updating. Atualmente usado em `caixas` e
  `desbravadores`. As colunas são anuláveis para que contextos sem autenticação não quebrem.
  Relações expostas: `criadoPor()` / `atualizadoPor()`.

### Frequência, colunas de chamada e pontuação
- Pontuação de presença é calculada em `Frequencia::getPontosAttribute()`. Há **dois modos**:
  - **Novo:** tabela `attendance_columns` (colunas configuráveis por clube, geridas via
    `AttendanceColumnService`) + `frequencia_column_values`. Colunas fixas padrão: Presente (10),
    Pontual (5), Bíblia (5), Uniforme (10).
  - **Legado:** colunas booleanas fixas em `frequencias` (`presente`/`pontual`/`biblia`/
    `uniforme`), usado quando a tabela `attendance_columns` não existe (`usesLegacyColumns()`).
    É dead code em instalações já migradas, mas continua entrelaçado no fluxo — não remover sem
    pedido.

### Ranking — lógica DUPLICADA, manter as duas em sincronia
- `AppServiceProvider::snapshotRankingYear(int $year, int $clubId)` (comando agendado
  `ranking:snapshot`, contexto de console — itera **por clube**) vs `RankingController` (telas ao
  vivo). Ambos agora filtram `club_id` + `no_ranking`. Qualquer mudança na regra de pontuação
  precisa tocar **as duas**.
- `Unidade::no_ranking` controla a participação no ranking (atenção: `no_ranking = true` significa
  **participa** — a coluna é mal-nomeada; ver `UnidadeController::toggleRanking`). Snapshots anuais
  são persistidos em `ranking_snapshots` (model `RankingSnapshot`, com `club_id`) para auditoria.

### Console & agendamento (padrão Laravel 11/12)
- O agendamento fica em **`routes/console.php`**, não num Kernel. Comandos personalizados são
  auto-registrados a partir de `app/Console/Commands` (via `withCommands` em `bootstrap/app.php`).
- Tarefas agendadas (timezone `America/Sao_Paulo`): `backup:run` (03:00), `backup:prune-manifests`
  (04:10), `backup:clean` (04:00), `backup:monitor` (04:30), `backup:deep-verify` (mensal, dia 1),
  `daily:backup-report` (05:00, resumo no Telegram), `ranking:snapshot` (anual, 01/01),
  `queue:monitor` (a cada 5 min, se `QUEUE_MONITOR_ENABLED`, pausado nas janelas
  `QUEUE_MONITOR_PAUSE_WINDOWS`).
- Exceções não tratadas são reportadas ao Telegram via `withExceptions` em `bootstrap/app.php`.
- Health check: `GET /health` (checa o PDO do banco, retorna 200/503) e `/up` (nativo Laravel).

### Sistema de backup (spatie/laravel-backup) — leia antes de mexer
Esse subsistema já causou várias falhas em produção; o histórico completo está na memória do
agente (`backup-gotchas-producao`). Invariantes-chave (ver `config/backup.php`):
- `source.files.include` é **apenas** `storage_path('app/public')` (uploads). **Nunca** volte a
  incluir `base_path()` — arquivos voláteis (sessão, cache, logs, `.git`, o zip temporário)
  quebravam o fechamento do zip com `ZipArchive::close(): Invalid argument`. O código da app está
  no Git; a restauração consome só o dump do banco + `/app/public/` (ver `BackupController::restore`).
  O banco é capturado via `source.databases`.
- A **criptografia do arquivo é opt-in** (`BACKUP_ARCHIVE_ENCRYPTION_ENABLED`, default off). Numa
  causa diferente do problema do zip volátil: a libzip do PHP 8.4/alt-php da Hostinger expõe
  `ZipArchive::EM_AES_256` mas não cifra de verdade → com senha, o spatie tenta cifrar e o
  `close()` quebra. Manter desligado a menos que o host tenha libzip que cifre de verdade.
- Discos de nuvem (`s3`/`r2`) sem bucket configurado são **descartados automaticamente** para não
  derrubar o backup inteiro (um disco com bucket nulo lança `TypeError` no Flysystem). O disco
  `r2` lê variáveis `R2_*` (não `AWS_*`).
- A pasta de destino do backup = `APP_NAME`. Mudar o `APP_NAME` orfana os backups antigos; por
  isso `BackupController::index` lista recursivamente.
- Camada própria de integridade além do spatie: `App\Services\BackupIntegrityVerifier` roda no
  evento `BackupWasSuccessful` (dispara **por disco**), localiza o zip mais recente, confere
  tamanho mínimo, reabre o zip exigindo o dump do banco + uploads, calcula SHA-256, grava
  `<zip>.manifest.json` (checksum por arquivo) ao lado do zip e uma linha em `backup_logs`
  (model `BackupLog`); falha → alerta IMEDIATO no Telegram. Config em `backup.integrity` (chave
  ignorada pelo spatie).
- Comandos próprios: `backup:prune-manifests` (remove `*.manifest.json` órfãos que o `backup:clean`
  deixa para trás + poda `backup_logs` > `BACKUP_LOG_RETENTION_DAYS`) e `backup:deep-verify`
  (mensal — abre e LÊ o dump dentro do zip: `.sql` checa CREATE TABLE/INSERT; `.sqlite` roda
  `PRAGMA integrity_check` via PDO).
- Retenção é controlada por `.env` (`BACKUP_RETENTION_DAYS` é o knob principal → `keep_all`;
  `BACKUP_KEEP_*` granulares; `BACKUP_MAX_STORAGE_MB` é o teto). A rotação real é do `backup:clean`
  do spatie — **não há lógica de delete própria**.
- `Command::fail()` já existe (público) no Laravel 11/12 — não defina um método privado `fail()`
  num comando (dá FatalError; use outro nome, ex.: `failWith`).

### Notificações & monitoramento operacional
- `App\Services\TelegramNotifier` envia alertas de backup, fila (`QueueBusy`/`JobFailed`) e
  exceções. `send()` trunca em 4096 caracteres (limite do Telegram). Suporta dedup de erros e
  supressão de erros transitórios de DB nas janelas de manutenção.
- `App\Services\ScheduledTaskTracker` registra resultados de tarefas da madrugada em Cache
  (chaveado por dia) para o `daily:backup-report` consolidar às 05:00 e depois limpar.
- `App\Support\OperationalWindow` faz parse/checagem de janelas de horário (formato `HH:MM-HH:MM`,
  suporta janelas que cruzam a meia-noite), usado para pausar monitor de fila/erros.

### Relatórios e PDFs
- `RelatorioController` gera PDFs com `barryvdh/laravel-dompdf`: autorização, carteirinha, ficha
  médica, financeiro, patrimônio, além de um hub de relatórios personalizados. `EventoController`
  também gera autorizações de evento em PDF.

## Mapa de rotas / módulos (ver `routes/web.php`)

| Prefixo / recurso | Gate | Controller |
|---|---|---|
| `dashboard`, `profile` | auth+verified | DashboardController, ProfileController |
| `usuarios`, `invites` | `gestao-acessos` | UsuarioController, InvitationController |
| `backups` | `master` | BackupController |
| `clube`, `atas`, `atos`, `desbravadores`, `unidades` (CRUD), eventos (CRUD) | `secretaria` | Club/Ata/Ato/Desbravador/Unidade/EventoController |
| `especialidades`, `classes`, `frequencia` | `pedagogico` | Especialidade/Classes/FrequenciaController |
| `frequencia/colunas` | `gerenciar-colunas-chamada` | AttendanceColumnController |
| `caixa`, `patrimonio`, `mensalidades` | `financeiro` | Caixa/Patrimonio/MensalidadeController |
| `eventos` (ver/inscrever) | `eventos` | EventoController |
| `ranking` | `relatorios` | RankingController |
| `relatorios` | `relatorios` (+`financeiro` nos sub) | RelatorioController |

Visualização geral (`unidades.index/show`, `desbravadores.show`) fica liberada a qualquer usuário
autenticado, para cargos como conselheiro. Registro só por convite (`/register-invite`).

## Convenções
- **Pint:** o projeto tem débito considerável de formatação pré-existente. Mantenha limpos
  **apenas os arquivos que você tocar**; não reformate o projeto inteiro sem ser solicitado.
- **Foco atual: endurecer qualidade** (robustez, testes, consistência de dados financeiros) em vez
  de novas features — priorize correção/cobertura ao propor próximos passos.
- **Soft deletes foram dispensados** por decisão de produto ("Excluir remove tudo em definitivo");
  a exclusão é em cascata por design.
- **UI:** siga `docs/guia-visual-ui.md` — mobile-first, `x-app-layout` + `ui-page`, botões de ação
  **fora** do header, classes `ui-btn-primary`/`ui-btn-secondary`/`ui-btn-danger` com
  `w-full sm:w-auto`, meta de contraste WCAG 2.1 AA. Telas de auth usam o layout guest (escuro)
  com classes bespoke — não usar componentes Breeze genéricos lá.
- **Seeders:** `DatabaseSeeder` (dev, dados demo completos) redireciona automaticamente para
  `MasterOnlySeeder` quando `app()->isProduction()`. Em produção, rode apenas
  `php artisan db:seed --class=MasterOnlySeeder`. No modelo multi-tenant, o `MasterOnlySeeder`
  cria **somente** o catálogo global (classes/especialidades) e o **admin da plataforma**
  (`admin@plataforma.com` / `password`, `is_platform_admin = true`, `club_id = null`) — **não**
  cria mais clube/master de exemplo. Clubes e seus usuários master passam a ser criados pelo painel
  da plataforma (`/platform`). O `DatabaseSeeder` tem um `SeederFallbackFaker` embutido para
  ambientes sem `fakerphp/faker` (`composer --no-dev`).
- **Imports em arquivos de rota (sem namespace):** o auto-Pint (hook pós-edição) poda imports
  considerados "não usados". Em arquivos sem `namespace` (ex.: `routes/*.php`), um `Controller::class`
  sem o `use` correspondente resolve para o nome **global curto** e quebra o `route:list`/dispatch.
  Ao adicionar uma rota, inclua o `use` e seu uso **na mesma edição** (ou adicione a rota antes do
  import) — nunca adicione o `import` isolado primeiro, pois o Pint o removerá antes de ele ser usado.

## Deploy
Guia completo em `docs/DEPLOY.md`; runbook de restauração em `docs/RESTORE.md`. Pontos-chave: `composer
install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `migrate --force`,
`storage:link`, `config:cache`/`route:cache`/`view:cache`, worker de fila via Supervisor
(`queue:work database`), cron de 1 minuto rodando `schedule:run`. Sempre `backup:run` antes de
deploy com migrations. Template de produção em `.env.production.example`.

APM_RULES {

## Validação ao concluir cada Task
- Toda Task termina com a suíte **verde**: rodar `composer test` (ou `php artisan test`) e garantir
  que não há falhas — incluindo os testes novos da Task. A baseline é 419 testes / 0 falhas; nenhuma
  Task pode introduzir regressão.
- Rodar `./vendor/bin/pint` **apenas nos arquivos criados/alterados pela Task** — nunca reformatar o
  projeto inteiro (ver "Convenções" acima sobre o débito pré-existente de formatação).

## Artefatos novos
- Código, testes, nomes de classe/rota/coluna e textos de UI em **pt_BR** (ver "Visão Geral").
- Testes em Pest sobre SQLite `:memory:`; não tocar o banco de dev. Reutilizar fixtures/factories e
  padrões dos testes já existentes em `tests/` antes de criar novos do zero.
- Views Blade novas seguem `docs/guia-visual-ui.md` (mobile-first, `x-app-layout`/`ui-page`, classes
  `ui-btn-*`, botões fora do header, contraste WCAG 2.1 AA). Telas de auth usam o layout guest bespoke.

## Invariantes a respeitar quando a Task tocar a área
- **Ranking:** qualquer mudança em pontuação/frequência mantém sincronizadas as duas implementações
  duplicadas (`AppServiceProvider::snapshotRankingYear` e `RankingController`) — ver "Ranking" acima.
- **Multi-tenancy:** models com dados de clube usam o global scope apropriado; queries que cruzam
  tenants ou usam `withoutGlobalScopes()` reaplicam filtro `club_id` explícito — ver "Multi-tenancy".
- **Backup:** não reincluir `base_path()` em `source.files.include`; criptografia de arquivo opt-in;
  ver "Sistema de backup" acima e a memória do agente antes de alterar o subsistema.
- **Exclusão é definitiva** (sem soft deletes); a cascata é por design — ver "Convenções".

## Versionamento (sessão APM)
- Modelo **gitflow**. Base branch da sessão: `multi-tenant` (linha estável; merge para `main` é
  decisão posterior do usuário). Cada unidade de trabalho usa uma branch `feature/<descrição-curta>`
  em pt_BR, sem termos de framework no nome.
- Commits em **Conventional Commits pt_BR com escopo**: `tipo(escopo): descrição` (tipos: feat, fix,
  refactor, docs, test, chore), seguindo o padrão já presente no histórico.
- Push para o `origin` (GitHub) é permitido nesta sessão.

} //APM_RULES
