<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterOnlySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Iniciando população mínima do banco de dados (multi-tenant)...');

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
        // No modelo multi-tenant, a instalação de produção começa apenas
        // com o admin da plataforma. Os clubes (e seus respectivos usuários
        // master) passam a ser criados pelo painel da plataforma, e não
        // mais semeados aqui.
        // forceFill: role/is_platform_admin/club_id são campos de privilégio (fora de
        // $fillable). firstOrNew + save preserva a idempotência do firstOrCreate.
        $adminPlataforma = User::firstOrNew(['email' => 'admin@plataforma.com']);
        if (! $adminPlataforma->exists) {
            $adminPlataforma->forceFill([
                'name' => 'Administrador da Plataforma',
                'password' => Hash::make('password'),
                'role' => 'platform_admin',
                'is_master' => false,
                'is_platform_admin' => true,
                'club_id' => null,
            ])->save();
        }

        $this->command->info('🛡️  Admin da Plataforma criado: admin@plataforma.com / password');
        $this->command->warn('⚠️  Altere a senha do admin da plataforma imediatamente após o primeiro acesso.');
    }
}
