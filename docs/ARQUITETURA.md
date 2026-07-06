# Arquitetura — ManagerDBV

Visão de arquitetura do sistema para quem precisa entender **como** ele funciona por
dentro: isolamento de dados, autorização, frequência/ranking, backup e agendamento.
É o complemento "humano" do `CLAUDE.md` (que é voltado para trabalho assistido por IA).

> Estrutura padrão de Laravel 12 (sem `Console/Kernel.php` nem `Http/Kernel.php` — tudo
> em `bootstrap/app.php`). Domínio e UI em **pt_BR**; locale forçado em `AppServiceProvider`.

---

## 1. Multi-tenancy — isolamento por `club_id`

Banco **único e compartilhado**; cada clube é um tenant isolado pela coluna `club_id`.

- Todos os models de clube usam o trait **`App\Models\Concerns\BelongsToTenant`**, que
  registra o global scope **`App\Models\Scopes\ClubScope`**. O scope filtra
  automaticamente todas as queries pelo clube ativo.
- Models **sem `club_id` direto** (`Desbravador`, `Mensalidade`) também usam
  `BelongsToTenant`, mas resolvem o clube por relação: `Desbravador` via
  `unidade.club_id`, `Mensalidade` via `desbravador`. Não existem classes de scope
  separadas (`DesbravadorClubScope`/`MensalidadeClubScope`) — é o mesmo `ClubScope`.
- **Fail-closed:** sem clube ativo, o usuário comum **não vê nada**. O scope faz
  curto-circuito quando não há autenticação/clube (seeders, factories e comandos de
  console sem usuário veem todas as linhas).
- O clube ativo é resolvido por **`App\Services\ClubContext`**. Fora do HTTP (jobs,
  commands), use `ClubContext` para propagar o tenant explicitamente.
- O **platform admin** (`is_platform_admin = true`, `club_id = null`) enxerga tudo.

> Ao adicionar um model com dados de clube, use o trait `BelongsToTenant`. Queries que
> cruzam tenants ou usam `withoutGlobalScopes()` **devem** reaplicar o filtro `club_id`
> explicitamente.

### Platform admin × master

| | `platform_admin` | `master` |
|---|---|---|
| Escopo | Cross-tenant (todos os clubes) | Um clube específico |
| `club_id` | `null` | do clube |
| Painel | `/platform` (cria/suspende clubes, modo suporte) | telas normais do clube |
| Backups | Backup completo do sistema (Spatie) | "Backup do Clube" (isolado) |

---

## 2. Autorização — papel + permissões de módulo via Gates

- Cada `User` tem um `role` (`master`, `diretor`, `secretario`, `tesoureiro`,
  `conselheiro`, `instrutor`) + `extra_permissions` opcional (array JSON).
- `User::temPermissao($modulo)` resolve o acesso: `master` → tudo; senão, os padrões
  do papel (`User::getPermissoesPadrao()`) combinados com `extra_permissions`.
- **Módulos:** `gestao_acessos`, `financeiro`, `secretaria`, `unidades`, `pedagogico`,
  `eventos`, `relatorios`.
- Os **Gates** são definidos em `AppServiceProvider::boot()` e aplicados em
  `routes/web.php` via `middleware('can:<gate>')`. Para proteger uma funcionalidade:
  defina o Gate ali e adicione o middleware — não invente verificações ad-hoc.
- Gates especiais: `master` (só `role === 'master'`), `gerenciar-colunas-chamada`
  (`master`/`diretor`/`secretario`), e `gerir-unidade` (definido mas **ainda não
  ligado** a nenhuma rota — melhoria futura).
- Registro de usuários **apenas por convite** (`/register-invite`).

---

## 3. Frequência, colunas de chamada e ranking

### Frequência e pontuação

Pontuação de presença em `Frequencia::getPontosAttribute()`. Dois modos:

- **Novo (atual):** tabela `attendance_columns` (colunas configuráveis por clube, via
  `AttendanceColumnService`) + `frequencia_column_values`. Padrões: Presente (10),
  Pontual (5), Bíblia (5), Uniforme (10).
- **Legado:** colunas booleanas fixas em `frequencias`, usado quando
  `attendance_columns` não existe. Dead code em instalações migradas, mas ainda
  entrelaçado no fluxo — não remover sem pedido.

### Ranking — lógica DUPLICADA

A regra de pontuação existe em **dois lugares que precisam ficar em sincronia**:

1. `AppServiceProvider::snapshotRankingYear()` — comando agendado `ranking:snapshot`
   (console, itera **por clube**).
2. `RankingController` — telas ao vivo.

Ambos filtram `club_id` + `no_ranking`. **Qualquer mudança na regra de pontuação
precisa tocar os dois.** Snapshots anuais ficam em `ranking_snapshots` (com `club_id`).

> ⚠️ `Unidade::no_ranking = true` significa **participa** do ranking (coluna mal-nomeada;
> ver `UnidadeController::toggleRanking`).

---

## 4. Trilha de auditoria

O trait `App\Models\Concerns\RegistraAutoria` preenche `created_by`/`updated_by` a
partir do usuário autenticado (eventos creating/updating). Usado em `caixas` e
`desbravadores`. Colunas anuláveis (contextos sem auth não quebram). Relações:
`criadoPor()` / `atualizadoPor()`. Há também `caixa_audit_logs` para lançamentos.

---

## 5. LGPD

O sistema trata dados pessoais (e sensíveis) de **menores**. Medidas implementadas:

- **Consentimento** dos responsáveis registrado no cadastro de desbravadores
  (com data/IP); aceite de termos no registro via convite (`termos_aceitos_em`).
- **Criptografia em repouso** (AES-256 via `APP_KEY`) de CPF, RG e campos médicos;
  `cpf_hash` (SHA-256) sustenta a unique constraint por clube. Exibição **mascarada**.
- **ROPA** (`lgpd_registros`) — registro de operações de tratamento via observer.
- **Retenção:** `lgpd:anonimizar-desligados --force` anonimiza desligados após o prazo
  (agendado). Política de privacidade/termos públicos (`/privacidade`, `/termos`).

> Detalhes operacionais do upgrade que introduziu esses campos em
> [`UPGRADE-MULTITENANT.md`](UPGRADE-MULTITENANT.md).

---

## 6. Sistema de backup

`spatie/laravel-backup` + camada própria de integridade. **Leia antes de mexer** —
invariantes que já causaram falhas em produção (ver `config/backup.php`):

- `source.files.include` é **apenas** `storage_path('app/public')` (uploads). **Nunca**
  reincluir `base_path()` (arquivos voláteis quebram o fechamento do zip). O código está
  no Git; a restauração consome só o dump do banco + `/app/public/`.
- **Criptografia do arquivo é opt-in** (`BACKUP_ARCHIVE_ENCRYPTION_ENABLED`, default off).
- Discos de nuvem (`s3`/`r2`) sem bucket são descartados automaticamente. O disco `r2`
  lê variáveis `R2_*`.
- Pasta de destino = `APP_NAME` (mudar o nome orfana backups antigos; o index lista
  recursivamente).
- **Integridade própria:** `BackupIntegrityVerifier` (no evento `BackupWasSuccessful`)
  confere tamanho, reabre o zip, calcula SHA-256, grava `<zip>.manifest.json` e uma linha
  em `backup_logs`. Falha → alerta imediato no Telegram.
- Comandos próprios: `backup:prune-manifests`, `backup:deep-verify` (mensal — lê o dump
  dentro do zip).

---

## 7. Console, agendamento e monitoramento

- Agendamento em **`routes/console.php`** (não num Kernel). Comandos personalizados
  auto-registrados de `app/Console/Commands`.
- Tarefas (timezone `America/Sao_Paulo`): backups de madrugada, `ranking:snapshot`
  (anual, 01/01), `queue:monitor` (se habilitado), relatório diário no Telegram.
- **`TelegramNotifier`** envia alertas de backup, fila e exceções (trunca em 4096 chars).
  `ScheduledTaskTracker` consolida resultados da madrugada. `OperationalWindow` faz parse
  de janelas de horário (pausa monitores em janelas de manutenção).
- Exceções não tratadas → Telegram (via `withExceptions` em `bootstrap/app.php`).
- Health check: `GET /health` (PDO do banco, 200/503) e `/up` (nativo).

---

## 8. Relatórios e PDFs

`RelatorioController` gera PDFs com `barryvdh/laravel-dompdf` (autorização, carteirinha,
ficha médica, financeiro, patrimônio + hub de relatórios). PDFs em lote pesados rodam
assíncronos via job `GerarRelatorioPDF` (tabela `relatorio_gerados`). `EventoController`
gera autorizações de evento.

---

## 9. Convenções

- **pt_BR** em controllers, rotas, models, colunas, textos.
- **Pint:** débito de formatação pré-existente — limpe **só os arquivos que tocar**.
- **Sem soft deletes** — exclusão é definitiva e em cascata, por design.
- **UI:** seguir [`guia-visual-ui.md`](guia-visual-ui.md) (mobile-first, `x-app-layout` +
  `ui-page`, classes `ui-btn-*`, contraste WCAG 2.1 AA).
- **Imports em `routes/*.php`:** o auto-Pint poda imports "não usados"; em arquivos sem
  namespace, adicione o `use` **e** seu uso na mesma edição.
