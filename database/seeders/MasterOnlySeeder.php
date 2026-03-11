<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterOnlySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando população do banco de dados...');

        // ---------------------------------------------------------
        // 0. SEEDERS DE BASE (TABELAS DE APOIO)
        // ---------------------------------------------------------
        $this->call([
            ClassesSeeder::class,        // Popula as Classes Regulares/Avançadas
            EspecialidadesSeeder::class, // Popula as ~470 Especialidades
        ]);

        User::create([
            'name' => 'Master Admin',
            'email' => 'admin@master.com',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_master' => true,
            'club_id' => null,
        ]);

        $this->command->info('✅ Usuário Master criado: admin@master.com / password');
    }
}
