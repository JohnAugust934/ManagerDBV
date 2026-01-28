<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cria o Administrador Master (Você)
        // Esse usuário NÃO tem clube, ele serve apenas para gerenciar o sistema/convites
        User::create([
            'name' => 'Master Admin',
            'email' => 'admin@desbravadores.com',
            'password' => Hash::make('jkd123sn'), // Senha padrão
            'is_master' => true,
            'club_id' => null,
        ]);
    }
}
