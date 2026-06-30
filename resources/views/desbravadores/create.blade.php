<x-app-layout>
    <div class="ui-page max-w-5xl space-y-8 ui-animate-fade-up">

        <x-page-title title="Novo Desbravador" :back="route('desbravadores.index')" />

        <div class="ui-card p-6 sm:p-8">
            
            @if ($errors->any())
                <div class="mb-8 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-900/50 rounded-2xl flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-800 dark:text-red-400 mb-2">Por favor, verifique os erros abaixo:</h3>
                        <ul class="text-sm font-medium text-red-700 dark:text-red-300 space-y-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('desbravadores.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8"
                  x-data="cadastroDesbravador()" @submit="limparRascunho()">
                @csrf

                {{-- Aviso de rascunho recuperado --}}
                <div x-show="temRascunho" x-cloak
                     class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-bold text-blue-700 dark:text-blue-300">Rascunho recuperado automaticamente.</p>
                    </div>
                    <button type="button" @click="descartarRascunho()"
                            class="text-xs font-black text-blue-500 hover:text-blue-700 uppercase tracking-widest shrink-0">
                        Descartar
                    </button>
                </div>

                {{-- DADOS PESSOAIS --}}
                <div class="p-6 rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20">
                    <h3 class="text-lg font-black text-[#002F6C] dark:text-blue-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Dados Pessoais
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label for="nome" class="ui-input-label">Nome Completo *</label>
                            <input id="nome" name="nome" type="text" class="ui-input" value="{{ old('nome') }}" required autofocus />
                        </div>

                        <div>
                            <label for="data_nascimento" class="ui-input-label">Data de Nascimento *</label>
                            <input id="data_nascimento" name="data_nascimento" type="date" class="ui-input" value="{{ old('data_nascimento') }}" required />
                        </div>

                        <div>
                            <label for="sexo" class="ui-input-label">Sexo *</label>
                            <div class="relative">
                                <select id="sexo" name="sexo" class="ui-input appearance-none pr-10" required>
                                    <option value="">Selecione...</option>
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="unidade_id" class="ui-input-label">Unidade *</label>
                            <div class="relative">
                                <select id="unidade_id" name="unidade_id" class="ui-input appearance-none pr-10" required>
                                    <option value="">Selecione uma unidade...</option>
                                    @foreach ($unidades as $unidade)
                                        <option value="{{ $unidade->id }}" {{ old('unidade_id') == $unidade->id ? 'selected' : '' }}>
                                            {{ $unidade->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="classe_atual" class="ui-input-label">Classe Atual</label>
                            <div class="relative">
                                <select id="classe_atual" name="classe_atual" class="ui-input appearance-none pr-10">
                                    <option value="">Selecione a classe...</option>
                                    @foreach ($classes as $classe)
                                        <option value="{{ $classe->id }}" {{ old('classe_atual') == $classe->id ? 'selected' : '' }}>
                                            {{ $classe->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DOCUMENTOS --}}
                <div class="p-6 rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20">
                    <h3 class="text-lg font-black text-[#002F6C] dark:text-blue-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        Documentos
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="cpf" class="ui-input-label">CPF *</label>
                            <input id="cpf" name="cpf" type="text" class="ui-input font-mono" value="{{ old('cpf') }}" required placeholder="000.000.000-00" />
                        </div>
                        <div>
                            <label for="rg" class="ui-input-label">RG</label>
                            <input id="rg" name="rg" type="text" class="ui-input font-mono" value="{{ old('rg') }}" placeholder="00.000.000-X" />
                        </div>
                    </div>
                </div>

                {{-- CONTATO E RESPONSÁVEL --}}
                <div class="p-6 rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20">
                    <h3 class="text-lg font-black text-[#002F6C] dark:text-blue-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Contato e Responsável
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="ui-input-label">E-mail *</label>
                            <input id="email" name="email" type="email" class="ui-input" value="{{ old('email') }}" required placeholder="exemplo@email.com" />
                        </div>
                        <div>
                            <label for="telefone" class="ui-input-label">Telefone (Celular)</label>
                            <input id="telefone" name="telefone" type="text" class="ui-input" value="{{ old('telefone') }}" placeholder="(XX) XXXXX-XXXX" />
                        </div>
                        <div class="md:col-span-2">
                            <label for="endereco" class="ui-input-label">Endereço Completo *</label>
                            <input id="endereco" name="endereco" type="text" class="ui-input" value="{{ old('endereco') }}" required placeholder="Rua, Número, Bairro, Cidade - Estado" />
                        </div>
                        <div>
                            <label for="nome_responsavel" class="ui-input-label">Nome do Responsável *</label>
                            <input id="nome_responsavel" name="nome_responsavel" type="text" class="ui-input" value="{{ old('nome_responsavel') }}" required />
                        </div>
                        <div>
                            <label for="telefone_responsavel" class="ui-input-label">Telefone do Responsável *</label>
                            <input id="telefone_responsavel" name="telefone_responsavel" type="text" class="ui-input" value="{{ old('telefone_responsavel') }}" required placeholder="(XX) XXXXX-XXXX" />
                        </div>
                    </div>
                </div>

                {{-- FICHA MÉDICA --}}
                <div class="p-6 rounded-3xl border border-red-100 dark:border-red-900/30 bg-red-50/30 dark:bg-red-900/10">
                    <h3 class="text-lg font-black text-red-600 dark:text-red-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Ficha Médica
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                        <div class="md:col-span-3">
                            <label for="numero_sus" class="ui-input-label">Cartão SUS (Obrigatório) *</label>
                            <input id="numero_sus" name="numero_sus" type="text" class="ui-input font-mono" value="{{ old('numero_sus') }}" required />
                        </div>
                        <div class="md:col-span-1">
                            <label for="tipo_sanguineo" class="ui-input-label">Tipo Sang.</label>
                            <div class="relative">
                                <select id="tipo_sanguineo" name="tipo_sanguineo" class="ui-input appearance-none pr-10 focus:ring-red-500 focus:border-red-500 text-sm">
                                    <option value="">Não sei</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                        <option value="{{ $tipo }}" {{ old('tipo_sanguineo') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="plano_saude" class="ui-input-label">Plano de Saúde</label>
                            <input id="plano_saude" name="plano_saude" type="text" class="ui-input" value="{{ old('plano_saude') }}" placeholder="Opcional" />
                        </div>
                        <div class="md:col-span-6 border-t border-red-200/50 dark:border-red-800/30 pt-6">
                            <label for="alergias" class="ui-input-label">Alergias (Se houver)</label>
                            <input id="alergias" name="alergias" type="text" class="ui-input" value="{{ old('alergias') }}" placeholder="Ex: Dipirona, poeira, amendoim..." />
                        </div>
                        <div class="md:col-span-6">
                            <label for="medicamentos_continuos" class="ui-input-label">Medicamentos de Uso Contínuo</label>
                            <input id="medicamentos_continuos" name="medicamentos_continuos" type="text" class="ui-input" value="{{ old('medicamentos_continuos') }}" placeholder="Ex: Insulina, bombinha de asma..." />
                        </div>
                    </div>
                </div>

                {{-- FOTO DE PERFIL --}}
                <div x-data="fotoUpload()" class="p-6 rounded-3xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/20">
                    <h3 class="text-lg font-black text-[#002F6C] dark:text-blue-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#FCD116]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Foto de Perfil <span class="text-sm font-medium text-slate-400">(opcional)</span>
                    </h3>

                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <div class="relative shrink-0">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover" alt="Preview da foto">
                                </template>
                                <template x-if="!preview">
                                    <svg class="w-14 h-14 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </template>
                            </div>
                        </div>

                        <div class="flex-1 text-center sm:text-left space-y-3">
                            <p x-show="erro" x-text="erro" class="text-sm font-medium text-red-600 dark:text-red-400"></p>
                            <p class="text-sm text-slate-500 dark:text-slate-400">JPEG, PNG ou WebP · Máximo 5 MB · Será redimensionada para 400×400 px</p>
                            <label class="ui-btn-secondary cursor-pointer inline-flex items-center gap-2 px-5 py-2.5 text-sm" aria-label="Selecionar foto de perfil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Escolher foto
                                <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="validar($event)">
                            </label>
                            <button x-show="preview" type="button" @click="limpar()" class="ml-3 text-sm font-medium text-red-500 hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                Remover
                            </button>
                        </div>
                    </div>
                </div>

                {{-- CONSENTIMENTO LGPD (Art. 14) --}}
                <div class="p-6 rounded-3xl border-2 border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-900/10">
                    <h3 class="text-base font-black text-amber-800 dark:text-amber-400 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Consentimento LGPD — Obrigatório
                    </h3>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mb-4">
                        Por tratar-se de dado pessoal de menor de idade, a Lei Geral de Proteção de Dados
                        (Art. 14, LGPD) exige o consentimento expresso do responsável legal.
                    </p>

                    <div class="mb-4">
                        <label for="consentimento_lgpd_responsavel" class="block text-xs font-bold text-amber-800 dark:text-amber-400 uppercase tracking-widest mb-2">
                            Nome do Responsável Legal que autoriza *
                        </label>
                        <input type="text" id="consentimento_lgpd_responsavel" name="consentimento_lgpd_responsavel"
                            value="{{ old('consentimento_lgpd_responsavel') }}"
                            placeholder="Nome completo do responsável que está autorizando"
                            class="block w-full rounded-2xl border border-amber-200 dark:border-amber-800 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 px-4 py-3 text-sm focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                        @error('consentimento_lgpd_responsavel')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer group">
                        <div class="relative mt-0.5 shrink-0">
                            <input type="checkbox" name="consentimento_lgpd" id="consentimento_lgpd" value="1"
                                {{ old('consentimento_lgpd') ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-2 border-amber-400 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        </div>
                        <span class="text-sm text-amber-800 dark:text-amber-300 font-medium leading-relaxed">
                            Declaro que o responsável legal pelo menor leu e concordou com a
                            <a href="{{ route('legal.privacidade') }}" target="_blank" class="font-black underline hover:text-amber-900 dark:hover:text-amber-100">
                                Política de Privacidade
                            </a>
                            e autoriza expressamente o tratamento dos dados pessoais do menor para
                            os fins descritos nesta política. *
                        </span>
                    </label>
                    @error('consentimento_lgpd')
                        <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- SUBMIT --}}
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-4 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('desbravadores.index') }}" class="ui-btn-secondary px-8 w-full sm:w-auto">
                        Cancelar
                    </a>
                    <button type="submit" class="ui-btn-primary px-8 w-full sm:w-auto text-[16px]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Salvar Desbravador
                    </button>
                </div>
            </form>

        </div>
    </div>
<script>
function fotoUpload() {
    return {
        preview: null,
        erro: null,
        validar(e) {
            this.erro = null;
            const file = e.target.files[0];
            if (!file) return;
            const tipos = ['image/jpeg', 'image/png', 'image/webp'];
            if (!tipos.includes(file.type)) {
                this.erro = 'Formato inválido. Use JPEG, PNG ou WebP.';
                e.target.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.erro = 'A foto não pode ultrapassar 5 MB.';
                e.target.value = '';
                return;
            }
            this.preview = URL.createObjectURL(file);
        },
        limpar() {
            this.preview = null;
            this.erro = null;
            this.$el.querySelector('input[type=file]').value = '';
        },
    };
}
</script>
    <script>
        function cadastroDesbravador() {
            const CHAVE = 'rascunho_desbravador_{{ auth()->user()->club_id }}';

            return {
                temRascunho: false,

                init() {
                    const salvo = sessionStorage.getItem(CHAVE);
                    if (salvo) {
                        try {
                            const dados = JSON.parse(salvo);
                            this.temRascunho = true;
                            this.$nextTick(() => {
                                Object.entries(dados).forEach(([campo, valor]) => {
                                    const el = this.$el.querySelector(`[name="${campo}"]`);
                                    if (el && el.type !== 'file' && el.type !== 'password') {
                                        el.value = valor;
                                    }
                                });
                            });
                        } catch (e) {
                            sessionStorage.removeItem(CHAVE);
                        }
                    }

                    let timer;
                    this.$el.addEventListener('input', (e) => {
                        if (e.target.type === 'file') return;
                        clearTimeout(timer);
                        timer = setTimeout(() => this.salvarRascunho(), 800);
                    });
                },

                salvarRascunho() {
                    const campos = {};
                    const inputs = this.$el.querySelectorAll('input:not([type=file]):not([type=hidden]):not([type=password]), select, textarea');
                    inputs.forEach(el => {
                        if (el.name && el.name !== '_token') campos[el.name] = el.value;
                    });
                    sessionStorage.setItem(CHAVE, JSON.stringify(campos));
                },

                limparRascunho() {
                    sessionStorage.removeItem(CHAVE);
                },

                descartarRascunho() {
                    sessionStorage.removeItem(CHAVE);
                    this.temRascunho = false;
                    const inputs = this.$el.querySelectorAll('input:not([type=hidden]), select, textarea');
                    inputs.forEach(el => { if (el.type !== 'file') el.value = ''; });
                }
            };
        }
    </script>
</x-app-layout>
