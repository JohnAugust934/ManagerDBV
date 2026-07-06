<?php

namespace Database\Seeders;

use App\Models\Ata;
use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Clube de teste persistente para validações manuais em staging/produção.
 *
 *   php artisan db:seed --class=TestClubSeeder
 *
 * Idempotente (firstOrCreate). Não cria platform admin — use o MasterOnlySeeder.
 */
class TestClubSeeder extends Seeder
{
    public function run(): void
    {
        $club = Club::firstOrCreate(['nome' => 'Clube Desbravadores Beta'], [
            'cidade' => 'Florianópolis',
            'associacao' => 'Associação Catarinense',
        ]);

        $this->command?->info("🧪 Semeando clube de teste: {$club->nome}");

        // Equipe administrativa
        $equipe = [
            ['name' => 'Master Beta', 'email' => 'master.beta@clube.com', 'role' => 'master', 'is_master' => true],
            ['name' => 'Diretor Beta', 'email' => 'diretor.beta@clube.com', 'role' => 'diretor', 'is_master' => false],
            ['name' => 'Secretário Beta', 'email' => 'secretaria.beta@clube.com', 'role' => 'secretario', 'is_master' => false],
            ['name' => 'Tesoureiro Beta', 'email' => 'tesoureiro.beta@clube.com', 'role' => 'tesoureiro', 'is_master' => false],
            ['name' => 'Conselheiro Beta', 'email' => 'conselheiro.beta@clube.com', 'role' => 'conselheiro', 'is_master' => false],
        ];

        foreach ($equipe as $membro) {
            // forceFill: role/is_master/club_id são campos de privilégio (fora de $fillable).
            $user = User::firstOrNew(['email' => $membro['email']]);
            if (! $user->exists) {
                $user->forceFill([
                    'name' => $membro['name'],
                    'password' => Hash::make('password'),
                    'role' => $membro['role'],
                    'is_master' => $membro['is_master'],
                    'is_platform_admin' => false,
                    'club_id' => $club->id,
                ])->save();
            }
        }

        // 3 unidades
        $unidades = collect(['Panteras', 'Águias', 'Raposas'])->map(function (string $nome) use ($club) {
            return Unidade::firstOrCreate(
                ['nome' => $nome, 'club_id' => $club->id],
                ['grito_guerra' => "Avante, {$nome}!", 'conselheiro' => "Conselheiro {$nome}"]
            );
        });

        // 15 desbravadores distribuídos pelas unidades
        $desbravadores = collect();
        for ($i = 1; $i <= 15; $i++) {
            $unidade = $unidades[($i - 1) % $unidades->count()];

            $desbravadores->push(Desbravador::firstOrCreate(
                ['nome' => "Desbravador Beta {$i}", 'unidade_id' => $unidade->id],
                [
                    'ativo' => true,
                    'data_nascimento' => now()->subYears(10 + ($i % 5)),
                    'sexo' => $i % 2 ? 'M' : 'F',
                ]
            ));
        }

        // Financeiro mínimo: caixa + mensalidades do mês atual
        Caixa::firstOrCreate(
            ['descricao' => 'Saldo inicial Beta', 'club_id' => $club->id],
            ['tipo' => 'entrada', 'categoria' => 'Abertura', 'valor' => 1000.00, 'data_movimentacao' => now()]
        );

        foreach ($desbravadores as $dbv) {
            Mensalidade::firstOrCreate(
                ['desbravador_id' => $dbv->id, 'mes' => now()->month, 'ano' => now()->year],
                ['valor' => 20.00, 'status' => 'pendente']
            );
        }

        // 1 evento
        Evento::firstOrCreate(
            ['nome' => 'Acampamento Beta', 'club_id' => $club->id],
            [
                'local' => 'Sítio Beta',
                'valor' => 50.00,
                'data_inicio' => now()->addWeek(),
                'data_fim' => now()->addWeek()->addDays(2),
                'descricao' => 'Evento de teste do clube Beta.',
            ]
        );

        // 1 ata
        Ata::firstOrCreate(
            ['titulo' => 'Ata inaugural Beta', 'club_id' => $club->id],
            [
                'data_reuniao' => now(),
                'tipo' => 'Regular',
                'hora_inicio' => '09:00',
                'hora_fim' => '11:00',
                'local' => 'Sede Beta',
                'secretario_responsavel' => 'Secretário Beta',
                'participantes' => 'Diretoria Beta',
                'conteudo' => 'Reunião inaugural do clube de teste.',
            ]
        );

        $this->command?->info('✅ Clube de teste Beta pronto (5 usuários, 3 unidades, 15 desbravadores).');
    }
}
