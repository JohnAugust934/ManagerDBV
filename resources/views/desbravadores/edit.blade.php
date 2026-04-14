<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('desbravadores.show', $desbravador) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 dark:text-gray-100 leading-tight">
                Editar Desbravador
            </h2>
        </div>
    </x-slot>

    <div class="ui-page max-w-5xl space-y-8 ui-animate-fade-up">

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

            <form action="{{ route('desbravadores.update', $desbravador) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                {{-- STATUS NO CLUBE --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-slate-50 dark:bg-slate-800/30 p-6 rounded-3xl border border-slate-100 dark:border-slate-700/60 gap-4">
                    <div class="flex-1">
                        <label class="text-[14px] font-black uppercase tracking-widest text-[#002F6C] dark:text-blue-400 mb-1">Status no Clube</label>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Desative esta opção se o membro saiu, foi transferido ou não frequenta mais o clube.</p>
                    </div>
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer group">
                            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $desbravador->ativo) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-14 h-7 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-slate-600 peer-checked:bg-emerald-500 transition-colors shadow-inner"></div>
                            <span class="ml-4 text-[15px] font-black text-slate-700 dark:text-slate-300 group-hover:text-[#002F6C] transition-colors">Ativo</span>
                        </label>
                    </div>
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
                            <input id="nome" name="nome" type="text" class="ui-input" value="{{ old('nome', $desbravador->nome) }}" required />
                        </div>

                        <div>
                            <label for="data_nascimento" class="ui-input-label">Data de Nascimento *</label>
                            <input id="data_nascimento" name="data_nascimento" type="date" class="ui-input" value="{{ old('data_nascimento', $desbravador->data_nascimento?->format('Y-m-d')) }}" required />
                        </div>

                        <div>
                            <label for="sexo" class="ui-input-label">Sexo *</label>
                            <div class="relative">
                                <select id="sexo" name="sexo" class="ui-input appearance-none pr-10" required>
                                    <option value="M" {{ old('sexo', $desbravador->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo', $desbravador->sexo) == 'F' ? 'selected' : '' }}>Feminino</option>
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
                                        <option value="{{ $unidade->id }}" {{ old('unidade_id', $desbravador->unidade_id) == $unidade->id ? 'selected' : '' }}>
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
                                        <option value="{{ $classe->id }}" {{ old('classe_atual', $desbravador->classe_atual) == $classe->id ? 'selected' : '' }}>
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
                            <input id="cpf" name="cpf" type="text" class="ui-input font-mono" value="{{ old('cpf', $desbravador->cpf) }}" required />
                        </div>
                        <div>
                            <label for="rg" class="ui-input-label">RG</label>
                            <input id="rg" name="rg" type="text" class="ui-input font-mono" value="{{ old('rg', $desbravador->rg) }}" />
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
                            <input id="email" name="email" type="email" class="ui-input" value="{{ old('email', $desbravador->email) }}" required />
                        </div>
                        <div>
                            <label for="telefone" class="ui-input-label">Telefone (Celular)</label>
                            <input id="telefone" name="telefone" type="text" class="ui-input" value="{{ old('telefone', $desbravador->telefone) }}" />
                        </div>
                        <div class="md:col-span-2">
                            <label for="endereco" class="ui-input-label">Endereço Completo *</label>
                            <input id="endereco" name="endereco" type="text" class="ui-input" value="{{ old('endereco', $desbravador->endereco) }}" required />
                        </div>
                        <div>
                            <label for="nome_responsavel" class="ui-input-label">Nome do Responsável *</label>
                            <input id="nome_responsavel" name="nome_responsavel" type="text" class="ui-input" value="{{ old('nome_responsavel', $desbravador->nome_responsavel) }}" required />
                        </div>
                        <div>
                            <label for="telefone_responsavel" class="ui-input-label">Telefone do Responsável *</label>
                            <input id="telefone_responsavel" name="telefone_responsavel" type="text" class="ui-input" value="{{ old('telefone_responsavel', $desbravador->telefone_responsavel) }}" required />
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
                            <input id="numero_sus" name="numero_sus" type="text" class="ui-input font-mono" value="{{ old('numero_sus', $desbravador->numero_sus) }}" required />
                        </div>
                        <div class="md:col-span-1">
                            <label for="tipo_sanguineo" class="ui-input-label">Tipo Sang.</label>
                            <div class="relative">
                                <select id="tipo_sanguineo" name="tipo_sanguineo" class="ui-input appearance-none pr-10 focus:ring-red-500 focus:border-red-500 text-sm">
                                    <option value="">Não sei</option>
                                    @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $tipo)
                                        <option value="{{ $tipo }}" {{ old('tipo_sanguineo', $desbravador->tipo_sanguineo) == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label for="plano_saude" class="ui-input-label">Plano de Saúde</label>
                            <input id="plano_saude" name="plano_saude" type="text" class="ui-input" value="{{ old('plano_saude', $desbravador->plano_saude) }}" />
                        </div>
                        <div class="md:col-span-6 border-t border-red-200/50 dark:border-red-800/30 pt-6">
                            <label for="alergias" class="ui-input-label">Alergias (Se houver)</label>
                            <input id="alergias" name="alergias" type="text" class="ui-input" value="{{ old('alergias', $desbravador->alergias) }}" />
                        </div>
                        <div class="md:col-span-6">
                            <label for="medicamentos_continuos" class="ui-input-label">Medicamentos de Uso Contínuo</label>
                            <input id="medicamentos_continuos" name="medicamentos_continuos" type="text" class="ui-input" value="{{ old('medicamentos_continuos', $desbravador->medicamentos_continuos) }}" />
                        </div>
                    </div>
                </div>

                {{-- SUBMIT E DANGER ZONE --}}
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-8 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex-1 w-full md:w-auto">
                        <button type="button" class="ui-btn-danger w-full sm:w-auto px-6 text-sm" onclick="if(confirm('Tem certeza? A exclusão apaga definitivamente.\nO mais seguro é inativar.')) document.getElementById('delete-form').submit()">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Excluir Registro
                        </button>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row w-full md:w-auto gap-4">
                        <a href="{{ route('desbravadores.show', $desbravador) }}" class="ui-btn-secondary px-8 text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="ui-btn-primary px-8 text-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-form" action="{{ route('desbravadores.destroy', $desbravador) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>
</x-app-layout>
