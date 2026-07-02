# Roadmap de Segurança — ManagerDBV

Itens de segurança **pendentes** após a auditoria de 4 fases (ver [README.md](README.md) e os
relatórios `fase-N-relatorio.md`). O núcleo de invasão/isolamento multi-tenant já foi corrigido e
testado nesta branch; este documento reúne o que ficou para rodadas futuras.

Legenda de prioridade: 🔴 Alta · 🟠 Média · 🟡 Baixa · ⚪ Arquitetural/futuro.

---

## Grupo 1 — Itens abertos da auditoria (conhecidos e documentados)

### 🟠 1.1 — Fotos de menores em disco público → disco privado
- **Onde:** `DesbravadorController` (`processarFoto`, `store`, `update`, `removerFoto`), accessor
  `Desbravador::getFotoUrlAttribute`, views que usam `asset('storage/'.$foto)`
  (`desbravadores/index`, `desbravadores/show`, `classes/show`, `unidades/show`).
- **Por que ficou aberto:** tornar as fotos privadas exige mover os arquivos para fora de
  `storage/app/public`, o que **cascata no subsistema de backup** (o spatie inclui
  `storage_path('app/public')`; `ClubBackupService`, `ClubRestoreService` e `ClubExportService`
  leem/gravam fotos no disco `public`; `config/backup.php`). Esse subsistema é marcado como alto
  risco no CLAUDE.md e já causou falhas em produção.
- **Ação futura (tarefa dedicada, com o backup-reviewer):**
  1. Mover fotos para disco privado (`local`) sob `fotos/`.
  2. Criar rota autenticada de streaming (`desbravadores/{desbravador}/foto`, tenant-scoped por
     route-model binding) e apontar `getFotoUrlAttribute` para ela.
  3. Atualizar as ~5 views para usar `$dbv->foto_url`.
  4. Ajustar `collectUploadFiles`/restore/export e o include do backup para a nova localização.
  5. Backfill dos arquivos existentes (ver 1.2).
- **Mitigadores atuais:** filename UUID aleatório (não-enumerável); URL só emitida a usuários
  autenticados do mesmo clube; sensibilidade menor que a via física (já corrigida).

### 🟠 1.2 — Backfill dos arquivos já gravados no disco público
- **Contexto:** a correção do consentimento (via física → disco privado) protege **dados novos**.
  Arquivos de consentimento (e fotos, se 1.1 for feito) gravados **antes** da mudança continuam no
  disco público.
- **Ação futura:** comando artisan (ex.: `php artisan seguranca:mover-uploads-privados`) que
  varre `via_fisica_caminho` (e fotos) apontando para o disco público, move para o privado e
  atualiza a coluna. Idempotente e com `--dry-run`.

### ⚪ 1.3 — CSP sem `unsafe-inline`/`unsafe-eval` (nonce)
- **Onde:** `app/Http/Middleware/SecurityHeaders.php`.
- **Contexto:** a CSP atual usa `'unsafe-inline'` + `'unsafe-eval'` (exigidos pelo Alpine.js v3),
  o que reduz a mitigação de XSS. O escape automático do Blade segue como defesa primária.
- **Ação futura:** migrar para CSP com **nonce** por requisição, removendo `unsafe-inline`; exige
  refatorar os scripts inline do Alpine/Vite. Baixa urgência.

### ⚪ 1.4 — Camada de Policies (defesa em profundidade)
- **Contexto:** não há Policies; a autorização de posse de registro repousa **inteiramente** no
  global scope `ClubScope` + route-model binding. Já mitigado pelo teste de regressão
  `GlobalScopeRegressaoTest`, mas segue como mecanismo único.
- **Ação futura:** avaliar Policies para operações destrutivas críticas (exclusões em cascata,
  restauração de backup, exportação de dados LGPD), como segunda camada.

---

## Grupo 2 — Dependências vulneráveis (fora do escopo da auditoria de código)

### 🔴 2.1 — Atualizar dependências Composer
- **Situação (data da auditoria):** `composer audit` → **25 advisories / 15 pacotes**
  (3 altos, 16 médios, 4 baixos). `npm audit --omit=dev` → **0**.
- **Altos (relevantes porque o sistema envia e-mails — convites, reset de senha):**
  - **Laravel Framework** — CRLF injection na regra de e-mail padrão.
  - **Symfony Mailer** — CVE-2026-45067: injeção de cabeçalho / comando SMTP via CRLF.
  - **PHPUnit** (php-code-coverage) — desserialização insegura (apenas dev/tooling; risco baixo em
    produção, mas atualizar mesmo assim).
- **Médios (exemplos):** `guzzlehttp/guzzle` (<7.12.1) — domínio de cookie "dot-only" casa todos os
  hosts; downgrade silencioso de proxy HTTPS→cleartext; `guzzlehttp/psr7`.
- **Ação futura:** `composer update` (traz guzzle ≥7.12.1 e patches de framework/mailer) →
  `composer test` (baseline 535 verdes) → revisar changelog de major bumps, se houver. Repetir
  `composer audit` até zerar os altos/médios.
- **Recorrência:** considerar rodar `composer audit`/`npm audit` no CI (falhar em severidade alta).

---

## Grupo 3 — Superfícies não auditadas a fundo (segunda rodada)

As 4 fases focaram em multi-tenant, autenticação/autorização, LGPD/PDF e injeção geral. As áreas
abaixo **não foram revisadas em profundidade** e merecem uma passada dedicada.

### 🟠 3.1 — WebAuthn / Passkeys
- **Onde:** `PasskeyController`, `PasskeyAutenticacaoController`.
- **Revisar:** validação de challenge, verificação de `origin`/`rpId`, proteção contra replay,
  vínculo credencial↔usuário, e o rate limiting da asserção (`throttle:6,1` já existe no login por
  passkey — confirmar cobertura). Vetor de autenticação sem senha, alto valor.

### 🟠 3.2 — Configuração de sessão/cookie e HTTPS em produção
- **Revisar:** `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE`, enforcement de HTTPS
  (`URL::forceScheme('https')` / proxy confiável / `TrustProxies`). O `.env.production.example` já
  fixa `SESSION_ENCRYPT=true`, mas não vi o flag de secure cookie.

### 🟠 3.3 — Vazamento de PII em logs e notificações
- **Onde:** chamadas `Log::error(...)` e `App\Services\TelegramNotifier`.
- **Revisar:** garantir que dados pessoais de menores (nome, CPF, contato, saúde) não sejam
  incluídos em mensagens de log/Telegram. Redigir/mascarar onde necessário.

### 🟡 3.4 — Profundidade da validação do import CSV
- **Onde:** `ImportacaoDesbravadorController`, `ClubImportService`.
- **Revisar:** CSV/formula injection (valores iniciando com `=`,`+`,`-`,`@`), limites de tamanho de
  arquivo/linhas, e validação de tipos por linha além do já existente.

### 🟡 3.5 — Rate limiting em consultas de dados de menores
- **Onde:** endpoints autenticados como `desbravadores.show`, `unidades.show`, relatórios.
- **Revisar:** aplicar `throttle` para limitar scraping por uma conta comprometida (hoje só há
  autenticação, sem limite de taxa nessas leituras).

---

## Ordem sugerida de execução

1. **🔴 2.1** — `composer update` + `composer test` (rápido, alto impacto, resolve os 3 altos).
2. **🟠 3.2 / 3.3** — hardening de config de produção e redação de PII em logs (baixo esforço).
3. **🟠 3.1** — revisão dedicada de WebAuthn/passkeys.
4. **🟠 1.1 + 1.2** — fotos privadas + backfill (tarefa coordenada com o backup-reviewer).
5. **🟡 3.4 / 3.5** — import CSV e rate limiting de leituras.
6. **⚪ 1.3 / 1.4** — CSP com nonce e camada de Policies (melhorias de longo prazo).
