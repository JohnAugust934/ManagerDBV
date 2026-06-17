<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterOnlySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando população mínima do banco de dados...');

        // ---------------------------------------------------------
        // 0. SEEDERS DE BASE (CATÁLOGO GLOBAL)
        // ---------------------------------------------------------
        $this->call([
            ClassesSeeder::class,        // Classes Regulares/Avançadas (global)
            EspecialidadesSeeder::class, // ~470 Especialidades (global)
        ]);

        // ---------------------------------------------------------
        // 1. SUPER ADMIN DE PLATAFORMA (cross-tenant, sem clube)
        // ---------------------------------------------------------
        User::firstOrCreate(['email' => 'admin@plataforma.com'], [
            'name' => 'Platform Admin',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_master' => true,
            'is_platform_admin' => true,
            'club_id' => null,
        ]);

        $this->command->info('🛡️  Platform Admin criado: admin@plataforma.com / password');

        // ---------------------------------------------------------
        // 2. CLUBE BASE + MASTER DO CLUBE (tenant inicial)
        // ---------------------------------------------------------
        $clube = Club::firstOrCreate(['nome' => 'Clube Desbravadores Exemplo'], [
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Exemplo',
        ]);

        User::firstOrCreate(['email' => 'master@clube.com'], [
            'name' => 'Master do Clube',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_master' => true,
            'is_platform_admin' => false,
            'club_id' => $clube->id,
        ]);

        $this->command->info('🏢 Clube base criado com master: master@clube.com / password');

        // ---------------------------------------------------------
        // 3. DADOS MÍNIMOS DO CLUBE BASE (1 unidade, 2 desbravadores)
        // ---------------------------------------------------------
        $unidade = Unidade::firstOrCreate(
            ['nome' => 'Unidade Exemplo', 'club_id' => $clube->id],
            ['grito_guerra' => 'Sempre alerta!', 'conselheiro' => 'Conselheiro Exemplo']
        );

        foreach (['Desbravador Exemplo 1', 'Desbravador Exemplo 2'] as $nome) {
            Desbravador::firstOrCreate(
                ['nome' => $nome, 'unidade_id' => $unidade->id],
                [
                    'ativo' => true,
                    'data_nascimento' => now()->subYears(12),
                    'sexo' => 'M',
                ]
            );
        }

        $this->command->info('🧒 Dados mínimos do clube base criados (1 unidade, 2 desbravadores).');
    }
}
