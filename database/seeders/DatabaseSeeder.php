<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
use App\Models\Evento;
use App\Models\Classe;
use App\Models\Requisito;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar tabelas para evitar duplicidade em seeders manuais
        // Em produção cuidado, mas em dev é útil.
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); 
        // User::truncate(); ... (Opcional, o migrate:fresh já faz isso)

        $this->command->info('🌱 Iniciando população completa do banco de dados...');

        // ---------------------------------------------------------
        // 1. MEU CLUBE & USUÁRIOS
        // ---------------------------------------------------------
        $clube = Club::create([
            'nome' => 'Clube de Desbravadores Orion',
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Paulista Leste',
            'logo' => null, // Poderia ser um caminho de imagem fictício
        ]);

        // Usuário Master
        User::create([
            'name' => 'Administrador Master',
            'email' => 'admin@desbravadores.com',
            'password' => Hash::make('password'),
            'is_master' => true,
            'club_id' => null,
        ]);

        // Usuário Diretor (Logado)
        $diretor = User::create([
            'name' => 'Diretor Silva',
            'email' => 'diretor@clube.com',
            'password' => Hash::make('password'),
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        // Usuário Secretário (Para assinar atas)
        $secretario = User::create([
            'name' => 'Secretária Ana',
            'email' => 'secretaria@clube.com',
            'password' => Hash::make('password'),
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        $this->command->info('✅ Clube e Usuários criados.');

        // ---------------------------------------------------------
        // 2. UNIDADES
        // ---------------------------------------------------------
        $unidades = collect();
        $dadosUnidades = [
            ['nome' => 'Águias', 'grito' => 'Voando alto, sempre avante! Águias!', 'conselheiro' => 'Conselheiro Pedro'],
            ['nome' => 'Leões', 'grito' => 'Força e coragem, somos Leões!', 'conselheiro' => 'Conselheiro João'],
            ['nome' => 'Escorpiões', 'grito' => 'Pequenos no tamanho, gigantes na bravura!', 'conselheiro' => 'Conselheiro Lucas'],
            ['nome' => 'Falcões', 'grito' => 'Velocidade e precisão, Falcões em ação!', 'conselheiro' => 'Conselheira Maria'],
        ];

        foreach ($dadosUnidades as $dado) {
            $unidades->push(Unidade::create([
                'nome' => $dado['nome'],
                'grito_guerra' => $dado['grito'],
                'conselheiro' => $dado['conselheiro'],
            ]));
        }
        $this->command->info('✅ Unidades criadas.');

        // ---------------------------------------------------------
        // 3. PEDAGÓGICO: CLASSES, REQUISITOS E ESPECIALIDADES
        // ---------------------------------------------------------

        // Classes Regulares
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
            $classe = Classe::create([
                'nome' => $dado['nome'],
                'cor' => $dado['cor'],
                'ordem' => $idx + 1
            ]);
            $classesModels->push($classe);

            foreach ($dado['reqs'] as $i => $desc) {
                Requisito::create([
                    'classe_id' => $classe->id,
                    'codigo' => substr($dado['nome'], 0, 1) . '-' . ($i + 1),
                    'descricao' => $desc,
                    'categoria' => 'Gerais'
                ]);
            }
        }

        // Especialidades
        $areas = ['ADRA', 'Artes e Habilidades Manuais', 'Estudo da Natureza', 'Atividades Recreativas', 'Saúde e Ciência', 'Atividades Missionárias'];
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
            'Civismo',
            'Cães',
            'Gatos',
            'Sementes',
            'Flores',
            'Cactos',
            'Arte de Contar Histórias'
        ];

        $especialidades = collect();
        foreach ($nomesEspecialidades as $nome) {
            $especialidades->push(Especialidade::create([
                'nome' => $nome,
                'area' => fake()->randomElement($areas),
                'cor_fundo' => fake()->hexColor(),
            ]));
        }
        $this->command->info('✅ Classes e Especialidades populadas.');

        // ---------------------------------------------------------
        // 4. DESBRAVADORES (MEMBROS)
        // ---------------------------------------------------------
        $desbravadores = collect();
        foreach ($unidades as $unidade) {
            // Cria 6 a 8 desbravadores por unidade
            for ($i = 0; $i < rand(6, 8); $i++) {
                $sexo = fake()->randomElement(['M', 'F']);
                $nome = fake()->name($sexo == 'M' ? 'male' : 'female');

                $dbv = Desbravador::create([
                    'ativo' => true,
                    'nome' => $nome,
                    'data_nascimento' => fake()->dateTimeBetween('-15 years', '-10 years'),
                    'sexo' => $sexo,
                    'unidade_id' => $unidade->id,
                    'classe_atual' => fake()->randomElement(['Amigo', 'Companheiro', 'Pesquisador', 'Pioneiro']),

                    // Dados Completos
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

                // Adiciona Especialidades Concluídas
                $dbv->especialidades()->attach($especialidades->random(rand(1, 5))->pluck('id'), [
                    'data_conclusao' => fake()->dateTimeBetween('-2 years', 'now')
                ]);

                // Adiciona Progresso na Classe Atual (Marca 1 ou 2 requisitos como feitos)
                $classeObj = $classesModels->where('nome', $dbv->classe_atual)->first();
                if ($classeObj) {
                    $reqs = $classeObj->requisitos->random(rand(1, 2));
                    foreach ($reqs as $req) {
                        $dbv->requisitosCumpridos()->attach($req->id, [
                            'user_id' => $diretor->id,
                            'data_conclusao' => now()->subDays(rand(1, 60))
                        ]);
                    }
                }

                $desbravadores->push($dbv);
            }
        }
        $this->command->info('✅ Desbravadores criados com Prontuário e Progresso.');

        // ---------------------------------------------------------
        // 5. EVENTOS E ACAMPAMENTOS
        // ---------------------------------------------------------
        $listaEventos = [
            // Passado
            ['nome' => 'Acampamento de Instrução', 'local' => 'Chácara Oliveira', 'valor' => 120.00, 'inicio' => '-2 months', 'fim' => '-2 months + 2 days'],
            ['nome' => 'Caminhada Noturna', 'local' => 'Trilha do Morro', 'valor' => 0.00, 'inicio' => '-1 month', 'fim' => '-1 month'],
            // Futuro
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
                'descricao' => 'Evento oficial do calendário anual. Presença obrigatória.'
            ]);

            // Inscrever membros
            foreach ($desbravadores as $dbv) {
                // Eventos passados: maioria foi
                // Eventos futuros: alguns inscritos
                $chance = (strtotime($evt['inicio']) < time()) ? 80 : 40;

                if (fake()->boolean($chance)) {
                    $pago = ($evento->valor == 0) || fake()->boolean(60);
                    $evento->desbravadores()->attach($dbv->id, [
                        'pago' => $pago,
                        'autorizacao_entregue' => fake()->boolean(70)
                    ]);
                }
            }
        }
        $this->command->info('✅ Eventos criados e inscrições realizadas.');

        // ---------------------------------------------------------
        // 6. FINANCEIRO (CAIXA E MENSALIDADES)
        // ---------------------------------------------------------
        // Caixa: Movimentações aleatórias
        for ($i = 0; $i < 30; $i++) {
            $tipo = fake()->randomElement(['entrada', 'saida']);
            Caixa::create([
                'descricao' => $tipo == 'entrada' ? fake()->randomElement(['Doação', 'Venda de Pizza', 'Cantina']) : fake()->randomElement(['Material de Escritório', 'Gás', 'Manutenção Barracas']),
                'tipo' => $tipo,
                'valor' => fake()->randomFloat(2, 20, 300),
                'data_movimentacao' => fake()->dateTimeBetween('-6 months', 'now'),
            ]);
        }

        // Mensalidades: Gerar para os últimos 3 meses
        $meses = [now()->subMonths(2), now()->subMonth(), now()];
        foreach ($meses as $data) {
            foreach ($desbravadores as $dbv) {
                $status = fake()->boolean(70) ? 'pago' : 'pendente';
                Mensalidade::create([
                    'desbravador_id' => $dbv->id,
                    'mes' => $data->month,
                    'ano' => $data->year,
                    'valor' => 20.00,
                    'status' => $status,
                    'data_pagamento' => $status == 'pago' ? $data->copy()->addDays(rand(1, 10)) : null
                ]);
            }
        }
        $this->command->info('✅ Financeiro (Caixa e Mensalidades) populado.');

        // ---------------------------------------------------------
        // 7. PATRIMÔNIO
        // ---------------------------------------------------------
        $itens = [
            ['item' => 'Barraca Canadense', 'qtd' => 5, 'valor' => 450.00, 'estado' => 'Bom'],
            ['item' => 'Barraca Iglu 4 Pessoas', 'qtd' => 8, 'valor' => 300.00, 'estado' => 'Novo'],
            ['item' => 'Lona 6x4', 'qtd' => 2, 'valor' => 150.00, 'estado' => 'Regular'],
            ['item' => 'Caixa de Som Amplificada', 'qtd' => 1, 'valor' => 1200.00, 'estado' => 'Bom'],
            ['item' => 'Bandeira Oficial', 'qtd' => 1, 'valor' => 200.00, 'estado' => 'Novo'],
            ['item' => 'Machadinha', 'qtd' => 4, 'valor' => 80.00, 'estado' => 'Ruim'],
            ['item' => 'Fogareiro 2 Bocas', 'qtd' => 2, 'valor' => 250.00, 'estado' => 'Regular'],
        ];

        foreach ($itens as $item) {
            Patrimonio::create([
                'item' => $item['item'],
                'quantidade' => $item['qtd'],
                'valor_estimado' => $item['valor'],
                'estado_conservacao' => $item['estado'],
                'data_aquisicao' => fake()->date(),
                'local_armazenamento' => 'Almoxarifado Sede',
                'observacoes' => 'Inventário 2026'
            ]);
        }
        $this->command->info('✅ Patrimônio populado.');

        // ---------------------------------------------------------
        // 8. SECRETARIA (ATAS E ATOS)
        // ---------------------------------------------------------
        // Atas
        for ($i = 0; $i < 10; $i++) {
            Ata::create([
                'tipo' => fake()->randomElement(['Regular', 'Diretoria', 'Planejamento']),
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),
                'secretario_responsavel' => 'Secretária Ana',
                'participantes' => 'Diretoria completa e conselheiros.',
                'conteudo' => fake()->paragraphs(3, true),
            ]);
        }

        // Atos (Nomeações/Disciplinas)
        for ($i = 0; $i < 5; $i++) {
            Ato::create([
                'tipo' => fake()->randomElement(['Nomeação', 'Exoneração', 'Disciplina']),
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao_resumida' => 'Ato oficial administrativo referente a membro.',
                'texto_completo' => fake()->paragraph(),
                'desbravador_id' => $desbravadores->random()->id,
            ]);
        }
        $this->command->info('✅ Secretaria (Atas e Atos) populada.');

        // ---------------------------------------------------------
        // 9. FREQUÊNCIA E RANKING
        // ---------------------------------------------------------
        // Gera chamadas para os últimos 4 domingos
        $datasChamada = [
            Carbon::now()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeeks(2)->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeeks(3)->startOfWeek(Carbon::SUNDAY),
        ];

        foreach ($datasChamada as $data) {
            foreach ($desbravadores as $dbv) {
                // Simula presença (80% de chance de estar presente)
                $presente = fake()->boolean(80);

                Frequencia::create([
                    'desbravador_id' => $dbv->id,
                    'data' => $data,
                    'presente' => $presente,
                    // Se faltou, não ganha pontos extras
                    'pontual' => $presente ? fake()->boolean(90) : false,
                    'biblia' => $presente ? fake()->boolean(70) : false,
                    'uniforme' => $presente ? fake()->boolean(95) : false,
                ]);
            }
        }
        $this->command->info('✅ Frequência e Ranking gerados.');

        $this->command->info('------------------------------------------');
        $this->command->info('🚀 BANCO DE DADOS 100% POPULADO COM SUCESSO!');
        $this->command->info('   Use: admin@desbravadores.com / password');
        $this->command->info('------------------------------------------');
    }
}
