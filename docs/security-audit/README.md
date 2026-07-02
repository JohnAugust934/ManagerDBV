# Auditoria de Segurança — ManagerDBV

Auditoria focada em **invasão/comprometimento e isolamento multi-tenant**, executada em 4 fases
sobre a branch `security-audit`. Detalhes por fase:

- [Fase 1 — Isolamento de Tenant](fase-1-relatorio.md)
- [Fase 2 — Autenticação e Autorização](fase-2-relatorio.md)
- [Fase 3 — Consentimento LGPD e Geração de PDF](fase-3-relatorio.md)
- [Fase 4 — Superfície de Ataque Geral](fase-4-relatorio.md)
- [**ROADMAP** — pendências de segurança para rodadas futuras](ROADMAP.md)

## Veredito geral

O sistema está **notavelmente bem endurecido**. Não foi encontrado nenhum vazamento cross-tenant
explorável, nem SQL injection, XSS, CSRF ou escalada de privilégio ativa. Os padrões de risco
(`withoutGlobalScopes`, `DB::table`, jobs em fila) aparecem quase sempre com filtro `club_id`
explícito e justificativa. **Um único achado de severidade Alta** (dado de menor em disco público)
merece correção antes do próximo deploy; o restante é endurecimento/defesa-em-profundidade.

## Status das correções

Todas as correções acionáveis foram **implementadas** nesta branch (`security-audit`), exceto a
exposição de fotos, **deferida conscientemente** (justificativa abaixo). Suíte: **533 testes verdes**.

### 🔴 Alta
- [x] **Via física de consentimento agora em disco privado.**
      `ConsentimentoPrivacidadeController::viaFisicaRecebida` grava em `local` (não mais `public`);
      novo endpoint autenticado `privacidade.via-fisica.download` (sob `can:secretaria`,
      route-model binding tenant-scoped + checagem `consentimento↔desbravador`) é a única porta de
      saída. Link adicionado na timeline de `privacidade/index`. *(Fase 3)*

### 🟠 Média
- [x] **Colunas de privilégio fora de `User::$fillable`** (`role`, `is_master`, `is_platform_admin`,
      `club_id`, `extra_permissions`). Escritas confiáveis passaram a usar `forceCreate`/`forceFill`
      (RegisteredUserController, UsuarioController, PlatformController, 3 seeders). Fecha a escalada
      por mass assignment. *(Fase 2)*
- [x] **Teste de regressão do isolamento:** `tests/Feature/MultiTenant/GlobalScopeRegressaoTest.php`
      falha se qualquer um dos 15 models de tenant deixar de registrar o `ClubScope`. *(Fase 1)*
- [~] **Fotos de desbravadores em disco público — DEFERIDO (decisão documentada).** Tornar as fotos
      privadas exige mover os arquivos para fora de `storage/app/public`, o que **cascata no
      subsistema de backup** (o spatie inclui `storage_path('app/public')`; `ClubBackupService`,
      `ClubRestoreService` e `ClubExportService` leem/gravam fotos no disco `public`; `config/backup.php`).
      Esse subsistema é explicitamente marcado como alto risco no CLAUDE.md e já causou falhas em
      produção. Dado (a) o acoplamento, (b) o nome de arquivo ser UUID aleatório não-enumerável, (c) a
      URL só ser emitida a usuários autenticados do mesmo clube e (d) a sensibilidade menor que a via
      física (já corrigida), optou-se por **não** alterar o backup nesta passada. **Recomendação
      futura:** mudança coordenada movendo fotos para disco privado + rota autenticada +
      ajuste de include do backup, em tarefa dedicada com o backup-reviewer. *(Fase 3)*

### 🟡 Baixa
- [x] `throttle:6,1` na rota `POST /forgot-password`. *(Fases 2 e 4)*
- [x] Placeholder genérico no `.env.example` (host real do Supabase removido). *(Fase 4)*
- [x] `gerarAutorizacao` (Eventos) valida que o desbravador está inscrito no evento. *(Fase 1)*
- [x] Usuário não edita mais o próprio `role`/`extra_permissions` (UsuarioController::update). *(Fase 2)*
- [ ] (Futuro) CSP com nonce por requisição, removendo `'unsafe-inline'`/`'unsafe-eval'`. Mantido
      como melhoria futura (exige refatorar inline scripts do Alpine/Vite). *(Fase 4)*

### Observação operacional (backfill)
As correções acima protegem **novos** dados. Arquivos de consentimento já gravados no disco público
antes desta mudança permanecem lá — recomenda-se um passo de migração para movê-los ao disco privado
(fora do escopo de código desta auditoria).

## Pontos fortes confirmados

- **Isolamento multi-tenant** consistente via `ClubScope`/`BelongsToTenant`; jobs em fila e serviços
  de export/import/restore amarram `club_id` explicitamente onde a sessão não existe.
- **Autenticação**: login com rate limiting; registro deriva privilégios do convite (não do input);
  passkeys/WebAuthn e verificação de e-mail com throttle; sem sinks de mass-assignment cru.
- **Integridade LGPD**: revogação registra o usuário autenticado, não apaga histórico, sem edição
  retroativa; ROPA via `LgpdService`; campos sensíveis cifrados em repouso (`rg`, `numero_sus`,
  `cpf`, saúde).
- **Superfície geral**: sem SQLi (queries parametrizadas), `{!! !!}` sempre sobre conteúdo
  seguro/escapado, CSRF coberto, uploads validados (fotos re-encodadas por GD), sem segredos
  commitados, config de produção correta, cabeçalhos de segurança + CSP.

## Restrição observada

Conforme solicitado, este relatório **descreve os vetores** sem fornecer exploits funcionais
prontos para uso.
