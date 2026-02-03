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
use App\Models\Requisito;
use App\Models\Unidade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------
        // 1. CLUBE
        // ---------------------------------------------------------
        $this->command->info('🌱 Iniciando população completa do banco de dados...');

        $clube = Club::firstOrCreate([
            'nome' => 'Clube de Desbravadores Orion',
        ], [
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Paulista Leste',
            'logo' => null,
        ]);

        // ---------------------------------------------------------
        // 2. USUÁRIOS DO SISTEMA (POR CARGO)
        // ---------------------------------------------------------

        $cargos = [
            ['name' => 'Administrador Master', 'email' => 'admin@desbravadores.com', 'role' => 'master', 'club_id' => null, 'is_master' => true],
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

        $this->command->info('✅ Equipe administrativa criada.');

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
        $this->command->info('✅ Unidades e seus respectivos Conselheiros criados.');

        // ---------------------------------------------------------
        // 4. PEDAGÓGICO
        // ---------------------------------------------------------

        $dadosClasses = [
            ['nome' => 'Amigo', 'cor' => '#3B82F6', 'reqs' => ['Ter 10 anos completos', 'Saber o Hino Nacional', 'Ler o livro do ano', 'Saber o Voto e a Lei']],
            ['nome' => 'Companheiro', 'cor' => '#F59E0B', 'reqs' => ['Ter 11 anos completos', 'Memorizar livros da Bíblia', 'Demonstrar nós básicos', 'Participar de caminhada de 5km']],
            ['nome' => 'Pesquisador', 'cor' => '#10B981', 'reqs' => ['Ter 12 anos completos', 'Estudar os Evangelhos', 'Identificar 3 constelações', 'Fazer fogo sem fósforo']],
            ['nome' => 'Pioneiro', 'cor' => '#6B7280', 'reqs' => ['Ter 13 anos completos', 'Construir móveis de acampamento', 'Liderar devocional', 'Participar de projeto comunitário']],
            ['nome' => 'Excursionista', 'cor' => '#8B5CF6', 'reqs' => ['Ter 14 anos completos', 'Planejar cardápio de acampamento', 'Primeiros socorros avançado', 'Pernoite ao ar livre']],
            ['nome' => 'Guia', 'cor' => '#EF4444', 'reqs' => ['Ter 15 anos completos', 'Liderar uma unidade por 3 meses', 'Completar especialidade de Ordem Unida', 'Organizar evento social']],
        ];

        $classesModels = collect();
        foreach ($dadosClasses as $idx => $dado) {
            $classe = Classe::firstOrCreate(['nome' => $dado['nome']], [
                'cor' => $dado['cor'],
                'ordem' => $idx + 1,
            ]);
            $classesModels->push($classe);

            foreach ($dado['reqs'] as $i => $desc) {
                Requisito::firstOrCreate(['descricao' => $desc], [
                    'classe_id' => $classe->id,
                    'codigo' => substr($dado['nome'], 0, 1).'-'.($i + 1),
                    'categoria' => 'Gerais',
                ]);
            }
        }

        $areas = ['ADRA', 'Artes e Habilidades Manuais', 'Estudo da Natureza', 'Atividades Recreativas', 'Saúde e Ciência', 'Atividades Missionárias'];
        $nomesEspecialidades = [
            'Nós e Amarras', 'Primeiros Socorros', 'Acampamento I', 'Acampamento II',
            'Culinária', 'Fogueiras e Cozinha', 'Répteis', 'Anfíbios',
            'Astronomia', 'Arte de Acampar', 'Pioneiria', 'Excursionismo',
            'Natação Principiante', 'Ordem Unida', 'Civismo', 'Cães', 'Gatos',
            'Sementes', 'Flores', 'Cactos', 'Arte de Contar Histórias',
        ];

        $especialidades = collect();
        foreach ($nomesEspecialidades as $nome) {
            $especialidades->push(Especialidade::firstOrCreate(['nome' => $nome], [
                'area' => fake()->randomElement($areas),
                'cor_fundo' => fake()->hexColor(),
            ]));
        }
        $this->command->info('✅ Classes e Especialidades populadas.');

        // ---------------------------------------------------------
        // 5. DESBRAVADORES
        // ---------------------------------------------------------
        $desbravadores = collect();
        $diretor = User::where('role', 'diretor')->first();

        foreach ($unidades as $unidade) {
            for ($i = 0; $i < rand(6, 8); $i++) {
                $sexo = fake()->randomElement(['M', 'F']);

                $dbv = Desbravador::create([
                    'ativo' => true,
                    'nome' => fake()->name($sexo == 'M' ? 'male' : 'female'),
                    'data_nascimento' => fake()->dateTimeBetween('-15 years', '-10 years'),
                    'sexo' => $sexo,
                    'unidade_id' => $unidade->id,
                    'classe_atual' => fake()->randomElement(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro']),
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

                // Especialidades
                $dbv->especialidades()->attach($especialidades->random(rand(1, 5))->pluck('id'), [
                    'data_conclusao' => fake()->dateTimeBetween('-2 years', 'now'),
                ]);

                // Progresso
                $classeObj = $classesModels->where('nome', $dbv->classe_atual)->first();
                if ($classeObj) {
                    $reqs = $classeObj->requisitos->random(rand(1, 2));
                    foreach ($reqs as $req) {
                        $dbv->requisitosCumpridos()->attach($req->id, [
                            'user_id' => $diretor->id,
                            'data_conclusao' => now()->subDays(rand(1, 60)),
                        ]);
                    }
                }
                $desbravadores->push($dbv);
            }
        }
        $this->command->info('✅ Desbravadores criados com Prontuário e Progresso.');

        // ---------------------------------------------------------
        // 6. EVENTOS
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

            foreach ($desbravadores as $dbv) {
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
        $this->command->info('✅ Eventos criados.');

        // ---------------------------------------------------------
        // 7. FINANCEIRO
        // ---------------------------------------------------------
        for ($i = 0; $i < 30; $i++) {
            $tipo = fake()->randomElement(['entrada', 'saida']);
            Caixa::create([
                'descricao' => $tipo == 'entrada' ? fake()->randomElement(['Doação', 'Venda de Pizza', 'Cantina']) : fake()->randomElement(['Material de Escritório', 'Gás', 'Manutenção Barracas']),
                'tipo' => $tipo,
                'valor' => fake()->randomFloat(2, 20, 300),
                'data_movimentacao' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // MENSALIDADES (CORRIGIDO PARA EVITAR ERRO DE DIA 31)
        // Usamos startOfMonth() para garantir que a data seja sempre dia 01
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
                    'valor' => 20.00,
                    'status' => $status,
                    'data_pagamento' => $status == 'pago' ? $data->copy()->addDays(rand(1, 10)) : null,
                ]);
            }
        }
        $this->command->info('✅ Financeiro populado.');

        // ---------------------------------------------------------
        // 8. PATRIMÔNIO
        // ---------------------------------------------------------
        $itens = [
            ['item' => 'Barraca Canadense', 'qtd' => 5, 'valor' => 450.00, 'estado' => 'Bom'],
            ['item' => 'Barraca Iglu 4 Pessoas', 'qtd' => 8, 'valor' => 300.00, 'estado' => 'Novo'],
            ['item' => 'Lona 6x4', 'qtd' => 2, 'valor' => 150.00, 'estado' => 'Regular'],
            ['item' => 'Caixa de Som Amplificada', 'qtd' => 1, 'valor' => 1200.00, 'estado' => 'Bom'],
        ];

        foreach ($itens as $item) {
            Patrimonio::create([
                'item' => $item['item'],
                'quantidade' => $item['qtd'],
                'valor_estimado' => $item['valor'],
                'estado_conservacao' => $item['estado'],
                'data_aquisicao' => fake()->date(),
                'local_armazenamento' => 'Almoxarifado Sede',
                'observacoes' => 'Inventário 2026',
            ]);
        }
        $this->command->info('✅ Patrimônio populado.');

        // ---------------------------------------------------------
        // 9. SECRETARIA (CORRIGIDO: TITULO, HORAS E LOCAL)
        // ---------------------------------------------------------
        for ($i = 0; $i < 5; $i++) {
            Ata::create([
                'titulo' => 'Reunião Administrativa nº '.($i + 1),
                'tipo' => fake()->randomElement(['Regular', 'Diretoria', 'Planejamento']),
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),

                // CORREÇÃO: Campos obrigatórios adicionados
                'hora_inicio' => fake()->time('H:i'),
                'hora_fim' => fake()->time('H:i'),
                'local' => 'Sede do Clube',

                'secretario_responsavel' => 'Secretária Ana',
                'participantes' => 'Diretoria completa.',
                'conteudo' => fake()->paragraphs(3, true),
            ]);
        }

        // ATOS ADMINISTRATIVOS
        for ($i = 0; $i < 3; $i++) {
            Ato::create([
                'numero' => str_pad($i + 1, 3, '0', STR_PAD_LEFT).'/2026',
                'tipo' => fake()->randomElement(['Nomeação', 'Exoneração']),
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao' => fake()->paragraph(),
                'desbravador_id' => $desbravadores->random()->id,
            ]);
        }
        $this->command->info('✅ Secretaria populada.');

        // ---------------------------------------------------------
        // 10. FREQUÊNCIA
        // ---------------------------------------------------------
        // FREQUÊNCIA (CORRIGIDO PARA EVITAR ERRO DE DIA 31)
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
        $this->command->info('✅ Frequência gerada.');

        $this->command->info('---------------------------------------------------------');
        $this->command->info('🚀 BANCO DE DADOS 100% POPULADO COM SUCESSO!');
        $this->command->info('---------------------------------------------------------');
    }
}
