# Roadmap de Melhorias

Itens de robustez/qualidade identificados mas **não bloqueadores** — registrados
para tratamento futuro. Convenção: cada item lista origem, risco e a correção
proposta. Priorize por risco operacional.

## Origem: revisão pré-promoção da `multi-tenant` → `main` (2026-06-26)

A promoção foi feita após corrigir os bloqueadores e os achados de severidade
alta (ver commits `ed8d9c3` e `06c8f04`). Os itens abaixo são os 🟢 verdes da
mesma revisão — robustez, sem vazamento ativo nem quebra de deploy.

> **Status (2026-06-29):** todos os itens desta seção foram tratados na branch
> `feature/roadmap-robustez` (1 commit por item, com testes). Marcados com ✅.

### Backup

- ✅ **`club_backup_logs` sem poda.** `backup:prune-manifests`
  (`app/Console/Commands/PruneBackupManifests.php`) poda apenas `backup_logs`.
  A tabela `club_backup_logs` cresce ~N linhas/dia por clube indefinidamente.
  **Correção:** estender `PruneBackupManifests::pruneOldLogs()` para podar também
  `ClubBackupLog` pela mesma janela (`BACKUP_LOG_RETENTION_DAYS`).

- ✅ **Backup de clube deletável pelo platform admin.**
  `BackupController::index()` lista recursivamente o disco `local` e
  `normalizeBackupSelection()` aceita qualquer `.zip`, incluindo
  `backups/clubes/{slug}/*.zip`. O platform admin pode excluir um backup de clube
  pela tela de backups da plataforma, sem aviso.
  **Correção:** restringir `normalizeBackupSelection()` ao prefixo
  `config('backup.backup.name').'/'`, ou filtrar `backups/clubes/` no `index()`.

- ✅ **`ClubBackupController::destroy()` exclui sem registro de auditoria**
  (`Storage::disk($disk)->delete($path)`). Exclusão permanente sem soft delete e
  sem log da exclusão. **Correção:** registrar a exclusão (quem/quando) antes de
  deletar.

- ✅ **`ClubRestoreService` ignora entradas corrompidas do zip em silêncio**
  (`getStream()` falso → `continue`). Restauração incompleta pode passar por
  bem-sucedida. **Correção:** `Log::warning()` ou acumular em `$this->warnings`.

### LGPD

- ✅ **`lgpd:anonimizar-desligados` nunca executa via cron.** O comando usa
  `$this->confirm(...)` sem fallback não-interativo; sob `schedule:run` (sem TTY)
  `confirm()` retorna `false` e nada é anonimizado — a retenção de 5 anos (LGPD
  Art. 16) nunca é aplicada automaticamente.
  **Correção:** adicionar opção `--force` ao comando (`$this->option('force') ||
  $this->confirm(...)`) e usar `lgpd:anonimizar-desligados --force` no schedule
  (`routes/console.php`).

- ✅ **Migration de cifra descriptografa todo o PII no `down()` sem guarda.**
  `2026_06_21_200002_encrypt_sensitive_desbravador_fields.php::down()` decifra
  CPF/RG/SUS/campos médicos para texto puro. Um `migrate:rollback` acidental em
  produção expõe tudo. **Correção:** bloquear no início do `down()`
  (`if (app()->isProduction()) throw ...`) exigindo override explícito + backup.

- ✅ **ROPA (`lgpd_registros`) sem política de retenção.** O
  `DesbravadorObserver` grava nome do responsável/menor nos metadados; a tabela
  cresce sem poda. **Correção:** estender um comando de poda para `lgpd_registros`
  acima de N anos (similar à poda de `backup_logs`).

### Financeiro

- ✅ **Valores monetários transitam por `float`** em somatórios e no insert massivo
  (`CaixaController::index`, `MensalidadeController::index`/`gerarMassivo`),
  apesar do cast `decimal:2`. Sem erro visível na faixa de valores atual, mas
  arquiteturalmente inconsistente. **Correção:** somar no banco
  (`SUM(CASE WHEN tipo='entrada' THEN valor ELSE -valor END)`) ou usar `bcmath`;
  gravar com `number_format(..., 2, '.', '')`.

### Multi-tenant (menores)

- ✅ **`RelatorioController` valida `unidade_id` com `exists` não-escopado.** Não
  vaza dado (o scope da unidade retorna vazio), mas gera relatório silenciosamente
  vazio em vez de erro de validação. **Correção:**
  `Rule::exists('unidades','id')->where('club_id', ClubContext::currentClubId())`.

- ✅ **Comentários obsoletos citam `DesbravadorClubScope`** (que não existe;
  `Desbravador` usa `BelongsToTenant`/`ClubScope`) em `DesbravadorController` e
  `FrequenciaController`. **Correção:** atualizar os comentários.

### Cobertura de testes sugerida

- ✅ Isolamento cross-tenant de `caixa` (clube A não vê lançamentos de B) via HTTP.
- ✅ `caixa_audit_logs` recebe linha após store/update/destroy via fluxo HTTP.
