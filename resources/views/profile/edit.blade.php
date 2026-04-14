<x-app-layout>
    <x-slot name="header">
        Configurações do Perfil
    </x-slot>

    <div class="ui-page max-w-4xl min-h-full space-y-8">
        {{-- Profile Header info --}}
        <div class="ui-card p-6 sm:p-8 bg-gradient-to-r from-[#002F6C] to-blue-800 text-white border-0 shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0wIDBoMjB2MjBIMGZpbGw9Im5vbmUiLz4KPHBhdGggZD0iTTAgMjBoMjBNMjAgMHYyMCIgc3Ryb2tlPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMSkiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIvPgo8L3N2Zz4=')] opacity-30"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <div class="w-24 h-24 rounded-2xl bg-white text-[#002F6C] flex items-center justify-center text-4xl font-black shadow-xl shrink-0">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="text-center md:text-left">
                    <h2 class="text-3xl font-black tracking-tight mb-1">{{ Auth::user()->name }}</h2>
                    <p class="text-blue-200 font-medium tracking-wide">{{ Auth::user()->email }}</p>
                    <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-2">
                        <span class="ui-badge bg-blue-900/50 border border-blue-500/30 text-blue-100">{{ ucfirst(Auth::user()->role) }}</span>
                        @if(Auth::user()->club)
                            <span class="ui-badge bg-amber-500/20 border border-amber-500/30 text-amber-200">{{ Auth::user()->club->nome }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8">
            <div class="ui-card p-6 sm:p-8 hover:-translate-y-1 transition-transform shadow-sm hover:shadow-md border border-slate-100 dark:border-slate-800">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="ui-card p-6 sm:p-8 hover:-translate-y-1 transition-transform shadow-sm hover:shadow-md border border-slate-100 dark:border-slate-800">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="ui-card p-6 sm:p-8 bg-red-50/50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 hover:-translate-y-1 transition-transform shadow-sm">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
