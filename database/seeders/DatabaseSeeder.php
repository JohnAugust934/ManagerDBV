<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Club;
use App\Models\Unidade;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Caixa;
use App\Models\Patrimonio;
use App\Models\Ata;
use App\Models\Ato;
use App\Models\Mensalidade;
use App\Models\Frequencia;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Limpando banco e iniciando população...');

        // 1. Clube e Usuários
        $clube = Club::create([
            'nome' => 'Clube Orion',
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Paulista Leste',
            'logo' => null,
        ]);

        User::create([
            'name' => 'Master Admin',
            'email' => 'admin@desbravadores.com',
            'password' => Hash::make('password'),
            'is_master' => true,
            'club_id' => null,
        ]);

        $diretor = User::create([
            'name' => 'Diretor Silva',
            'email' => 'diretor@clube.com',
            'password' => Hash::make('password'),
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        // 2. Unidades
        $nomesUnidades = ['Águias', 'Leões', 'Escorpiões', 'Falcões'];
        $unidades = collect();

        foreach ($nomesUnidades as $nome) {
            $unidades->push(Unidade::create([
                'nome' => $nome,
                'grito_guerra' => 'Força e honra, somos ' . $nome . '!',
                'conselheiro' => 'Conselheiro ' . fake()->firstName,
            ]));
        }
        $this->command->info('Unidades criadas.');

        // 3. Especialidades
        $nomesEspecialidades = [
            'Nós e Amarras',
            'Primeiros Socorros',
            'Acampamento I',
            'Acampamento II',
            'Culinária',
            'Fogueiras e Cozinha',
            'Répteis',
            'Anfíbios',
            'Astronomia',
            'Arte de Acampar',
            'Pioneiria',
            'Excursionismo',
            'Natação Principiante',
            'Ordem Unida',
            'Civismo'
        ];

        $especialidades = collect();
        foreach ($nomesEspecialidades as $nome) {
            $especialidades->push(Especialidade::create([
                'nome' => $nome,
                'area' => fake()->randomElement(['ADRA', 'Artes', 'Natureza', 'Atividades Recreativas']),
                'cor_fundo' => fake()->hexColor(),
            ]));
        }
        $this->command->info('Especialidades criadas.');

        // 4. Desbravadores (ATUALIZADO COM DADOS DE PRONTUÁRIO)
        $desbravadores = collect();
        foreach ($unidades as $unidade) {
            for ($i = 0; $i < 8; $i++) {
                $dbv = Desbravador::create([
                    'ativo' => true,
                    'nome' => fake()->name(),
                    'data_nascimento' => fake()->dateTimeBetween('-15 years', '-10 years'),
                    'sexo' => fake()->randomElement(['M', 'F']),
                    'unidade_id' => $unidade->id,
                    'classe_atual' => fake()->randomElement(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro', 'Excursionista', 'Guia']),

                    // Novos Campos de Prontuário
                    'email' => fake()->safeEmail(),
                    'telefone' => fake()->phoneNumber(),
                    'endereco' => fake()->address(),
                    'nome_responsavel' => fake()->name(),
                    'telefone_responsavel' => fake()->phoneNumber(),
                    'tipo_sanguineo' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                    'alergias' => fake()->boolean(20) ? fake()->word() : null,
                    'medicamentos_continuos' => fake()->boolean(10) ? 'Insulina' : null,
                    'plano_saude' => fake()->boolean(60) ? 'Unimed' : null,
                ]);

                $dbv->especialidades()->attach($especialidades->random(rand(2, 3))->pluck('id'), [
                    'data_conclusao' => now()->subDays(rand(1, 300))
                ]);

                $desbravadores->push($dbv);
            }
        }
        $this->command->info('32 Desbravadores criados com Prontuário.');

        // 5. Financeiro
        for ($i = 0; $i < 20; $i++) {
            $tipo = fake()->randomElement(['entrada', 'saida']);
            Caixa::create([
                'descricao' => $tipo == 'entrada' ? 'Venda de Pizza' : 'Compra de Material',
                'tipo' => $tipo,
                'valor' => fake()->randomFloat(2, 50, 500),
                'data_movimentacao' => fake()->dateTimeBetween('-2 months', 'now'),
            ]);
        }

        $dataAtual = now();
        foreach ($desbravadores as $dbv) {
            $status = fake()->boolean(60) ? 'pago' : 'pendente';
            Mensalidade::create([
                'desbravador_id' => $dbv->id,
                'mes' => $dataAtual->month,
                'ano' => $dataAtual->year,
                'valor' => 15.00,
                'status' => $status,
                'data_pagamento' => $status === 'pago' ? now() : null,
            ]);
        }
        $this->command->info('Financeiro populado.');

        // 6. Patrimônio
        $itensPatrimonio = ['Barraca Canadense', 'Barraca Iglu', 'Lona 4x4', 'Bandeira', 'Mastro', 'Caixa de Som', 'Kit Primeiros Socorros', 'Machadinha', 'Facão', 'Lampião', 'Fogareiro', 'Panela', 'Cordas', 'Bússola'];

        foreach ($itensPatrimonio as $itemNome) {
            Patrimonio::create([
                'item' => $itemNome,
                'quantidade' => rand(1, 3),
                'valor_estimado' => fake()->randomFloat(2, 50, 1000),
                'data_aquisicao' => fake()->date(),
                'estado_conservacao' => fake()->randomElement(['Novo', 'Bom', 'Regular', 'Ruim']),
                'local_armazenamento' => fake()->randomElement(['Almoxarifado', 'Armário A', 'Sede']),
                'observacoes' => 'Código Patrimonial: PAT-' . rand(1000, 9999),
            ]);
        }
        $this->command->info('Patrimônio populado.');

        // 7. Secretaria
        for ($i = 0; $i < 15; $i++) {
            Ata::create([
                'tipo' => 'Regular',
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),
                'secretario_responsavel' => 'Secretário ' . fake()->firstName,
                'participantes' => 'Diretoria completa.',
                'conteudo' => fake()->paragraphs(3, true),
            ]);
        }
        for ($i = 0; $i < 5; $i++) {
            Ato::create([
                'tipo' => 'Nomeação',
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao_resumida' => 'Nomeação de Cargo #' . ($i + 1),
                'texto_completo' => 'Nomeação oficial para cargo.',
                'desbravador_id' => $desbravadores->random()->id,
            ]);
        }
        $this->command->info('Secretaria populada.');

        // 8. Frequência
        $datas = [
            Carbon::now()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeeks(2)->startOfWeek(Carbon::SUNDAY),
        ];

        foreach ($datas as $data) {
            foreach ($desbravadores as $dbv) {
                $chance = ($dbv->unidade_id == $unidades->first()->id) ? 90 : 60;
                Frequencia::create([
                    'desbravador_id' => $dbv->id,
                    'data' => $data,
                    'presente' => fake()->boolean($chance + 5),
                    'pontual' => fake()->boolean($chance),
                    'biblia' => fake()->boolean($chance - 10),
                    'uniforme' => fake()->boolean($chance),
                ]);
            }
        }
        $this->command->info('Ranking gerado. SUCESSO! 🚀');
    }
}
