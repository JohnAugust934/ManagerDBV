<?php

namespace Database\Seeders;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\Caixa;
use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\Unidade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
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

        // Carrega dados em memória para otimizar os loops abaixo
        $classesCache = Classe::all();
        $especialidadesCache = Especialidade::all(); // Pega as especialidades criadas pelo seeder

        // ---------------------------------------------------------
        // 1. CLUBE
        // ---------------------------------------------------------
        $clube = Club::firstOrCreate([
            'nome' => 'Clube de Desbravadores Orion',
        ], [
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Paulista Leste',
            'logo' => null,
        ]);

        $this->command->info("🏢 Clube '{$clube->nome}' verificado.");

        // ---------------------------------------------------------
        // 2. USUÁRIOS DO SISTEMA (POR CARGO)
        // ---------------------------------------------------------
        $cargos = [
            ['name' => 'Administrador Master', 'email' => 'admin@clube.com', 'role' => 'master', 'club_id' => null, 'is_master' => true],
            ['name' => 'Diretor Silva', 'email' => 'diretor@clube.com', 'role' => 'diretor', 'club_id' => $clube->id, 'is_master' => false],
            ['name' => 'Secretária Ana', 'email' => 'secretaria@clube.com', 'role' => 'secretario', 'club_id' => $clube->id, 'is_master' => false],
            ['name' => 'Tesoureiro Carlos', 'email' => 'tesoureiro@clube.com', 'role' => 'tesoureiro', 'club_id' => $clube->id, 'is_master' => false],
            ['name' => 'Instrutor Marcos', 'email' => 'instrutor@clube.com', 'role' => 'instrutor', 'club_id' => $clube->id, 'is_master' => false],
        ];

        foreach ($cargos as $cargo) {
            User::firstOrCreate(['email' => $cargo['email']], [
                'name' => $cargo['name'],
                'password' => Hash::make('password'),
                'role' => $cargo['role'],
                'club_id' => $cargo['club_id'],
                'is_master' => $cargo['is_master'],
            ]);
        }

        $this->command->info('👥 Equipe administrativa criada.');

        // ---------------------------------------------------------
        // 3. UNIDADES & CONSELHEIROS
        // ---------------------------------------------------------
        $unidades = collect();
        $dadosUnidades = [
            ['nome' => 'Águias', 'grito' => 'Voando alto, sempre avante!', 'conselheiro' => 'Conselheiro Pedro', 'email' => 'pedro@clube.com'],
            ['nome' => 'Leões', 'grito' => 'Força e coragem, somos Leões!', 'conselheiro' => 'Conselheiro João', 'email' => 'joao@clube.com'],
            ['nome' => 'Escorpiões', 'grito' => 'Pequenos no tamanho, gigantes na bravura!', 'conselheiro' => 'Conselheiro Lucas', 'email' => 'lucas@clube.com'],
            ['nome' => 'Falcões', 'grito' => 'Velocidade e precisão, Falcões em ação!', 'conselheiro' => 'Conselheira Maria', 'email' => 'maria@clube.com'],
        ];

        foreach ($dadosUnidades as $dado) {
            // Cria a Unidade
            $unidade = Unidade::firstOrCreate(['nome' => $dado['nome']], [
                'grito_guerra' => $dado['grito'],
                'conselheiro' => $dado['conselheiro'],
                'club_id' => $clube->id, // Garante vínculo com o clube
            ]);
            $unidades->push($unidade);

            // Cria o Usuário Conselheiro
            User::firstOrCreate(['email' => $dado['email']], [
                'name' => $dado['conselheiro'],
                'password' => Hash::make('password'),
                'role' => 'conselheiro',
                'is_master' => false,
                'club_id' => $clube->id,
            ]);
        }
        $this->command->info('⛺ Unidades e Conselheiros criados.');

        // ---------------------------------------------------------
        // 4. DESBRAVADORES
        // ---------------------------------------------------------
        $desbravadores = collect();
        $diretor = User::where('role', 'diretor')->first();

        // Garante que temos especialidades para vincular
        if ($especialidadesCache->isEmpty()) {
            $this->command->warn('⚠️ Nenhuma especialidade encontrada. Rode o EspecialidadesSeeder primeiro.');
        }

        foreach ($unidades as $unidade) {
            // Cria entre 6 a 8 desbravadores por unidade
            for ($i = 0; $i < rand(6, 8); $i++) {
                $sexo = fake()->randomElement(['M', 'F']);

                // Sorteia uma classe e pega o objeto real do banco
                $classeSorteada = $classesCache->where('nome', fake()->randomElement(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro']))->first();

                $dbv = Desbravador::create([
                    'ativo' => true,
                    'nome' => fake()->name($sexo == 'M' ? 'male' : 'female'),
                    'data_nascimento' => fake()->dateTimeBetween('-15 years', '-10 years'),
                    'sexo' => $sexo,
                    'unidade_id' => $unidade->id,
                    'classe_atual' => $classeSorteada ? $classeSorteada->id : null, // ID correto
                    'email' => fake()->unique()->safeEmail(),
                    'telefone' => fake()->phoneNumber(),
                    'endereco' => fake()->address(),
                    'nome_responsavel' => fake()->name(),
                    'telefone_responsavel' => fake()->phoneNumber(),
                    'numero_sus' => fake()->numerify('### #### #### ####'),
                    'tipo_sanguineo' => fake()->randomElement(['A+', 'A-', 'B+', 'O+', 'O-']),
                    'alergias' => fake()->boolean(20) ? fake()->randomElement(['Amendoim', 'Dipirona', 'Picada de Inseto']) : null,
                    'medicamentos_continuos' => fake()->boolean(10) ? 'Insulina' : null,
                    'plano_saude' => fake()->boolean(40) ? 'Unimed' : null,
                ]);

                // Vincula Especialidades Aleatórias (se houver)
                if ($especialidadesCache->isNotEmpty()) {
                    $dbv->especialidades()->attach(
                        $especialidadesCache->random(rand(1, 3))->pluck('id'),
                        ['data_conclusao' => fake()->dateTimeBetween('-2 years', 'now')]
                    );
                }

                // Simula Progresso na Classe (Requisitos Cumpridos)
                if ($classeSorteada && $classeSorteada->requisitos->count() > 0) {
                    $reqs = $classeSorteada->requisitos->random(min(3, $classeSorteada->requisitos->count()));
                    foreach ($reqs as $req) {
                        $dbv->requisitosCumpridos()->attach($req->id, [
                            'user_id' => $diretor->id ?? 1, // Assinado pelo diretor
                            'data_conclusao' => now()->subDays(rand(1, 60)),
                        ]);
                    }
                }
                $desbravadores->push($dbv);
            }
        }
        $this->command->info("🧒 {$desbravadores->count()} Desbravadores criados com histórico.");

        // ---------------------------------------------------------
        // 5. EVENTOS
        // ---------------------------------------------------------
        $listaEventos = [
            ['nome' => 'Acampamento de Instrução', 'local' => 'Chácara Oliveira', 'valor' => 120.00, 'inicio' => '-2 months', 'fim' => '-2 months + 2 days'],
            ['nome' => 'Caminhada Noturna', 'local' => 'Trilha do Morro', 'valor' => 0.00, 'inicio' => '-1 month', 'fim' => '-1 month'],
            ['nome' => 'IV Campori da APL', 'local' => 'Parque do Peão - Barretos', 'valor' => 280.00, 'inicio' => '+1 month', 'fim' => '+1 month + 4 days'],
            ['nome' => 'Investidura de Classes', 'local' => 'Igreja Central', 'valor' => 15.00, 'inicio' => '+2 months', 'fim' => '+2 months'],
            ['nome' => 'Dia Mundial dos Desbravadores', 'local' => 'Ginásio de Esportes', 'valor' => 0.00, 'inicio' => '+5 months', 'fim' => '+5 months'],
        ];

        foreach ($listaEventos as $evt) {
            $evento = Evento::create([
                'nome' => $evt['nome'],
                'local' => $evt['local'],
                'valor' => $evt['valor'],
                'data_inicio' => date('Y-m-d H:i:s', strtotime($evt['inicio'])),
                'data_fim' => date('Y-m-d H:i:s', strtotime($evt['fim'])),
                'descricao' => 'Evento oficial do calendário anual.',
            ]);

            // Inscreve alguns desbravadores aleatoriamente
            foreach ($desbravadores as $dbv) {
                // Eventos passados têm mais inscritos
                $chance = (strtotime($evt['inicio']) < time()) ? 80 : 40;

                if (fake()->boolean($chance)) {
                    $pago = ($evento->valor == 0) || fake()->boolean(60);
                    $evento->desbravadores()->attach($dbv->id, [
                        'pago' => $pago,
                        'autorizacao_entregue' => fake()->boolean(70),
                    ]);
                }
            }
        }
        $this->command->info('📅 Calendário de Eventos populado.');

        // ---------------------------------------------------------
        // 6. FINANCEIRO (CAIXA E MENSALIDADES)
        // ---------------------------------------------------------

        // Caixa: Movimentações Avulsas
        for ($i = 0; $i < 30; $i++) {
            $tipo = fake()->randomElement(['entrada', 'saida']);
            Caixa::create([
                'descricao' => $tipo == 'entrada'
                    ? fake()->randomElement(['Doação', 'Venda de Pizza', 'Cantina', 'Oferta Especial'])
                    : fake()->randomElement(['Material de Secretaria', 'Gás', 'Manutenção Barracas', 'Lanche']),
                'tipo' => $tipo,
                'categoria' => $tipo == 'entrada' ? 'Receitas Diversas' : 'Despesas Operacionais',
                'valor' => fake()->randomFloat(2, 20, 300),
                'data_movimentacao' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // Mensalidades: Gera para os últimos 3 meses
        $meses = [
            now()->startOfMonth()->subMonths(2),
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth(),
        ];

        foreach ($meses as $data) {
            foreach ($desbravadores as $dbv) {
                $status = fake()->boolean(70) ? 'pago' : 'pendente';
                Mensalidade::firstOrCreate([
                    'desbravador_id' => $dbv->id,
                    'mes' => $data->month,
                    'ano' => $data->year,
                ], [
                    'valor' => 15.00,
                    'status' => $status,
                    'data_pagamento' => $status == 'pago' ? $data->copy()->addDays(rand(1, 10)) : null,
                ]);
            }
        }
        $this->command->info('💰 Financeiro (Caixa e Mensalidades) atualizado.');

        // ---------------------------------------------------------
        // 7. PATRIMÔNIO
        // ---------------------------------------------------------
        $itens = [
            ['item' => 'Barraca Canadense', 'qtd' => 5, 'valor' => 450.00, 'estado' => 'Bom'],
            ['item' => 'Barraca Iglu 4 Pessoas', 'qtd' => 8, 'valor' => 300.00, 'estado' => 'Novo'],
            ['item' => 'Lona 6x4', 'qtd' => 2, 'valor' => 150.00, 'estado' => 'Regular'],
            ['item' => 'Caixa de Som Amplificada', 'qtd' => 1, 'valor' => 1200.00, 'estado' => 'Bom'],
            ['item' => 'Bandeiras Oficiais', 'qtd' => 4, 'valor' => 80.00, 'estado' => 'Novo'],
            ['item' => 'Panelas de Acampamento', 'qtd' => 3, 'valor' => 100.00, 'estado' => 'Ruim'],
        ];

        foreach ($itens as $item) {
            Patrimonio::create([
                'item' => $item['item'],
                'quantidade' => $item['qtd'],
                'valor_estimado' => $item['valor'],
                'estado_conservacao' => $item['estado'],
                'data_aquisicao' => fake()->dateTimeBetween('-3 years', '-1 month'),
                'local_armazenamento' => 'Almoxarifado Sede',
                'observacoes' => 'Inventário Inicial 2026',
            ]);
        }
        $this->command->info('📦 Inventário de Patrimônio criado.');

        // ---------------------------------------------------------
        // 8. SECRETARIA (ATAS E ATOS)
        // ---------------------------------------------------------
        for ($i = 0; $i < 5; $i++) {
            Ata::create([
                'titulo' => 'Reunião Administrativa nº '.($i + 1),
                'tipo' => fake()->randomElement(['Regular', 'Diretoria', 'Planejamento']),
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),
                'hora_inicio' => '09:00',
                'hora_fim' => '11:00',
                'local' => 'Sede do Clube',
                'secretario_responsavel' => 'Secretária Ana',
                'participantes' => 'Diretoria completa e Conselheiros.',
                'conteudo' => fake()->paragraphs(3, true),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Ato::create([
                'numero' => str_pad($i + 1, 3, '0', STR_PAD_LEFT).'/2026',
                'tipo' => fake()->randomElement(['Nomeação', 'Exoneração']),
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao' => fake()->sentence(10),
                'desbravador_id' => $desbravadores->random()->id,
            ]);
        }
        $this->command->info('📂 Documentos de Secretaria gerados.');

        // ---------------------------------------------------------
        // 9. FREQUÊNCIA
        // ---------------------------------------------------------
        $datasChamada = [
            Carbon::now()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeeks(1)->startOfWeek(Carbon::SUNDAY),
        ];

        foreach ($datasChamada as $data) {
            foreach ($desbravadores as $dbv) {
                Frequencia::firstOrCreate([
                    'desbravador_id' => $dbv->id,
                    'data' => $data->format('Y-m-d'),
                ], [
                    'presente' => $presente = fake()->boolean(80),
                    'pontual' => $presente ? fake()->boolean(90) : false,
                    'biblia' => $presente ? fake()->boolean(70) : false,
                    'uniforme' => $presente ? fake()->boolean(95) : false,
                ]);
            }
        }
        $this->command->info('📋 Frequência das últimas reuniões registrada.');

        $this->command->info('---------------------------------------------------------');
        $this->command->info('🚀 SISTEMA COMPLETO PRONTO PARA USO!');
        $this->command->info('---------------------------------------------------------');
    }
}
