# Plano de Escalabilidade, Segurança e Conformidade LGPD
> ManagerDBV — Branch `multi-tenant`  
> Data de criação: 2026-06-20  
> Status: **Planejado** — implementar em partes, na ordem definida abaixo

---

## Visão Geral

Este plano consolida todas as melhorias identificadas na auditoria de concorrência/multi-tenant,
organizadas em fases executáveis. A ordem de prioridade segue o critério:

1. **Estabilidade imediata** — o que pode derrubar o sistema hoje
2. **Segurança e conformidade LGPD** — obrigação legal
3. **Performance e escalabilidade** — suportar crescimento de tenants
4. **Resiliência operacional** — o sistema se recuperar sozinho

Cada parte pode ser entregue em um PR isolado. Não há dependência entre partes diferentes,
a menos que indicado explicitamente.

---

## Parte 1 — Correções Críticas de Código (Risco de Crash)

> **Esforço estimado:** 2–4 horas  
> **Impacto:** Elimina risco de OOM e timeout sob carga real

### 1.1 Substituir `Model::all()` por agregações SQL no RelatorioController

**Problema:** `Caixa::all()` e `Patrimonio::all()` carregam TODOS os registros em memória PHP
para calcular totais. Um clube com 2 anos de operação pode ter 20.000+ registros. Em multi-tenant,
múltiplos clubes fazendo isso simultaneamente causa OOM.

**Arquivo:** `app/Http/Controllers/RelatorioController.php`

**Correções necessárias:**

```php
// LINHA 60-77 — Antes:
$movimentacoes = Caixa::all();
$totalEntradas = $movimentacoes->where('tipo', 'entrada')->sum('valor');
$totalSaidas   = $movimentacoes->where('tipo', 'saida')->sum('valor');

// Depois (uma query de agregação, sem carregar registros):
$totais = Caixa::selectRaw("
    SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as total_entradas,
    SUM(CASE WHEN tipo = 'saida'   THEN valor ELSE 0 END) as total_saidas
")->first();
$totalEntradas = $totais->total_entradas ?? 0;
$totalSaidas   = $totais->total_saidas   ?? 0;

// LINHA 78 — Antes:
$patrimonioTotal = Patrimonio::all()->sum(fn ($p) => $p->valor_estimado);

// Depois:
$patrimonioTotal = Patrimonio::sum('valor_estimado');
```

**Revisar também** todas as ocorrências de `->all()` no controller (linhas 148, 176, 234, 265,
282, 311, 357, 398, 434, 466, 507, 548, 594, 650, 689, 743, 898, 908, 921, 935) e garantir que
cada uma ou usa `->get()` já com filtros aplicados, ou é substituída por agregação quando só
precisa de totais.

### 1.2 Tornar envio de e-mail assíncrono

**Problema:** `Mail::to()->send()` é síncrono. Se o SMTP demorar, trava o request e segura
conexão com o banco.

**Arquivo:** `app/Http/Controllers/InvitationController.php` (linhas 141, 201, 229)

```php
// Antes:
Mail::to($request->email)->send(new ClubInvitation($invitation));

// Depois:
Mail::to($request->email)->queue(new ClubInvitation($invitation));
```

A Mailable `ClubInvitation` não precisa de nenhuma alteração — `queue()` é um método nativo
do Laravel. Garantir que `QUEUE_CONNECTION=database` esteja configurado (já está no template).

### 1.3 Adicionar índice em `frequencia_column_values`

**Problema:** A tabela pivot do sistema de colunas de chamada não tem índice em `frequencia_id`.
O Ranking percorre essa tabela para cada frequência de cada desbravador (potencialmente 500+
full scans por carregamento de página).

**Criar nova migration:**

```php
// database/migrations/YYYY_MM_DD_add_index_frequencia_column_values.php
Schema::table('frequencia_column_values', function (Blueprint $table) {
    $table->index(['frequencia_id'], 'fcv_frequencia_id_idx');
    $table->index(['frequencia_id', 'attendance_column_id'], 'fcv_freq_col_idx');
});
```

### 1.4 Adicionar índice composto em `desbravadores(club_id, unidade_id)`

**Problema:** Queries de chamada/frequência filtram `WHERE club_id = ? AND unidade_id = ?`
mas não há índice composto para isso — o MySQL faz full scan no `club_id` primeiro.

```php
Schema::table('desbravadores', function (Blueprint $table) {
    $table->index(['club_id', 'unidade_id'], 'dbv_club_unidade_idx');
});
```

---

## Parte 2 — LGPD — Conformidade Legal

> **Esforço estimado:** 1–2 dias  
> **Impacto:** Obrigação legal. Clubes são controladores de dados de menores de idade — risco jurídico sem isso

### Contexto legal

O ManagerDBV armazena dados pessoais e dados pessoais **sensíveis** de menores de idade
(membros Desbravadores): nome, data de nascimento, filiação, dados médicos (ficha médica),
frequência, endereço e foto. Isso ativa obrigações específicas da LGPD (Lei 13.709/2018):

- **Art. 14** — Tratamento de dados de crianças/adolescentes exige consentimento dos pais/responsáveis
- **Art. 18** — Titulares têm direito de acesso, retificação, exclusão, portabilidade
- **Art. 37** — Registro das operações de tratamento (ROPA)
- **Art. 46** — Medidas técnicas e administrativas de segurança

### 2.1 Política de Privacidade e Termos de Uso

**Criar:** `resources/views/legal/politica-privacidade.blade.php`  
**Criar:** `resources/views/legal/termos-de-uso.blade.php`  
**Criar:** `app/Http/Controllers/LegalController.php`  
**Criar rotas:** `GET /privacidade` e `GET /termos` (públicas, fora do middleware auth)

Conteúdo mínimo da Política de Privacidade:
- Quem é o controlador de dados (clube + responsável pelo sistema)
- Quais dados são coletados e por qual finalidade
- Base legal para cada categoria (consentimento, legítimo interesse, obrigação legal)
- Dados de menores: consentimento dos responsáveis (Art. 14)
- Como o titular pode exercer seus direitos
- Prazo de retenção dos dados
- Contato do encarregado (DPO) ou responsável pelo clube

### 2.2 Consentimento no cadastro de Desbravadores

**Problema:** O cadastro de desbravadores (menores) não registra o consentimento dos pais/responsáveis.

**Alterações necessárias:**

- Adicionar campos na migration de `desbravadores`:
  ```php
  $table->boolean('consentimento_lgpd')->default(false);
  $table->timestamp('consentimento_lgpd_em')->nullable();
  $table->string('consentimento_lgpd_responsavel')->nullable(); // nome do responsável que assinou
  ```
- Adicionar checkbox obrigatório no formulário de criação/edição do desbravador
- Exibir link para a Política de Privacidade no formulário
- Registrar data/hora e IP do consentimento

### 2.3 Portal do Titular (Direitos do Art. 18)

**Criar** uma seção acessível pelo responsável/secretaria para:

- **Acesso:** Exportar todos os dados de um desbravador em JSON ou PDF
- **Retificação:** Já existe via formulário de edição — documentar explicitamente
- **Exclusão:** A exclusão já é definitiva (sem soft delete) — adicionar confirmação com aviso LGPD
- **Portabilidade:** Exportação CSV/JSON dos dados do desbravador
- **Oposição:** Campo para registrar oposição ao uso de foto/imagem em materiais do clube

**Sugestão de implementação:** Nova aba na tela `desbravadores.show` chamada "Privacidade" com
estas ações, acessível apenas com gate `secretaria`.

### 2.4 Registro de Operações de Tratamento (ROPA — Art. 37)

Criar uma tabela `lgpd_registros` (interna, não exibida no produto):

```php
Schema::create('lgpd_registros', function (Blueprint $table) {
    $table->id();
    $table->foreignId('club_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('acao'); // 'acesso', 'exportacao', 'exclusao', 'consentimento', 'retificacao'
    $table->string('entidade'); // 'desbravador', 'frequencia', 'ficha_medica'
    $table->unsignedBigInteger('entidade_id')->nullable();
    $table->string('ip_origem')->nullable();
    $table->json('metadados')->nullable();
    $table->timestamps();
});
```

Registrar automaticamente via Observer ou evento quando:
- Dados pessoais são exportados (PDF de ficha, carteirinha)
- Desbravador é excluído
- Consentimento é registrado ou revogado
- Acesso à ficha médica

### 2.5 Segurança de Dados Pessoais (Art. 46)

**2.5.1 Criptografia de campos sensíveis**

Avaliar uso do pacote `spatie/laravel-ciphersweet` para criptografar em repouso:
- `desbravadores.cpf`
- `desbravadores.rg`
- Campos de ficha médica (alergias, condições, medicamentos)

Alternativa mais simples: `$casts` com `encrypted` (nativo Laravel 9+):
```php
protected $casts = [
    'cpf' => 'encrypted',
    'alergias' => 'encrypted',
];
```
**Atenção:** campos criptografados não podem ser usados em `WHERE` — verificar impacto nas queries antes de implementar.

**2.5.2 Logs de auditoria para dados sensíveis**

Estender o trait `RegistraAutoria` para registrar no `lgpd_registros` toda vez que campos
sensíveis (`cpf`, `rg`, campos médicos) forem acessados via export ou visualizados na ficha.

**2.5.3 Mascaramento em telas de listagem**

CPF e dados médicos não devem aparecer completos em listagens. Implementar helpers:
```php
// Helpers em User ou helper global:
function mascaraCpf(string $cpf): string {
    return substr($cpf, 0, 3) . '.***.***-' . substr($cpf, -2);
}
```

**2.5.4 Expiração de convites**

Os `invitations` já têm `expires_at` — verificar se é validado na entrada de registro
e se convites expirados são limpos periodicamente (adicionar ao agendamento se necessário).

### 2.6 Retenção e Exclusão de Dados

**Política de retenção mínima a implementar:**

| Dado | Retenção sugerida | Ação |
|---|---|---|
| Desbravadores ativos | Enquanto ativo no clube | Manter |
| Desbravadores desligados | 5 anos (obrigação documental) | Anonimizar após prazo |
| Logs de auditoria (`caixa_audit_logs`) | 5 anos (fiscal) | Rotação automática |
| Backup logs (`backup_logs`) | `BACKUP_LOG_RETENTION_DAYS` já configurado | OK |
| Sessões expiradas | Limpeza automática Laravel | Verificar `php artisan session:gc` agendado |
| Frequências antigas | Decisão do clube | Documentar |

**Implementar:** Comando `artisan lgpd:anonimizar-desligados` que, para desbravadores
marcados como inativos há mais de N anos, substitui dados pessoais por hash anônimo,
mantendo registros estatísticos (frequência, pontuação) sem vínculo identificável.

### 2.7 Aviso no Login e Aceite nos Convites

- Adicionar no formulário de registro via convite (`register-invite`) um checkbox obrigatório
  de aceite dos Termos de Uso e Política de Privacidade com timestamp de aceite registrado no usuário.
- Adicionar coluna `termos_aceitos_em` na tabela `users`.
- Exibir banner não-intrusivo no dashboard informando a política de privacidade (dismissível,
  registrado em preferências do usuário).

---

## Parte 3 — Performance: Cache e Queries Otimizadas

> **Esforço estimado:** 4–8 horas  
> **Impacto:** Redução de 40–70% de queries no fluxo normal

### 3.1 Cache do Dashboard por Clube

O dashboard carrega totais de `Caixa`, `Patrimonio`, `Evento`, `Desbravador` a cada request.
Com 20 tenants acessando o dashboard ao mesmo tempo, são 80+ queries a cada ciclo.

```php
// Em DashboardController (ou onde esses totais são calculados):
$clubId = auth()->user()->club_id;

$resumo = Cache::remember("dashboard_resumo_{$clubId}", 300, function () {
    return [
        'total_membros'    => Desbravador::count(),
        'total_entradas'   => Caixa::where('tipo', 'entrada')->sum('valor'),
        'total_patrimonio' => Patrimonio::sum('valor_estimado'),
        'proximos_eventos' => Evento::where('data_inicio', '>=', now())
                                    ->orderBy('data_inicio')
                                    ->limit(3)
                                    ->get(['id', 'nome', 'data_inicio']),
    ];
});
```

O driver `database` já funciona — sem Redis, já há ganho eliminando N queries por request.

### 3.2 Adicionar `SELECT` explícito nos eager loads pesados

Nos relatórios e ranking, os `->with()` carregam todas as colunas das tabelas relacionadas.

```php
// Antes (carrega tudo):
->with(['desbravadores.frequencias'])

// Depois (carrega só o necessário):
->with([
    'desbravadores:id,nome,unidade_id,ativo',
    'desbravadores.frequencias:id,desbravador_id,data',
    'desbravadores.frequencias.columnValues:id,frequencia_id,attendance_column_id,checked',
])
```

Aplicar em:
- `RelatorioController` — eager loads das linhas 449, 481 e similares
- `RankingController` — eager load linha 29 e 105
- `AppServiceProvider::snapshotRankingYear()` — linha ~100

### 3.3 Timeout de Query no MySQL

Adicionar ao `config/database.php` na conexão `mysql`:

```php
'options' => extension_loaded('pdo_mysql') ? array_filter([
    (PHP_VERSION_ID >= 80500
        ? \Pdo\Mysql::ATTR_SSL_CA
        : \PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
    PDO::ATTR_TIMEOUT => env('DB_QUERY_TIMEOUT', 30),
]) : [],
```

Adicionar `DB_QUERY_TIMEOUT=30` ao `.env.production.example`. Isso garante que uma query
de relatório pesado não segura conexão por mais de 30 segundos.

### 3.4 Paginação em listagens sem limite atual

Verificar controllers com `->get()` em listagens que não têm `->paginate()`. Os principais
suspeitos são:
- `CaixaController::index()` — listagem de movimentações sem LIMIT
- `PatrimonioController::index()` — listagem de itens sem LIMIT
- `DesbravadorController::index()` — listagem de membros (verificar se já tem paginação)

Qualquer listagem que pode crescer indefinidamente deve usar `->paginate(25)` com links
de paginação na view.

### 3.5 Detectar N+1 em desenvolvimento

Adicionar ao `AppServiceProvider::boot()` em ambiente local:

```php
if (app()->isLocal()) {
    DB::listen(function ($query) {
        if ($query->time > 500) {
            logger()->warning('Slow query: ' . $query->sql, [
                'bindings' => $query->bindings,
                'time_ms'  => $query->time,
            ]);
        }
    });
}
```

Considerar o pacote `barryvdh/laravel-debugbar` em dev para visualização de queries N+1.

---

## Parte 4 — PDF em Batch: Operações Assíncronas

> **Esforço estimado:** 1 dia  
> **Impacto:** Elimina timeouts em relatórios grandes; libera workers PHP para outros tenants

### 4.1 Criar Jobs para PDFs em lote

Mover geração de PDFs em lote para Jobs na fila:

**Criar:** `app/Jobs/GerarFichasCompletasPDF.php`  
**Criar:** `app/Jobs/GerarFichasMedicasPDF.php`  
**Criar:** `app/Jobs/GerarRelatorioFinanceiroPDF.php`

Cada Job:
1. Recebe o `club_id` e filtros (unidade, período)
2. Gera o PDF e salva em `storage/app/private/relatorios/{club_id}/`
3. Notifica via Telegram (já existe `TelegramNotifier`) ou por e-mail quando pronto
4. O arquivo fica disponível para download por 24h (limpeza agendada)

**Alterar** os endpoints do `RelatorioController` que geram PDFs em lote:
```php
// Antes (síncrono, trava por 30-60s):
$pdf = Pdf::loadView(...)->output();
return response($pdf)->header('Content-Type', 'application/pdf');

// Depois (assíncrono):
GerarFichasCompletasPDF::dispatch(auth()->user()->club_id, $filtros);
return back()->with('info', 'Seu relatório está sendo gerado. Você receberá uma notificação quando estiver pronto.');
```

### 4.2 Tela de relatórios gerados

Criar uma seção `/relatorios/downloads` que lista os PDFs gerados pendentes/prontos do clube,
com link para download e expiração visível.

### 4.3 PDFs individuais (carteirinha, autorização)

PDFs de membro único (linhas 190-209 do RelatorioController) são rápidos o suficiente para
continuar síncronos. Não precisam ser movidos para fila.

---

## Parte 5 — Redis: Cache e Fila em Memória

> **Esforço estimado:** 2–4 horas de configuração  
> **Pré-requisito:** Instalar Redis no VPS (`apt install redis-server`)  
> **Custo:** Zero (Redis é open source, roda no mesmo VPS)  
> **Impacto:** Cache e fila saem do MySQL; reduz 60-80% de queries no banco em carga normal

### 5.1 Instalar e configurar Redis

No VPS da Hostinger (SSH):
```bash
sudo apt update && sudo apt install redis-server
sudo systemctl enable redis-server && sudo systemctl start redis-server
redis-cli ping  # deve retornar PONG
```

### 5.2 Configurar o Laravel para usar Redis

No `.env` de produção:
```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis  # ou predis (instalar via composer)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null  # configurar senha se VPS compartilhado
```

Adicionar ao `.env.production.example` como padrão recomendado para Fase 2.

### 5.3 Separar prefixos de cache por tenant

Verificar que o cache usa `club_id` nas chaves (já garantido pelo padrão `dashboard_resumo_{$clubId}`
sugerido na Parte 3.1). Nunca cachear dados sem o `club_id` na chave.

### 5.4 Sessões em Redis (opcional, Fase 2)

```env
SESSION_DRIVER=redis
```

Mover sessões do banco para Redis elimina ~2 queries por request (leitura + escrita de sessão).
Com 100 usuários ativos, são 200 queries/s que somem do MySQL.

---

## Parte 6 — Segurança: Hardening do Sistema

> **Esforço estimado:** 4–6 horas  
> **Impacto:** Reduz superfície de ataque; protege dados dos tenants

### 6.1 Headers de segurança HTTP

Adicionar middleware de security headers:

**Criar:** `app/Http/Middleware/SecurityHeaders.php`

```php
public function handle(Request $request, Closure $next): Response
{
    $response = $next($request);
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    if (app()->isProduction()) {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
    return $response;
}
```

Registrar em `bootstrap/app.php` como middleware global.

### 6.2 Content Security Policy (CSP)

Adicionar CSP ao middleware de security headers para prevenir XSS:

```php
$response->headers->set(
    'Content-Security-Policy',
    "default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';"
);
```

Ajustar o nonce nos templates Blade que usam `<script>` inline (Alpine.js).

### 6.3 Forçar HTTPS e verificar cookies seguros

Confirmar que o `AppServiceProvider` força HTTPS em produção (já existe em `bootstrap/app.php`).
Garantir no `.env.production.example`:
```env
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
SESSION_SAME_SITE=lax
```

### 6.4 Proteção de arquivos de backup

Os backups em `storage/app/` contêm dumps completos do banco. Verificar:
- `storage/app/private/` não está acessível via URL pública
- `APP_URL` não serve arquivos de `storage` que não passaram por `storage:link`
- Adicionar ao `.htaccess` ou config do Nginx: negar acesso direto a `*.zip` e `*.sql`

### 6.5 Validação e sanitização de uploads

Verificar todos os `$request->file()` no código (logos de clube, fotos de desbravadores):
- Validar tipo MIME do lado do servidor (não apenas extensão)
- Limitar tamanho máximo
- Renomear arquivo para UUID antes de salvar (evitar path traversal)
- Nunca servir uploads como executáveis

```php
// Validação segura de upload:
$request->validate([
    'foto' => ['nullable', 'file', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
]);
$path = $request->file('foto')->storeAs(
    "desbravadores/{$clube->id}",
    Str::uuid() . '.' . $request->file('foto')->extension(),
    'public'
);
```

### 6.6 Rate limiting por tenant em endpoints pesados

Adicionar rate limiting nos endpoints de geração de relatórios/PDFs para evitar que um
único clube sobrecarregue o sistema:

```php
// routes/web.php:
Route::middleware(['auth', 'throttle:relatorios'])->group(function () {
    Route::get('/relatorios/...', [RelatorioController::class, '...']);
});

// Em AppServiceProvider::boot():
RateLimiter::for('relatorios', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->club_id);
});
```

### 6.7 Proteção contra enumeração de IDs

Verificar que rotas de recursos (desbravadores, caixas, etc.) não permitem acessar registros
de outros clubes passando IDs diferentes. O global scope deve proteger isso, mas adicionar
testes explícitos de isolamento (ver Parte 7).

---

## Parte 7 — Testes de Isolamento Multi-Tenant

> **Esforço estimado:** 4–8 horas  
> **Impacto:** Garantia automatizada de que dados não vazam entre tenants

### 7.1 Testes de isolamento por recurso

Para cada recurso principal, criar um teste que:
1. Cria dois clubes (A e B) com dados próprios
2. Autentica como usuário do clube A
3. Tenta acessar/modificar dados do clube B diretamente por ID
4. Verifica que recebe 404 ou 403 (nunca 200 com dados do clube B)

```php
// tests/Feature/Tenant/IsolamentoDesbravadorTest.php
it('não permite ver desbravador de outro clube', function () {
    $clubeA = Club::factory()->create();
    $clubeB = Club::factory()->create();
    $userA  = User::factory()->for($clubeA)->create(['role' => 'secretario']);
    $dbvB   = Desbravador::factory()->for($clubeA /* unidade do clube B */)->create();

    $this->actingAs($userA)
         ->get(route('desbravadores.show', $dbvB))
         ->assertNotFound();
});
```

Recursos prioritários para cobrir: `Desbravador`, `Caixa`, `Evento`, `Patrimonio`, `Frequencia`.

### 7.2 Teste de vazamento via `withoutGlobalScopes`

Para cada ocorrência de `withoutGlobalScopes()` no código, adicionar um teste verificando
que a query subsequente tem filtro de `club_id` explícito:

```php
it('ClubExportService não vaza dados de outros clubes', function () {
    $clubeA = Club::factory()->hasDesbravadores(3)->create();
    $clubeB = Club::factory()->hasDesbravadores(5)->create();

    $export = app(ClubExportService::class)->exportar($clubeA->id);

    expect($export['desbravadores'])->toHaveCount(3);
});
```

### 7.3 CI — rodar testes de isolamento em todo PR

Garantir que o workflow do GitHub Actions (ou equivalente) execute os testes de isolamento.
Se não houver CI configurado, adicionar um `composer test` como passo manual obrigatório
antes de qualquer merge na branch `multi-tenant`.

---

## Parte 8 — Resiliência Operacional

> **Esforço estimado:** 4 horas  
> **Impacto:** Sistema se recupera de falhas sem intervenção manual

### 8.1 Retry automático para deadlocks MySQL

Adicionar ao `AppServiceProvider::boot()`:

```php
DB::connection()->setQueryGrammar(DB::connection()->getQueryGrammar());

// Via macro para retries automáticos:
DB::macro('retryOnDeadlock', function (callable $callback, int $maxAttempts = 3) {
    $attempt = 0;
    while (true) {
        try {
            return DB::transaction($callback);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($attempt++ >= $maxAttempts || $e->getCode() !== '40001') {
                throw $e;
            }
            usleep(100_000 * $attempt); // backoff exponencial: 100ms, 200ms, 300ms
        }
    }
});
```

Usar `DB::retryOnDeadlock(fn() => ...)` nas escritas críticas de `Caixa` e `Frequencia`.

### 8.2 Health check com mais informações

O endpoint `/health` atual verifica apenas o PDO. Expandir para:

```php
// GET /health retorna 200 com JSON:
{
    "status": "ok",
    "database": "ok",
    "queue_size": 12,        // jobs pendentes na fila
    "cache": "ok",
    "timestamp": "2026-06-20T03:00:00Z"
}
```

Se `queue_size > 100`, retornar status `degraded` (mas ainda 200 para não derrubar monitor).

### 8.3 Monitorar jobs falhos

O `QueueMonitor` já existe, mas verificar:
- Jobs falhos são notificados no Telegram? (já configurado via `withExceptions`)
- A tabela `failed_jobs` é limpa periodicamente? Adicionar `php artisan queue:prune-failed --hours=168` ao agendamento.

### 8.4 Limpeza agendada de dados temporários

Adicionar ao `routes/console.php`:

```php
// Limpar PDFs gerados há mais de 24h (Parte 4):
Schedule::command('relatorios:limpar-temporarios')->daily();

// Limpar jobs falhos com mais de 7 dias:
Schedule::command('queue:prune-failed', ['--hours' => 168])->weekly();

// Limpar sessões expiradas:
Schedule::command('session:gc')->daily();

// LGPD: anonimizar desbravadores desligados há mais de 5 anos:
Schedule::command('lgpd:anonimizar-desligados')->monthly();
```

### 8.5 Documentar procedimento de recuperação de jobs travados

Adicionar seção ao `docs/RESTORE.md`:

```markdown
## Jobs travados após reinício

Se o servidor reiniciar com jobs em estado `reserved`:
1. `php artisan queue:restart` — sinaliza workers para parar graciosamente
2. Após ~90s (retry_after), jobs sem ACK são recolocados na fila automaticamente
3. Verificar tabela `failed_jobs` para jobs que excederam tentativas
4. `php artisan queue:retry all` se necessário
```

---

## Parte 9 — Monitoramento e Observabilidade

> **Esforço estimado:** 2–4 horas  
> **Impacto:** Detectar problemas antes de virarem incidentes

### 9.1 Log de queries lentas em desenvolvimento

Já descrito na Parte 3.5. Garantir que está ativo em `local` e desligado em `production`.

### 9.2 Log de queries lentas em produção via MySQL

No MySQL da Hostinger, habilitar slow query log (se o plano permitir acesso ao `my.cnf`):
```sql
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;  -- queries acima de 2s
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow.log';
```

Alternativa sem acesso ao MySQL config: adicionar ao `AppServiceProvider`:
```php
if (app()->isProduction() && env('LOG_SLOW_QUERIES', false)) {
    DB::listen(function ($query) {
        if ($query->time > 2000) {
            Log::channel('single')->warning('Slow query', [
                'sql'  => $query->sql,
                'time' => $query->time,
            ]);
        }
    });
}
```

### 9.3 Dashboard operacional para platform admin

Criar uma tela em `/plataforma` (gate `platform-admin`) com:
- Total de clubes ativos/inativos
- Jobs pendentes na fila
- Último backup de cada clube (já existe na BackupController)
- Top 5 queries lentas do dia (se log habilitado)
- Versão da aplicação (`git rev-parse --short HEAD`)

---

## Ordem de Execução Recomendada

| Parte | Título | Prioridade | Esforço |
|---|---|---|---|
| **1** | Correções críticas de código | 🔴 Fazer antes de ir a produção | 2–4h |
| **2** | LGPD — Conformidade legal | 🔴 Obrigação legal | 1–2 dias |
| **3** | Cache e queries otimizadas | 🟡 Importante | 4–8h |
| **4** | PDF em batch assíncrono | 🟡 Importante | 1 dia |
| **6** | Segurança: hardening | 🟡 Importante | 4–6h |
| **7** | Testes de isolamento | 🟡 Regressão preventiva | 4–8h |
| **5** | Redis | 🟢 Quando escalar | 2–4h |
| **8** | Resiliência operacional | 🟢 Melhoria contínua | 4h |
| **9** | Monitoramento | 🟢 Melhoria contínua | 2–4h |

---

## Checklist de Pré-Produção

Antes de colocar o sistema em produção com tenants reais:

- [ ] Parte 1.1 — `Caixa::all()` e `Patrimonio::all()` substituídos por agregações
- [ ] Parte 1.2 — E-mails usando `Mail::queue()`
- [ ] Parte 1.3 — Índice em `frequencia_column_values`
- [ ] Parte 1.4 — Índice em `desbravadores(club_id, unidade_id)`
- [ ] Parte 2.1 — Política de Privacidade e Termos de Uso publicados
- [ ] Parte 2.2 — Campo de consentimento LGPD no cadastro de desbravadores
- [ ] Parte 2.7 — Aceite de termos no registro via convite
- [ ] Parte 3.3 — Timeout de query configurado (30s)
- [ ] Parte 6.1 — Security headers ativos
- [ ] Parte 6.3 — HTTPS, cookies seguros e criptografia de sessão confirmados
- [ ] `SESSION_ENCRYPT=true` no `.env` de produção
- [ ] `SESSION_SECURE_COOKIE=true` no `.env` de produção
- [ ] `APP_DEBUG=false` no `.env` de produção
- [ ] Backup testado e restauração validada (ver `docs/RESTORE.md`)
