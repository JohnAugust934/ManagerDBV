---
name: restore
description: Guia a restauração de um backup do ManagerDBV seguindo o runbook docs/RESTORE.md — escolher o backup, validar manifesto/integridade, restaurar banco + uploads com segurança. Use numa recuperação de desastre ou ao testar restauração.
disable-model-invocation: true
---

# restore

Conduz uma restauração de backup com segurança. Procedimento **raro e crítico** — siga o runbook,
não improvise. Sempre confirme cada passo destrutivo com o usuário antes de executar.

## Antes de tudo

1. **Leia `docs/RESTORE.md`** (runbook canônico) e siga-o. Esta skill é um guia de execução, o
   runbook é a fonte da verdade.
2. Identifique o ambiente (dev/produção). Em produção, **tire um snapshot de emergência antes** —
   `BackupController` já tem `restoreDatabaseFromEmergencySnapshot()` como rede de segurança.

## Passos

1. **Listar backups disponíveis**: a pasta de destino = `APP_NAME`; `BackupController::index` lista
   **recursivamente** (backups antigos podem estar órfãos sob outro `APP_NAME`). Escolha o zip alvo.
2. **Validar integridade ANTES de restaurar**:
   - Conferir o `<zip>.manifest.json` ao lado do zip (checksums SHA-256 por arquivo, gravado pelo
     `BackupIntegrityVerifier`) e a linha em `backup_logs`.
   - Rodar `php artisan backup:deep-verify` (abre e lê o dump dentro do zip) se houver dúvida.
3. **Restaurar** via `BackupController::restore` (rota `master`): restaura o **dump do banco** +
   `storage/app/public/` (uploads). O código da app vem do Git, não do backup.
4. **Pós-restauração**: `php artisan migrate --force` (se o backup for de schema anterior),
   `php artisan config:cache`/`route:cache`/`view:cache`, `storage:link`, e validar logins/health
   (`GET /health`).

## Regras

- Nunca restaure por cima de produção sem snapshot de emergência confirmado.
- Se o manifesto/checksum não bater, **pare** e alerte — não restaure backup corrompido.
- Confirme com o usuário antes de qualquer passo que sobrescreve banco ou arquivos.
