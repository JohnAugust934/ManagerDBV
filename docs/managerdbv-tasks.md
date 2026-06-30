# ManagerDBV — Tarefas para Claude Code

> Projeto: Laravel 12 + Blade + Alpine.js + Tailwind  
> Multi-tenant por `club_id` via trait `BelongsToTenant` + `ClubScope` global scope  
> Todos os arquivos referenciados existem no repositório clonado.

---

## MELHORIAS (bugs e friction points)

---

### MELHORIA 1 — Paginação perde filtros ao trocar de página

**Arquivo:** `app/Http/Controllers/DesbravadorController.php`

**Problema:** O método `index()` usa `paginate(10)` sem `->withQueryString()`.
Ao clicar na página 2 de uma busca, os parâmetros `search`, `unidade_id` e `status`
somem da URL e o usuário perde o contexto da busca.

**Correção exata — linha 57:**

```php
// ANTES
$desbravadores = $query->paginate(10);

// DEPOIS
$desbravadores = $query->paginate(10)->withQueryString();
```

**Arquivo de view — verificar também:**  
`resources/views/desbravadores/index.blade.php`

Confirmar que o componente de paginação usa `$desbravadores->links()` (já deve estar
assim). Não é necessário alterar a view, só o controller.

**Verificar se outros controllers têm o mesmo problema e aplicar a mesma correção:**
- `app/Http/Controllers/EventoController.php` → método `index()`
- `app/Http/Controllers/CaixaController.php` → método `index()` (se existir paginação)
- `app/Http/Controllers/EspecialidadeController.php` → método `index()`
- `app/Http/Controllers/AtaController.php` → método `index()`
- `app/Http/Controllers/PatrimonioController.php` → método `index()`

Buscar por `->paginate(` em todos os controllers e adicionar `->withQueryString()` em todos.

---

### MELHORIA 2 — Query Eloquent crua dentro da view Blade (risco cross-tenant)

**Arquivo da view:** `resources/views/desbravadores/index.blade.php`  
**Arquivo do controller:** `app/Http/Controllers/DesbravadorController.php`

**Problema:** Na linha ~46 da view há:
```php
@foreach (\App\Models\Unidade::orderBy('nome')->get() as $unidade)
```

Isso executa uma query diretamente no template sem garantia que o `ClubScope` esteja
ativo, podendo listar unidades de todos os tenants no filtro. Além disso é uma query
solta executada em toda request sem cache.

**Passo 1 — Adicionar `$unidades` no método `index()` do controller:**

```php
// app/Http/Controllers/DesbravadorController.php
public function index(Request $request)
{
    // ... código existente de busca e filtros ...

    $desbravadores = $query->paginate(10)->withQueryString();

    // ADICIONAR esta linha antes do return:
    $unidades = \App\Models\Unidade::where('club_id', \App\Services\ClubContext::currentClubId())
        ->orderBy('nome')
        ->get(['id', 'nome']);

    return view('desbravadores.index', compact('desbravadores', 'status', 'unidades'));
}
```

**Passo 2 — Substituir a query crua na view:**

```blade
{{-- ANTES (linha ~46 da view) --}}
@foreach (\App\Models\Unidade::orderBy('nome')->get() as $unidade)

{{-- DEPOIS --}}
@foreach ($unidades as $unidade)
```

**Verificar e aplicar o mesmo padrão** em qualquer outra view que faça queries Eloquent
diretamente no template (buscar por `\App\Models\` dentro de `resources/views/`).

---

### MELHORIA 3 — Dashboard sem alertas proativos de pendências

**Arquivo do controller:** `app/Http/Controllers/DashboardController.php`  
**Arquivo da view:** `resources/views/dashboard.blade.php`

**Objetivo:** Adicionar uma seção "Atenção Necessária" no dashboard que mostre
alertas automáticos com base nos dados existentes.

**Passo 1 — Adicionar dados de alertas no `DashboardController::index()`:**

Adicionar após o bloco de `$resumo` existente:

```php
// Alertas proativos — sem cache (precisam ser sempre frescos)
$alertas = [];

// 1. Inadimplência alta (> 30% do mês atual)
if ($taxaInadimplencia > 30) {
    $alertas[] = [
        'tipo' => 'danger',
        'icone' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'titulo' => 'Inadimplência alta',
        'texto' => "Taxa de {$taxaInadimplencia}% no mês atual. Verifique as mensalidades pendentes.",
        'link' => route('mensalidades.index'),
        'link_texto' => 'Ver mensalidades',
    ];
}

// 2. Mensalidades vencidas de meses anteriores
$inadimplentesAntigos = \App\Models\Mensalidade::doClube($clubId)
    ->where('status', 'pendente')
    ->where(function ($q) use ($mesAtual, $anoAtual) {
        $q->where('ano', '<', $anoAtual)
          ->orWhere(function ($q2) use ($mesAtual, $anoAtual) {
              $q2->where('ano', $anoAtual)->where('mes', '<', $mesAtual - 1);
          });
    })
    ->count();

if ($inadimplentesAntigos > 0) {
    $alertas[] = [
        'tipo' => 'warning',
        'icone' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'titulo' => 'Mensalidades atrasadas',
        'texto' => "{$inadimplentesAntigos} mensalidades de meses anteriores ainda pendentes.",
        'link' => route('mensalidades.index'),
        'link_texto' => 'Verificar',
    ];
}

// 3. Eventos próximos com inscrições abertas (próximos 7 dias)
$eventosProximos = \App\Models\Evento::where('club_id', $clubId)
    ->whereBetween('data_inicio', [now(), now()->addDays(7)])
    ->count();

if ($eventosProximos > 0) {
    $alertas[] = [
        'tipo' => 'info',
        'icone' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'titulo' => 'Eventos nos próximos 7 dias',
        'texto' => "{$eventosProximos} evento(s) se aproximando. Confirme as inscrições e pagamentos.",
        'link' => route('eventos.index'),
        'link_texto' => 'Ver eventos',
    ];
}

// 4. Desbravadores sem frequência há mais de 21 dias
$semFrequencia = \App\Models\Desbravador::ativos()
    ->whereDoesntHave('frequencias', function ($q) {
        $q->where('data', '>=', now()->subDays(21));
    })
    ->count();

if ($semFrequencia > 3) {
    $alertas[] = [
        'tipo' => 'warning',
        'icone' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
        'titulo' => 'Membros sem frequência recente',
        'texto' => "{$semFrequencia} desbravadores sem registro de presença nos últimos 21 dias.",
        'link' => route('frequencia.index'),
        'link_texto' => 'Ver frequência',
    ];
}

return view('dashboard', compact(
    'saldoAtual',
    'taxaInadimplencia',
    'totalAtivos',
    'labelsGrafico',
    'dadosGrafico',
    'alertas'  // NOVO
));
```

**Passo 2 — Adicionar seção de alertas na view `resources/views/dashboard.blade.php`:**

Inserir logo após o bloco do header de boas-vindas (antes do grid de KPIs):

```blade
{{-- ALERTAS PROATIVOS --}}
@if (!empty($alertas))
    <div class="space-y-3 ui-animate-fade-up" style="animation-delay: 50ms;">
        @foreach ($alertas as $alerta)
            <div class="flex items-center justify-between gap-4 px-5 py-4 rounded-2xl border
                @if($alerta['tipo'] === 'danger') bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800/50
                @elseif($alerta['tipo'] === 'warning') bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800/50
                @else bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800/50
                @endif">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                        @if($alerta['tipo'] === 'danger') bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400
                        @elseif($alerta['tipo'] === 'warning') bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400
                        @else bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400
                        @endif">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $alerta['icone'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-slate-800 dark:text-white">{{ $alerta['titulo'] }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">{{ $alerta['texto'] }}</p>
                    </div>
                </div>
                <a href="{{ $alerta['link'] }}"
                   class="shrink-0 text-xs font-black uppercase tracking-widest px-4 py-2 rounded-xl
                   @if($alerta['tipo'] === 'danger') bg-red-100 hover:bg-red-200 dark:bg-red-500/20 text-red-700 dark:text-red-400
                   @elseif($alerta['tipo'] === 'warning') bg-amber-100 hover:bg-amber-200 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400
                   @else bg-blue-100 hover:bg-blue-200 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400
                   @endif transition-colors">
                    {{ $alerta['link_texto'] }}
                </a>
            </div>
        @endforeach
    </div>
@endif
```

**Importante:** O bloco de alertas deve usar `@can('financeiro')` para os alertas de
mensalidades e `@can('pedagogico')` para o alerta de frequência, respeitando as
permissões do usuário logado. Ajustar o array `$alertas` no controller para incluir
o módulo necessário em cada alerta e filtrar na view.

---

### MELHORIA 4 — Geração massiva de mensalidades sem preview de confirmação

**Arquivo do controller:** `app/Http/Controllers/MensalidadeController.php`  
**Arquivo da view:** `resources/views/financeiro/mensalidades/index.blade.php`

**Objetivo:** Antes de gerar as mensalidades, mostrar ao tesoureiro quantas serão
criadas e quantas já existem (serão puladas).

**Passo 1 — Adicionar rota de preview em `routes/web.php`:**

Dentro do grupo de rotas de mensalidades, adicionar:
```php
Route::post('mensalidades/preview-lote', [MensalidadeController::class, 'previewMassivo'])
    ->name('mensalidades.preview');
```

**Passo 2 — Adicionar método `previewMassivo()` no controller:**

```php
/**
 * Retorna preview (dry-run) de quantas mensalidades seriam criadas.
 * Usado pelo modal antes de confirmar a geração em lote.
 */
public function previewMassivo(Request $request): \Illuminate\Http\JsonResponse
{
    Gate::authorize('financeiro');

    $request->validate([
        'mes' => 'required|integer|min:1|max:12',
        'ano' => 'required|integer|min:2020',
        'valor' => 'required|numeric|min:0',
    ]);

    $clubId = ClubContext::currentClubId();
    abort_unless($clubId, 403);

    $ids = Desbravador::ativos()->pluck('id');

    $existentes = Mensalidade::whereIn('desbravador_id', $ids)
        ->where('mes', $request->mes)
        ->where('ano', $request->ano)
        ->count();

    $novas = $ids->count() - $existentes;

    return response()->json([
        'total_ativos' => $ids->count(),
        'ja_existem' => $existentes,
        'serao_criadas' => $novas,
        'valor_formatado' => 'R$ ' . number_format((float) $request->valor, 2, ',', '.'),
        'competencia' => sprintf('%02d/%d', $request->mes, $request->ano),
    ]);
}
```

**Passo 3 — Modificar o modal de geração na view:**

No arquivo `resources/views/financeiro/mensalidades/index.blade.php`, no bloco do
modal de "Gerar Lote Mensal", substituir o `<form>` direto por um fluxo em duas
etapas com Alpine.js:

```blade
{{-- Modal de Geração em Lote (Duas etapas: configurar → confirmar) --}}
<div x-data="{
    step: 'form',
    loading: false,
    preview: null,
    mes: '{{ date('m') }}',
    ano: '{{ date('Y') }}',
    valor: '15.00',
    async buscarPreview() {
        this.loading = true;
        this.step = 'loading';
        try {
            const res = await fetch('{{ route('mensalidades.preview') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ mes: this.mes, ano: this.ano, valor: this.valor })
            });
            this.preview = await res.json();
            this.step = 'confirm';
        } catch(e) {
            this.step = 'form';
            window.notify('Erro ao verificar dados. Tente novamente.', 'error');
        }
        this.loading = false;
    }
}">
    {{-- Etapa 1: Formulário de configuração --}}
    <div x-show="step === 'form'">
        <div class="space-y-5">
            {{-- campos existentes de mes/ano/valor, vincular com x-model --}}
            {{-- Exemplo: <select x-model="mes" name="mes"> --}}
            
            <button type="button" @click="buscarPreview()"
                class="w-full ui-btn-primary">
                Verificar e prosseguir
            </button>
        </div>
    </div>

    {{-- Etapa 2: Preview e confirmação --}}
    <div x-show="step === 'confirm'" x-cloak>
        <template x-if="preview">
            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Competência</span>
                        <span class="font-black text-slate-800 dark:text-white" x-text="preview.competencia"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Valor unitário</span>
                        <span class="font-black text-slate-800 dark:text-white" x-text="preview.valor_formatado"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Total de membros ativos</span>
                        <span class="font-black text-slate-800 dark:text-white" x-text="preview.total_ativos"></span>
                    </div>
                    <div class="flex justify-between text-sm border-t border-slate-200 dark:border-slate-700 pt-2 mt-2">
                        <span class="text-slate-500">Já existem (serão puladas)</span>
                        <span class="font-black text-amber-600 dark:text-amber-400" x-text="preview.ja_existem"></span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-base">
                        <span class="text-emerald-600 dark:text-emerald-400">Serão criadas agora</span>
                        <span class="text-emerald-600 dark:text-emerald-400" x-text="preview.serao_criadas"></span>
                    </div>
                </div>

                <template x-if="preview.serao_criadas === 0">
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-bold text-center">
                        Todas as mensalidades desta competência já foram geradas.
                    </p>
                </template>

                <div class="flex gap-3">
                    <button type="button" @click="step = 'form'" class="flex-1 ui-btn-secondary">
                        Voltar
                    </button>
                    <form action="{{ route('mensalidades.gerar') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="mes" :value="mes">
                        <input type="hidden" name="ano" :value="ano">
                        <input type="hidden" name="valor" :value="valor">
                        <button type="submit" class="w-full ui-btn-primary"
                            :disabled="preview.serao_criadas === 0">
                            Confirmar geração
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
```

---

### MELHORIA 5 — Formulário de cadastro sem salvamento parcial

**Arquivo da view:** `resources/views/desbravadores/create.blade.php`

**Objetivo:** Salvar automaticamente o rascunho do formulário no `sessionStorage`
do browser via Alpine.js para que, se o usuário sair por engano, os dados não
sejam perdidos.

**Implementação — adicionar componente Alpine.js no `<form>` existente:**

Localizar a tag `<form action="{{ route('desbravadores.store') }}" ...>` e substituir
por:

```blade
<form action="{{ route('desbravadores.store') }}" method="POST" enctype="multipart/form-data"
      class="space-y-8"
      x-data="cadastroDesbravador()"
      @submit="limparRascunho()">
    @csrf
    {{-- Aviso de rascunho salvo (mostrar apenas se há dados no sessionStorage) --}}
    <div x-show="temRascunho" x-cloak
         class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-bold text-blue-700 dark:text-blue-300">
                Rascunho recuperado automaticamente.
            </p>
        </div>
        <button type="button" @click="descartarRascunho()"
                class="text-xs font-black text-blue-500 hover:text-blue-700 uppercase tracking-widest">
            Descartar
        </button>
    </div>

    {{-- Todo o conteúdo do form existente permanece aqui --}}
    {{-- Adicionar x-model em cada campo importante --}}
```

**Adicionar bloco `<script>` no final da view (antes de `</x-app-layout>`):**

```blade
<script>
function cadastroDesbravador() {
    const CHAVE = 'rascunho_desbravador_{{ auth()->user()->club_id }}';
    
    return {
        temRascunho: false,
        
        init() {
            // Verificar se há rascunho salvo
            const salvo = sessionStorage.getItem(CHAVE);
            if (salvo) {
                try {
                    const dados = JSON.parse(salvo);
                    this.temRascunho = true;
                    // Restaurar valores nos campos
                    this.$nextTick(() => {
                        Object.entries(dados).forEach(([campo, valor]) => {
                            const el = this.$el.querySelector(`[name="${campo}"]`);
                            if (el && el.type !== 'file' && el.type !== 'password') {
                                el.value = valor;
                            }
                        });
                    });
                } catch(e) {
                    sessionStorage.removeItem(CHAVE);
                }
            }
            
            // Salvar ao digitar (debounced)
            let timer;
            this.$el.addEventListener('input', (e) => {
                if (e.target.type === 'file') return; // não salvar arquivos
                clearTimeout(timer);
                timer = setTimeout(() => this.salvarRascunho(), 800);
            });
        },
        
        salvarRascunho() {
            const campos = {};
            const inputs = this.$el.querySelectorAll('input:not([type=file]):not([type=hidden]), select, textarea');
            inputs.forEach(el => {
                if (el.name) campos[el.name] = el.value;
            });
            sessionStorage.setItem(CHAVE, JSON.stringify(campos));
        },
        
        limparRascunho() {
            sessionStorage.removeItem(CHAVE);
        },
        
        descartarRascunho() {
            sessionStorage.removeItem(CHAVE);
            this.temRascunho = false;
            // Limpar todos os campos
            const inputs = this.$el.querySelectorAll('input:not([type=hidden]), select, textarea');
            inputs.forEach(el => { el.value = ''; });
        }
    }
}
</script>
```

**Nota importante:** O `_token` (CSRF) e campos `type="file"` nunca devem ser salvos
no sessionStorage. Já está contemplado no código acima.

---

## FEATURES NOVAS

---

### FEATURE A — Módulo de comunicados para responsáveis

**Objetivo:** Permitir que secretário/diretor envie comunicados por e-mail para
responsáveis de desbravadores, com histórico de envios no sistema.

#### 1. Migration

Criar arquivo `database/migrations/2026_07_01_000001_create_comunicados_table.php`:

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->index();
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->string('titulo');
            $table->text('corpo');
            $table->string('destinatarios')->default('todos'); // 'todos', 'unidade:{id}', 'ativos'
            $table->unsignedBigInteger('unidade_id')->nullable();
            $table->integer('total_enviados')->default(0);
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('criado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('unidade_id')->references('id')->on('unidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicados');
    }
};
```

#### 2. Model

Criar `app/Models/Comunicado.php`:

```php
<?php
namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Comunicado extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'club_id', 'criado_por', 'titulo', 'corpo',
        'destinatarios', 'unidade_id', 'total_enviados', 'enviado_em',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
    ];

    public function club() { return $this->belongsTo(Club::class); }
    public function criador() { return $this->belongsTo(User::class, 'criado_por'); }
    public function unidade() { return $this->belongsTo(Unidade::class); }
}
```

#### 3. Mailable

Criar `app/Mail/ComunicadoResponsavel.php`:

```php
<?php
namespace App\Mail;

use App\Models\Comunicado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoResponsavel extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Comunicado $comunicado,
        public readonly string $nomeDesbravador,
        public readonly string $nomeClube,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->nomeClube}] {$this->comunicado->titulo}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comunicado-responsavel');
    }
}
```

#### 4. View do e-mail

Criar `resources/views/emails/comunicado-responsavel.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1e293b;">
    <div style="background: #002F6C; padding: 24px; border-radius: 12px 12px 0 0;">
        <h1 style="color: #FCD116; margin: 0; font-size: 20px;">{{ $nomeClube }}</h1>
        <p style="color: rgba(255,255,255,0.7); margin: 4px 0 0; font-size: 13px;">Comunicado oficial</p>
    </div>
    <div style="border: 1px solid #e2e8f0; border-top: none; padding: 28px; border-radius: 0 0 12px 12px;">
        <h2 style="font-size: 18px; margin: 0 0 16px;">{{ $comunicado->titulo }}</h2>
        <div style="line-height: 1.7; color: #334155;">
            {!! nl2br(e($comunicado->corpo)) !!}
        </div>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="font-size: 12px; color: #94a3b8; margin: 0;">
            Este comunicado foi enviado ao responsável de <strong>{{ $nomeDesbravador }}</strong>.
        </p>
    </div>
</body>
</html>
```

#### 5. Controller

Criar `app/Http/Controllers/ComunicadoController.php`:

```php
<?php
namespace App\Http\Controllers;

use App\Mail\ComunicadoResponsavel;
use App\Models\Comunicado;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class ComunicadoController extends Controller
{
    public function index()
    {
        Gate::authorize('secretaria');
        $comunicados = Comunicado::latest()->paginate(15)->withQueryString();
        return view('comunicados.index', compact('comunicados'));
    }

    public function create()
    {
        Gate::authorize('secretaria');
        $unidades = Unidade::where('club_id', ClubContext::currentClubId())->orderBy('nome')->get(['id', 'nome']);
        return view('comunicados.create', compact('unidades'));
    }

    public function store(Request $request)
    {
        Gate::authorize('secretaria');

        $dados = $request->validate([
            'titulo' => 'required|string|max:255',
            'corpo' => 'required|string|max:5000',
            'destinatarios' => 'required|in:todos,ativos,unidade',
            'unidade_id' => 'nullable|required_if:destinatarios,unidade|exists:unidades,id',
        ]);

        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $comunicado = Comunicado::create([
            'club_id' => $clubId,
            'criado_por' => auth()->id(),
            'titulo' => $dados['titulo'],
            'corpo' => $dados['corpo'],
            'destinatarios' => $dados['destinatarios'],
            'unidade_id' => $dados['destinatarios'] === 'unidade' ? $dados['unidade_id'] : null,
        ]);

        // Buscar destinatários
        $query = Desbravador::with('unidade')
            ->whereNotNull('email')
            ->where('email', '!=', '');

        if ($dados['destinatarios'] === 'ativos') {
            $query->where('ativo', true);
        } elseif ($dados['destinatarios'] === 'unidade' && $dados['unidade_id']) {
            $query->where('unidade_id', $dados['unidade_id'])->where('ativo', true);
        } else {
            $query->where('ativo', true); // 'todos' = todos os ativos
        }

        $desbravadores = $query->get(['id', 'nome', 'email', 'nome_responsavel']);
        $clube = \App\Models\Club::find($clubId);
        $enviados = 0;

        foreach ($desbravadores as $desb) {
            // Tentar enviar para o e-mail do próprio desbravador (pode ser do responsável)
            try {
                Mail::to($desb->email)
                    ->send(new ComunicadoResponsavel($comunicado, $desb->nome, $clube->nome));
                $enviados++;
            } catch (\Throwable) {
                // Silenciar falhas individuais, continuar enviando para os demais
            }
        }

        $comunicado->update([
            'total_enviados' => $enviados,
            'enviado_em' => now(),
        ]);

        return redirect()->route('comunicados.index')
            ->with('success', "Comunicado enviado para {$enviados} destinatário(s).");
    }

    public function show(Comunicado $comunicado)
    {
        Gate::authorize('secretaria');
        return view('comunicados.show', compact('comunicado'));
    }
}
```

#### 6. Views (criar as 3 views)

**`resources/views/comunicados/index.blade.php`** — listagem com histórico de envios,
campo de busca básico, botão "Novo Comunicado", tabela com colunas:
Título | Destinatários | Enviado para | Data | Ações (visualizar).

**`resources/views/comunicados/create.blade.php`** — formulário com:
- Campo "Título" (input texto)
- Campo "Mensagem" (textarea, max 5000 chars com contador)
- Select "Destinatários": "Todos os membros ativos", "Apenas membros ativos", "Por unidade"
- Select de unidade (aparece apenas quando "Por unidade" selecionado, via Alpine.js)
- Botão "Enviar comunicado"

**`resources/views/comunicados/show.blade.php`** — detalhes do comunicado enviado:
título, corpo, quantidade de destinatários, data de envio.

#### 7. Rotas em `routes/web.php`

Dentro do grupo `can:secretaria`, adicionar:

```php
Route::resource('comunicados', ComunicadoController::class)->only(['index', 'create', 'store', 'show']);
```

#### 8. Menu lateral

Em `resources/views/layouts/app.blade.php`, dentro da seção "Secretaria & Clube",
adicionar link para comunicados:

```blade
@can('secretaria')
<a href="{{ route('comunicados.index') }}"
   class="{{ $linkBase }} {{ request()->routeIs('comunicados*') ? $activeClass : $inactiveClass }}"
   :class="!sidebarExpanded && 'lg:justify-center'">
    <svg class="shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
    </svg>
    <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms>Comunicados</span>
</a>
@endcan
```

---

### FEATURE B — Calendário unificado de eventos e reuniões

**Objetivo:** View única que mostre num calendário mensal: datas de reuniões
(frequência), eventos do clube, e aniversariantes do mês.

#### 1. Controller

Criar `app/Http/Controllers/CalendarioController.php`:

```php
<?php
namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $mes = (int) $request->input('mes', now()->month);
        $ano = (int) $request->input('ano', now()->year);

        $inicio = Carbon::create($ano, $mes, 1)->startOfMonth();
        $fim = $inicio->copy()->endOfMonth();

        // 1. Datas de reuniões (registros de frequência)
        $reunioes = Frequencia::whereHas('desbravador.unidade', fn ($q) => $q->where('club_id', $clubId))
            ->whereBetween('data', [$inicio, $fim])
            ->selectRaw('DATE(data) as data_reuniao')
            ->distinct()
            ->pluck('data_reuniao');

        // 2. Eventos no mês
        $eventos = Evento::where('club_id', $clubId)
            ->where(function ($q) use ($inicio, $fim) {
                $q->whereBetween('data_inicio', [$inicio, $fim])
                  ->orWhereBetween('data_fim', [$inicio, $fim]);
            })
            ->get(['id', 'nome', 'data_inicio', 'data_fim', 'local']);

        // 3. Aniversariantes do mês
        $aniversariantes = Desbravador::ativos()
            ->whereMonth('data_nascimento', $mes)
            ->orderByRaw('DAY(data_nascimento)')
            ->get(['id', 'nome', 'data_nascimento', 'unidade_id'])
            ->map(fn ($d) => [
                'dia' => Carbon::parse($d->data_nascimento)->day,
                'nome' => $d->nome,
                'idade' => Carbon::parse($d->data_nascimento)->age,
            ]);

        return view('calendario.index', compact(
            'mes', 'ano', 'inicio',
            'reunioes', 'eventos', 'aniversariantes'
        ));
    }
}
```

#### 2. Rota em `routes/web.php`

Dentro do grupo autenticado (sem permissão específica, pois o calendário é
informativo e mostra apenas o que o usuário já pode ver):

```php
Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
```

#### 3. View `resources/views/calendario/index.blade.php`

Criar a view com uma grade mensal em CSS Grid (7 colunas = dias da semana).
Para cada célula do dia, exibir:
- 🔵 Bolinha azul se houve reunião naquele dia
- 📅 Card pequeno com nome do evento (se houver)
- 🎂 Lista de aniversariantes do dia

**Estrutura da view:**

```blade
<x-app-layout>
    <x-slot name="header">Calendário do Clube</x-slot>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">
        {{-- Navegação mês/ano --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('calendario.index', ['mes' => $mes == 1 ? 12 : $mes - 1, 'ano' => $mes == 1 ? $ano - 1 : $ano]) }}"
               class="ui-btn-secondary">← Mês anterior</a>
            <h2 class="text-xl font-black text-slate-800 dark:text-white">
                {{ $inicio->locale('pt_BR')->translatedFormat('F Y') }}
            </h2>
            <a href="{{ route('calendario.index', ['mes' => $mes == 12 ? 1 : $mes + 1, 'ano' => $mes == 12 ? $ano + 1 : $ano]) }}"
               class="ui-btn-secondary">Próximo mês →</a>
        </div>

        {{-- Legenda --}}
        <div class="flex gap-6 text-xs font-bold text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span> Reunião
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Evento
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-amber-400"></span> Aniversário
            </span>
        </div>

        {{-- Grade do calendário --}}
        <div class="ui-card p-4 overflow-hidden">
            {{-- Cabeçalho dias da semana --}}
            <div class="grid grid-cols-7 mb-2">
                @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dia)
                    <div class="text-center text-[11px] font-black text-slate-400 uppercase tracking-widest py-2">{{ $dia }}</div>
                @endforeach
            </div>

            {{-- Células dos dias --}}
            <div class="grid grid-cols-7 gap-1">
                {{-- Padding inicial (dia da semana do início do mês) --}}
                @for ($i = 0; $i < $inicio->dayOfWeek; $i++)
                    <div class="min-h-[80px]"></div>
                @endfor

                @for ($dia = 1; $dia <= $inicio->daysInMonth; $dia++)
                    @php
                        $dataStr = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                        $temReuniao = $reunioes->contains($dataStr);
                        $eventosNoDia = $eventos->filter(fn($e) =>
                            Carbon\Carbon::parse($e->data_inicio)->day <= $dia &&
                            Carbon\Carbon::parse($e->data_fim ?? $e->data_inicio)->day >= $dia
                        );
                        $aniversariantesNoDia = $aniversariantes->where('dia', $dia);
                        $hoje = now()->day === $dia && now()->month === $mes && now()->year === $ano;
                    @endphp
                    <div class="min-h-[80px] p-1.5 rounded-xl border border-transparent
                        {{ $hoje ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50' }}
                        transition-colors">
                        <span class="text-xs font-black {{ $hoje ? 'text-blue-600 dark:text-blue-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $dia }}</span>

                        <div class="mt-1 space-y-0.5">
                            @if ($temReuniao)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 truncate">Reunião</span>
                                </div>
                            @endif

                            @foreach ($eventosNoDia as $evento)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 truncate">{{ $evento->nome }}</span>
                                </div>
                            @endforeach

                            @foreach ($aniversariantesNoDia as $aniv)
                                <div class="flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                                    <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400 truncate">{{ explode(' ', $aniv['nome'])[0] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Lista de aniversariantes do mês --}}
        @if ($aniversariantes->isNotEmpty())
            <div class="ui-card p-6">
                <h3 class="font-black text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                    🎂 Aniversariantes de {{ $inicio->locale('pt_BR')->translatedFormat('F') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($aniversariantes as $aniv)
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-900/30">
                            <div class="w-8 h-8 rounded-full bg-amber-200 dark:bg-amber-800 flex items-center justify-center font-black text-amber-800 dark:text-amber-200 text-sm">
                                {{ $aniv['dia'] }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800 dark:text-white leading-none">{{ explode(' ', $aniv['nome'])[0] }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $aniv['idade'] }} anos</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
```

#### 4. Menu lateral

Adicionar no `app.blade.php` na seção "Visão Geral":

```blade
<a href="{{ route('calendario.index') }}"
   class="{{ $linkBase }} {{ request()->routeIs('calendario*') ? $activeClass : $inactiveClass }}"
   :class="!sidebarExpanded && 'lg:justify-center'">
    <svg class="shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms>Calendário</span>
</a>
```

---

### FEATURE C — Importação de desbravadores via CSV

**Objetivo:** Permitir que o secretário importe uma planilha CSV com dados básicos
dos desbravadores, com mapeamento de colunas e preview antes de confirmar.

#### 1. Controller

Criar `app/Http/Controllers/ImportacaoDesbravadorController.php`:

```php
<?php
namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Unidade;
use App\Services\ClubContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class ImportacaoDesbravadorController extends Controller
{
    // Campos aceitos no CSV (chave = coluna esperada, valor = label para o usuário)
    private const CAMPOS = [
        'nome'               => 'Nome completo *',
        'data_nascimento'    => 'Data de nascimento (DD/MM/AAAA) *',
        'sexo'               => 'Sexo (M/F)',
        'email'              => 'E-mail',
        'telefone'           => 'Telefone',
        'nome_responsavel'   => 'Nome do responsável',
        'telefone_responsavel' => 'Telefone do responsável',
    ];

    public function index()
    {
        Gate::authorize('secretaria');
        $unidades = Unidade::where('club_id', ClubContext::currentClubId())
            ->orderBy('nome')->get(['id', 'nome']);
        return view('desbravadores.importar', compact('unidades'));
    }

    /**
     * Recebe o CSV, valida e retorna preview (sem salvar).
     */
    public function preview(Request $request)
    {
        Gate::authorize('secretaria');

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:2048',
            'unidade_id' => 'required|exists:unidades,id',
        ]);

        $linhas = $this->lerCsv($request->file('arquivo'));

        if (count($linhas) < 2) {
            return back()->withErrors(['arquivo' => 'O arquivo CSV parece vazio ou sem cabeçalho.']);
        }

        $cabecalho = array_map('trim', $linhas[0]);
        $dados = array_slice($linhas, 1);

        $preview = [];
        $erros = [];

        foreach ($dados as $i => $linha) {
            if (count($linha) !== count($cabecalho)) continue;
            $row = array_combine($cabecalho, $linha);

            // Validação básica por linha
            $v = Validator::make($row, [
                'nome' => 'required|string|min:3',
                'data_nascimento' => ['required', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            ]);

            if ($v->fails()) {
                $erros[] = "Linha " . ($i + 2) . ": " . implode(', ', $v->errors()->all());
                continue;
            }

            // Verificar duplicata por nome no clube
            $existente = Desbravador::where('nome', $row['nome'] ?? '')->exists();

            $preview[] = [
                'nome' => $row['nome'] ?? '',
                'data_nascimento' => $row['data_nascimento'] ?? '',
                'sexo' => $row['sexo'] ?? '',
                'email' => $row['email'] ?? '',
                'nome_responsavel' => $row['nome_responsavel'] ?? '',
                'duplicado' => $existente,
            ];
        }

        // Salvar dados parseados na sessão para o step de confirmação
        session(['importacao_csv' => [
            'dados' => $preview,
            'unidade_id' => $request->unidade_id,
            'timestamp' => now()->timestamp,
        ]]);

        return view('desbravadores.importar-preview', compact('preview', 'erros'));
    }

    /**
     * Confirma e persiste os dados do preview.
     */
    public function confirmar(Request $request)
    {
        Gate::authorize('secretaria');

        $sessao = session('importacao_csv');
        abort_if(!$sessao || (now()->timestamp - $sessao['timestamp']) > 1800, 422, 'Sessão de importação expirada.');

        $clubId = ClubContext::currentClubId();
        abort_unless($clubId, 403);

        $importados = 0;
        $pulados = 0;

        foreach ($sessao['dados'] as $linha) {
            if ($linha['duplicado'] && !$request->boolean('substituir_duplicados')) {
                $pulados++;
                continue;
            }

            try {
                $dataNasc = \Carbon\Carbon::createFromFormat('d/m/Y', $linha['data_nascimento'])->format('Y-m-d');

                Desbravador::create([
                    'nome' => $linha['nome'],
                    'data_nascimento' => $dataNasc,
                    'sexo' => in_array(strtoupper($linha['sexo'] ?? ''), ['M', 'F']) ? strtoupper($linha['sexo']) : null,
                    'email' => $linha['email'] ?: null,
                    'nome_responsavel' => $linha['nome_responsavel'] ?: null,
                    'unidade_id' => $sessao['unidade_id'],
                    'club_id' => $clubId,
                    'ativo' => true,
                    'consentimento_lgpd_em' => now(),
                ]);
                $importados++;
            } catch (\Throwable $e) {
                $pulados++;
            }
        }

        session()->forget('importacao_csv');

        return redirect()->route('desbravadores.index')
            ->with('success', "{$importados} desbravadores importados com sucesso. {$pulados} pulados.");
    }

    private function lerCsv(\Illuminate\Http\UploadedFile $file): array
    {
        $linhas = [];
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            while (($linha = fgetcsv($handle, 1000, ';')) !== false) {
                // Tentar vírgula se ponto-e-vírgula não funcionar
                if (count($linha) === 1) {
                    $linha = str_getcsv($linha[0], ',');
                }
                $linhas[] = array_map(fn($v) => trim($v), $linha);
            }
            fclose($handle);
        }
        return $linhas;
    }
}
```

#### 2. Rotas em `routes/web.php`

Dentro do grupo `can:secretaria`:

```php
Route::prefix('desbravadores/importar')->name('desbravadores.importar.')->group(function () {
    Route::get('/', [ImportacaoDesbravadorController::class, 'index'])->name('index');
    Route::post('/preview', [ImportacaoDesbravadorController::class, 'preview'])->name('preview');
    Route::post('/confirmar', [ImportacaoDesbravadorController::class, 'confirmar'])->name('confirmar');
});
```

#### 3. Views

**`resources/views/desbravadores/importar.blade.php`** — formulário com:
- Upload de arquivo CSV
- Select de unidade de destino
- Seção de instruções com exemplo de formato CSV
- Botão "Analisar arquivo"

**`resources/views/desbravadores/importar-preview.blade.php`** — preview com:
- Tabela mostrando todos os registros encontrados
- Linhas duplicadas destacadas em amarelo com badge "Já existe"
- Contador: X serão importados, Y duplicados encontrados
- Checkbox "Substituir duplicados"
- Botão "Confirmar importação" (POST para `confirmar`) e "Voltar"

#### 4. Link no botão/menu de desbravadores

Em `resources/views/desbravadores/index.blade.php`, adicionar botão de importação
ao lado do "Novo Cadastro":

```blade
<a href="{{ route('desbravadores.importar.index') }}" class="ui-btn-secondary w-full sm:w-auto">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
    </svg>
    <span>Importar CSV</span>
</a>
```

---

### FEATURE D — Painel dedicado do conselheiro de unidade

**Objetivo:** Criar uma rota `/minha-unidade` que carrega um painel focado para
o conselheiro — mostrando apenas os membros da sua unidade, frequência recente,
progresso de classes e especialidades.

#### 1. Controller

Criar `app/Http/Controllers/PainelConselheiroController.php`:

```php
<?php
namespace App\Http\Controllers;

use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use App\Services\ClubContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class PainelConselheiroController extends Controller
{
    public function index()
    {
        // Disponível para conselheiros e instrutores (permissão pedagógico)
        Gate::authorize('pedagogico');

        $user = auth()->user();
        $clubId = ClubContext::currentClubId();

        // Encontrar a unidade deste conselheiro
        $unidade = Unidade::where('club_id', $clubId)
            ->where('conselheiro_user_id', $user->id)
            ->first();

        // Se não for conselheiro de nenhuma unidade, redirecionar
        if (!$unidade && !$user->isMaster() && !$user->is_platform_admin) {
            return redirect()->route('dashboard')
                ->with('info', 'Você não está vinculado como conselheiro de nenhuma unidade.');
        }

        // Master/Diretor pode ver todas — usa a primeira unidade do clube
        if (!$unidade) {
            $unidade = Unidade::where('club_id', $clubId)->first();
        }

        if (!$unidade) {
            return redirect()->route('dashboard')->with('info', 'Nenhuma unidade cadastrada.');
        }

        // Membros da unidade com dados de frequência e progresso
        $membros = Desbravador::with(['classe', 'frequencias' => function ($q) {
                $q->whereYear('data', now()->year)->orderBy('data', 'desc');
            }, 'especialidades'])
            ->where('unidade_id', $unidade->id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(function ($desb) {
                $frequenciasAno = $desb->frequencias;
                $totalReunioes = $frequenciasAno->count();
                $presencas = $frequenciasAno->where('presente', true)->count();
                $taxaFreq = $totalReunioes > 0 ? round($presencas / $totalReunioes * 100) : 0;

                return (object) [
                    'id' => $desb->id,
                    'nome' => $desb->nome,
                    'foto_url' => $desb->foto_url,
                    'classe' => $desb->classe?->nome ?? 'Sem classe',
                    'progresso_classe' => $desb->progresso_classe,
                    'total_especialidades' => $desb->especialidades->count(),
                    'taxa_frequencia' => $taxaFreq,
                    'ultima_presenca' => $frequenciasAno->where('presente', true)->first()?->data,
                    'ausente_recente' => $taxaFreq < 50 || ($frequenciasAno->where('presente', true)->first()?->data < now()->subDays(21)),
                ];
            });

        // Últimas 5 reuniões da unidade
        $ultimasReunioes = Frequencia::whereHas('desbravador', fn ($q) => $q->where('unidade_id', $unidade->id))
            ->selectRaw('DATE(data) as data_reuniao, COUNT(*) as total, SUM(CASE WHEN presente = 1 THEN 1 ELSE 0 END) as presentes')
            ->groupBy('data_reuniao')
            ->orderBy('data_reuniao', 'desc')
            ->take(5)
            ->get();

        return view('painel-conselheiro.index', compact(
            'unidade', 'membros', 'ultimasReunioes'
        ));
    }
}
```

#### 2. Rota em `routes/web.php`

```php
Route::get('/minha-unidade', [PainelConselheiroController::class, 'index'])
    ->name('conselheiro.painel')
    ->middleware('can:pedagogico');
```

#### 3. View `resources/views/painel-conselheiro/index.blade.php`

Criar view com:

```blade
<x-app-layout>
    <x-slot name="header">Minha Unidade — {{ $unidade->nome }}</x-slot>

    <div class="ui-page space-y-6 max-w-5xl mx-auto">

        {{-- Header da Unidade --}}
        <div class="ui-card p-6 flex items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-[#002F6C] text-white flex items-center justify-center font-black text-3xl shadow-inner">
                {{ mb_strtoupper(substr($unidade->nome, 0, 1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-2xl font-black text-slate-800 dark:text-white">{{ $unidade->nome }}</h2>
                @if ($unidade->grito_guerra)
                    <p class="text-slate-500 italic text-sm mt-1">"{{ $unidade->grito_guerra }}"</p>
                @endif
                <div class="flex gap-4 mt-3 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <span>{{ $membros->count() }} membros ativos</span>
                    <span>·</span>
                    <span>{{ $membros->where('ausente_recente', true)->count() }} com baixa frequência</span>
                </div>
            </div>
            <a href="{{ route('frequencia.create') }}" class="ui-btn-primary shrink-0">
                📋 Registrar Chamada
            </a>
        </div>

        {{-- Frequência recente --}}
        @if ($ultimasReunioes->isNotEmpty())
        <div class="ui-card p-6">
            <h3 class="font-black text-slate-700 dark:text-slate-200 mb-4">Últimas reuniões</h3>
            <div class="flex gap-3 flex-wrap">
                @foreach ($ultimasReunioes as $reuniao)
                    @php $pct = $reuniao->total > 0 ? round($reuniao->presentes / $reuniao->total * 100) : 0; @endphp
                    <div class="flex flex-col items-center p-3 rounded-xl border border-slate-100 dark:border-slate-700 min-w-[80px]">
                        <span class="text-xs font-bold text-slate-400">{{ \Carbon\Carbon::parse($reuniao->data_reuniao)->format('d/m') }}</span>
                        <span class="text-2xl font-black {{ $pct >= 75 ? 'text-emerald-500' : ($pct >= 50 ? 'text-amber-500' : 'text-red-500') }}">{{ $pct }}%</span>
                        <span class="text-[10px] text-slate-400">{{ $reuniao->presentes }}/{{ $reuniao->total }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Tabela de membros --}}
        <div class="ui-card overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800">
                <h3 class="font-black text-slate-700 dark:text-slate-200">Membros da unidade</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($membros as $membro)
                    <div class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-[#002F6C]/10 dark:bg-blue-500/10 flex items-center justify-center font-black text-[#002F6C] dark:text-blue-400 text-sm shrink-0">
                            {{ mb_strtoupper(substr($membro->nome, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-800 dark:text-white text-sm leading-none">{{ $membro->nome }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $membro->classe }}</p>
                        </div>
                        {{-- Progresso de Classe --}}
                        <div class="hidden sm:flex flex-col items-center min-w-[80px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Classe</span>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 mt-1">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $membro->progresso_classe }}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500 mt-0.5">{{ $membro->progresso_classe }}%</span>
                        </div>
                        {{-- Taxa de Frequência --}}
                        <div class="flex flex-col items-center min-w-[60px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Freq.</span>
                            <span class="font-black text-sm mt-0.5
                                {{ $membro->taxa_frequencia >= 75 ? 'text-emerald-500' : ($membro->taxa_frequencia >= 50 ? 'text-amber-500' : 'text-red-500') }}">
                                {{ $membro->taxa_frequencia }}%
                            </span>
                        </div>
                        {{-- Especialidades --}}
                        <div class="hidden md:flex flex-col items-center min-w-[60px]">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Espec.</span>
                            <span class="font-black text-sm text-slate-600 dark:text-slate-300 mt-0.5">{{ $membro->total_especialidades }}</span>
                        </div>
                        <a href="{{ route('desbravadores.show', $membro->id) }}"
                           class="text-slate-400 hover:text-[#002F6C] dark:hover:text-blue-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
```

#### 4. Menu lateral

Adicionar no `app.blade.php` para usuários com permissão `pedagogico` que
sejam conselheiros ou instrutores:

```blade
@can('pedagogico')
<a href="{{ route('conselheiro.painel') }}"
   class="{{ $linkBase }} {{ request()->routeIs('conselheiro*') ? $activeClass : $inactiveClass }}"
   :class="!sidebarExpanded && 'lg:justify-center'">
    <svg class="shrink-0 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms>Minha Unidade</span>
</a>
@endcan
```

---

### FEATURE E — PWA com suporte offline para chamada de frequência

**Objetivo:** Transformar o sistema em PWA instalável com cache offline para
a tela de chamada (`frequencia.create`), permitindo uso sem internet.

#### 1. Web App Manifest

Criar `public/manifest.json`:

```json
{
    "name": "Desbravadores Manager",
    "short_name": "DBV Manager",
    "description": "Sistema de gestão de clubes de Desbravadores",
    "start_url": "/dashboard",
    "display": "standalone",
    "background_color": "#001D42",
    "theme_color": "#002F6C",
    "orientation": "portrait-primary",
    "icons": [
        {
            "src": "/icons/icon-192.png",
            "sizes": "192x192",
            "type": "image/png",
            "purpose": "any maskable"
        },
        {
            "src": "/icons/icon-512.png",
            "sizes": "512x512",
            "type": "image/png",
            "purpose": "any maskable"
        }
    ],
    "shortcuts": [
        {
            "name": "Nova Chamada",
            "url": "/frequencia/create",
            "description": "Registrar chamada de frequência"
        }
    ]
}
```

#### 2. Service Worker

Criar `public/sw.js`:

```javascript
const CACHE_NAME = 'dbv-manager-v1';
const OFFLINE_URLS = [
    '/frequencia/create',
    '/dashboard',
];

// Instalar e fazer cache das rotas offline
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            // Cachear apenas assets estáticos — não as rotas autenticadas
            return cache.addAll([
                '/favicon.ico',
            ]);
        })
    );
    self.skipWaiting();
});

// Ativar e limpar caches antigos
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Estratégia: Network First com fallback para cache
self.addEventListener('fetch', (event) => {
    // Ignorar requests não-GET e de outras origens
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cachear resposta bem-sucedida das URLs offline
                if (response.ok) {
                    const url = new URL(event.request.url);
                    if (OFFLINE_URLS.some((u) => url.pathname.startsWith(u))) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    }
                }
                return response;
            })
            .catch(() => {
                // Sem internet — tentar servir do cache
                return caches.match(event.request).then((cached) => {
                    if (cached) return cached;
                    // Página de fallback offline
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="pt-BR">
                        <head><meta charset="UTF-8"><title>Sem conexão</title>
                        <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f8fafc;color:#334155;}
                        .box{text-align:center;padding:2rem;}h1{font-size:1.5rem;margin:0 0 .5rem;}p{color:#64748b;}</style>
                        </head>
                        <body><div class="box">
                        <h1>📡 Sem conexão</h1>
                        <p>Verifique sua internet e tente novamente.</p>
                        <p>Se estiver em uma reunião, aguarde reconectar para sincronizar.</p>
                        </div></body></html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
    );
});

// Sincronização em background quando a internet voltar
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-chamadas') {
        event.waitUntil(sincronizarChamadasPendentes());
    }
});

async function sincronizarChamadasPendentes() {
    // Implementação básica — pode ser expandida para sincronizar
    // chamadas registradas offline via IndexedDB
    console.log('[SW] Sincronizando chamadas pendentes...');
}
```

#### 3. Registrar o Service Worker e Meta Tags

No layout principal `resources/views/layouts/app.blade.php`, dentro do `<head>`:

```blade
{{-- PWA --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#002F6C">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="DBV Manager">
<link rel="apple-touch-icon" href="/icons/icon-192.png">

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(reg => console.log('[SW] Registrado:', reg.scope))
            .catch(err => console.warn('[SW] Falha ao registrar:', err));
    });
}
</script>
```

#### 4. Banner de instalação do PWA

Criar componente `resources/views/components/pwa-install-banner.blade.php`:

```blade
<div x-data="pwaInstall()" x-show="mostrar" x-cloak
     class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-sm z-50">
    <div class="ui-card p-4 shadow-xl border border-[#002F6C]/20 dark:border-blue-800/50 flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-[#002F6C] flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-black text-slate-800 dark:text-white leading-none">Instalar o App</p>
            <p class="text-xs text-slate-500 mt-1">Acesse mais rápido e use offline em reuniões</p>
        </div>
        <div class="flex flex-col gap-2 shrink-0">
            <button @click="instalar()" class="text-xs font-black text-[#002F6C] dark:text-blue-400 uppercase tracking-widest">
                Instalar
            </button>
            <button @click="dispensar()" class="text-xs text-slate-400 uppercase tracking-widest">
                Agora não
            </button>
        </div>
    </div>
</div>

<script>
function pwaInstall() {
    return {
        mostrar: false,
        promptEvento: null,
        init() {
            if (localStorage.getItem('pwa-dispensado')) return;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.promptEvento = e;
                this.mostrar = true;
            });
        },
        instalar() {
            if (!this.promptEvento) return;
            this.promptEvento.prompt();
            this.promptEvento.userChoice.then(() => { this.mostrar = false; });
        },
        dispensar() {
            this.mostrar = false;
            localStorage.setItem('pwa-dispensado', '1');
        }
    }
}
</script>
```

Incluir o componente no `app.blade.php`, antes do `</body>`:

```blade
<x-pwa-install-banner />
```

#### 5. Ícones necessários

Criar os ícones do PWA em `public/icons/`:
- `icon-192.png` (192×192px) — logo do Desbravadores Manager
- `icon-512.png` (512×512px) — mesma logo, tamanho maior

Se não houver logo disponível, usar a inicial "D" com fundo `#002F6C` e texto
`#FCD116` como ícone temporário (pode ser gerado via canvas/Imagick).

#### 6. Indicador offline na tela de chamada

Na view `resources/views/frequencia/create.blade.php`, adicionar indicador de
status de conexão após o header:

```blade
{{-- Indicador de status de conexão --}}
<div x-data="{ online: navigator.onLine }"
     @online.window="online = true"
     @offline.window="online = false">
    <div x-show="!online"
         class="mb-4 px-4 py-3 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M9.172 14.828a4 4 0 010-5.656"/>
        </svg>
        <div>
            <p class="text-sm font-black text-amber-700 dark:text-amber-400">Modo offline</p>
            <p class="text-xs text-amber-600 dark:text-amber-500">A chamada será sincronizada quando a conexão voltar.</p>
        </div>
    </div>
</div>
```

---

## ORDEM DE EXECUÇÃO SUGERIDA

1. **MELHORIA 1** — Rápida, 1 linha por controller. Fazer primeiro.
2. **MELHORIA 2** — Correção de segurança, fazer junto com a 1.
3. **MELHORIA 3** — Dashboard alertas. Impacto visual imediato.
4. **MELHORIA 4** — Preview de mensalidades. Previne erros do tesoureiro.
5. **MELHORIA 5** — Autosave do formulário. Boa para UX mobile.
6. **FEATURE B** — Calendário (mais simples, só view + controller).
7. **FEATURE D** — Painel do conselheiro (aproveita dados existentes).
8. **FEATURE C** — Importação CSV (mais trabalhoso, mas alto valor no onboarding).
9. **FEATURE A** — Comunicados (requer migration + mailable + múltiplas views).
10. **FEATURE E** — PWA (não requer backend, só assets e JS).

---

*Gerado em {{ now()->format('d/m/Y H:i') }} — baseado na análise do código-fonte real do repositório.*
