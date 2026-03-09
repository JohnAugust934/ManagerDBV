<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 1. Gate MASTER (Acesso total e gestão de usuários)
        Gate::define('master', function (User $user) {
            return $user->role === 'master';
        });

        // 2. Gates de Módulos (Chama a função do Model)
        Gate::define('financeiro', fn (User $user) => $user->temPermissao('financeiro'));
        Gate::define('secretaria', fn (User $user) => $user->temPermissao('secretaria'));
        Gate::define('unidades', fn (User $user) => $user->temPermissao('unidades'));
        Gate::define('pedagogico', fn (User $user) => $user->temPermissao('pedagogico'));
        Gate::define('eventos', fn (User $user) => $user->temPermissao('eventos'));
        Gate::define('relatorios', fn (User $user) => $user->temPermissao('relatorios')); // <-- GATE DO RELATÓRIO ADICIONADO AQUI

        // 3. Gate Especial: Minha Unidade (Para Conselheiros)
        // Permite se for master/diretor/secretario OU se for o conselheiro da unidade especifica
        Gate::define('gerir-unidade', function (User $user, $unidade = null) {
            if ($user->temPermissao('unidades')) {
                return true;
            }

            // Se for conselheiro, verifica se o nome dele bate com o da unidade
            if ($unidade && ($user->role === 'conselheiro' || $user->role === 'instrutor')) {
                return $unidade->conselheiro === $user->name;
            }

            return false;
        });
    }
}
