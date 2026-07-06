# Fase 4 — Superfície de Ataque Geral

**Escopo:** SQL injection, XSS (Blade `{!! !!}`/`{{ }}`), CSRF, upload de arquivos, segredos
hardcoded/commitados, rate limiting, e configuração de produção (`APP_DEBUG`, erros genéricos).

**Veredito geral:** superfície geral **sólida**. Sem SQL injection, sem XSS explorável, CSRF
coberto pelo padrão do Laravel, uploads validados (fotos até re-processadas por GD), sem `.env`
real commitado e template de produção correto. Achados são de baixa severidade / endurecimento.

## Achados

| Severidade | Arquivo:Linha | Vulnerabilidade | Cenário de Exploração | Correção Sugerida |
|---|---|---|---|---|
| Baixa | `.env.example:26,29` | **Host de infraestrutura real em arquivo commitado.** `DB_HOST=aws-1-sa-east-1.pooler.supabase.com` e `DB_USERNAME=postgres.your-project-ref` expõem o provedor/região do banco (Supabase, sa-east-1). **Sem senha** (campo vazio). | Não há credencial vazada — mas revela o alvo de infraestrutura (facilita reconhecimento). | Trocar por placeholder genérico (`DB_HOST=127.0.0.1`, `DB_USERNAME=laravel`), como já faz o `.env.production.example`. |
| Baixa | `app/Http/Controllers/Auth/PasswordResetLinkController.php:26` (rota `routes/auth.php:41`) | `POST /forgot-password` **sem `throttle` de rota** (login, passkey e verificação de e-mail têm). | Spam de e-mails de reset / enumeração leve por muitos endereços. O broker limita reenvio por usuário (~60s), mas não há limite por IP. | `Route::post('forgot-password', ...)->middleware('throttle:6,1')`. |
| Baixa (informativo) | `app/Http/Middleware/SecurityHeaders.php:28-37` | **CSP com `'unsafe-inline'` e `'unsafe-eval'`** em `script-src` (exigidos pelo Alpine.js v3). | A CSP não bloqueia scripts inline injetados — ou seja, oferece **pouca mitigação** de XSS. O escape automático do Blade continua sendo a defesa primária (e está íntegro). | Aceitável dado o Alpine. Melhoria futura: migrar para CSP com **nonce** por requisição, removendo `unsafe-inline`. Sem ação imediata. |

## O que foi verificado e está correto (não é achado)

- **SQL injection:** grep por `whereRaw/selectRaw/orderByRaw/DB::raw` com interpolação de variável
  (`$`) = **0 ocorrências**. Todo acesso usa Eloquent ou `DB::table()` com bindings parametrizados
  (`where`/`whereIn` recebem valores, não SQL). `DashboardController` usa `DB::raw` só com strings
  estáticas de agregação.
- **XSS / `{!! !!}`:** todas as ocorrências são seguras — ícones de um array **hardcoded**
  (`flash-messages`), `nl2br(e($comunicado->corpo))` (escapado com `e()` antes), strings de
  tradução (paginação), atributos de componente Breeze (`text-input`), e `{!! $termoHtml !!}`
  cujo conteúdo é renderizado da view `privacidade.termo` onde `$clubeNome` sai **escapado com
  `{{ }}`** (`termo.blade.php:17`). Nome de clube malicioso não injeta script.
- **CSRF:** todas as rotas que alteram estado são `POST/PUT/PATCH/DELETE` (protegidas pelo
  `VerifyCsrfToken` padrão do grupo web); **nenhuma rota GET altera estado** (todos os GETs são
  index/show/create/edit/download/export/print/histórico — apenas leitura).
- **Uploads:** fotos de desbravador **re-processadas por GD** (decodifica/re-encoda — descarta
  payload embutido); consentimento (`mimes:pdf,jpg,jpeg,png`, `max:5120`) e logo do clube
  (`file|image|mimes:jpeg,png,webp|max:2048`, nome `Str::uuid()` + extensão **derivada do
  conteúdo** via `->extension()`). Validação de tipo/tamanho presente. (O disco público do
  consentimento é tratado na **Fase 3**.)
- **Segredos:** `.env` e `.env.*` estão no `.gitignore`; só `.env.example` e
  `.env.production.example` são versionados. Nenhuma chave/token real commitado (todos os
  `*_TOKEN`/`*_PASSWORD`/`APP_KEY` vazios).
- **Config de produção:** `.env.production.example` fixa `APP_ENV=production`, `APP_DEBUG=false`,
  `SESSION_ENCRYPT=true` (com comentários "OBRIGATÓRIO"). Exceções são reportadas ao Telegram
  (`bootstrap/app.php`) sem vazar stack trace ao usuário final.
- **Rate limiting:** `login` (5/email+IP via `LoginRequest`), `passkeys.login`/`verification.*`
  (`throttle:6,1`), `relatorios.custom` (limiter `relatorios` definido em `AppServiceProvider:77`).
- **SecurityHeaders:** `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`,
  `Referrer-Policy`, `Permissions-Policy`, HSTS em produção, CSP com `frame-ancestors 'none'`.

## Conclusão da Fase 4

Nenhuma correção bloqueante. Aplicar os dois ajustes de baixo esforço (throttle no
`forgot-password` e placeholder genérico no `.env.example`) e, como melhoria futura, considerar CSP
com nonce.
