<x-app-layout>
    <x-slot name="header">Configurações do Clube</x-slot>

    <div class="ui-page space-y-6 max-w-5xl mx-auto ui-animate-fade-up">

        {{-- Header Navigation --}}
        <div class="flex items-center justify-between mb-2">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Configurações do Clube</h1>
                <p class="text-slate-500 font-medium mt-1">Identidade e dados cadastrais da agremiação.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('club.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                {{-- Identidade Visual --}}
                <div class="xl:col-span-1">
                    <div class="ui-card p-6 sm:p-8 sticky top-24">
                        <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Brasão Oficial</h3>
                        </div>

                        <div class="flex flex-col items-center text-center gap-6">
                            @if ($club && $club->logo)
                                <div class="relative group w-full">
                                    <div class="absolute inset-0 bg-gradient-to-tr from-slate-100 to-white dark:from-slate-800 dark:to-slate-700/50 rounded-2xl shadow-inner border border-slate-200 dark:border-slate-700 -z-10"></div>
                                    <img src="{{ asset('storage/' . $club->logo) }}" alt="Brasão do Clube" class="h-48 w-full object-contain p-4 drop-shadow-xl transition-transform group-hover:scale-105 duration-300">
                                </div>
                                <button type="button" onclick="if(confirm('Tem certeza que deseja remover o brasão do clube?')) document.getElementById('form-remove-logo').submit();" class="w-full px-4 py-3 rounded-xl bg-red-50 text-red-600 font-bold hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20 transition text-sm flex justify-center items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remover Brasão
                                </button>
                            @else
                                <div class="w-full h-48 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 flex flex-col items-center justify-center gap-3">
                                    <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Nenhum brasão</span>
                                </div>
                            @endif

                            <div class="w-full text-left">
                                <label for="logo" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Enviar Novo Brasão</label>
                                <input id="logo" name="logo" type="file" accept="image/png, image/jpeg, image/jpg" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-black file:bg-[#002F6C]/10 file:text-[#002F6C] hover:file:bg-[#002F6C]/20 dark:file:bg-blue-500/20 dark:file:text-blue-400 dark:hover:file:bg-blue-500/30 cursor-pointer transition">
                                <p class="text-[10px] font-bold text-slate-400 mt-2">Formatos permitidos: PNG, JPG. Máx 2MB.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dados Cadastrais --}}
                <div class="xl:col-span-2 space-y-6">
                    <div class="ui-card p-6 sm:p-8">
                        <div class="flex items-center gap-3 mb-6 border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white uppercase tracking-tight">Associação e Igreja</h3>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="nome" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nome do Clube <span class="text-red-500">*</span></label>
                                <input id="nome" name="nome" type="text" value="{{ old('nome', $club?->nome) }}" placeholder="Ex: Pioneiros da Colina" required autofocus class="ui-input w-full font-bold text-xl h-14">
                                <x-input-error class="mt-2" :messages="$errors->get('nome')" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="cidade" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Cidade - Estado <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <input id="cidade" name="cidade" type="text" value="{{ old('cidade', $club?->cidade) }}" placeholder="Ex: São Paulo - SP" required class="ui-input w-full pl-11 font-bold">
                                    </div>
                                    <x-input-error class="mt-2" :messages="$errors->get('cidade')" />
                                </div>

                                <div>
                                    <label for="associacao" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Associação / Campo <span class="text-red-500">*</span></label>
                                    <input id="associacao" name="associacao" type="text" value="{{ old('associacao', $club?->associacao) }}" placeholder="Ex: APL, APaC..." required class="ui-input w-full font-bold uppercase">
                                    <x-input-error class="mt-2" :messages="$errors->get('associacao')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
                        @if (session('success'))
                            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 font-bold text-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        <button type="submit" class="ui-btn-primary w-full sm:w-auto h-[52px] flex justify-center items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Salvar Alterações
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if ($club && $club->logo)
        <form id="form-remove-logo" method="POST" action="{{ route('club.remove_logo') }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
        @endif
    </div>
</x-app-layout>
