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
        // Opcional: Limpar tabelas se necessário (cuidado em produção)
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // User::truncate(); ...

        $this->command->info('🌱 Iniciando população completa do banco de dados...');

        // ---------------------------------------------------------
        // 1. CLUBE
        // ---------------------------------------------------------
        $clube = Club::create([
            'nome' => 'Clube de Desbravadores Orion',
            'cidade' => 'São Paulo',
            'associacao' => 'Associação Paulista Leste',
            'logo' => null,
        ]);

        // ---------------------------------------------------------
        // 2. USUÁRIOS DO SISTEMA (POR CARGO)
        // ---------------------------------------------------------

        // Master (Acesso Total)
        User::create([
            'name' => 'Administrador Master',
            'email' => 'admin@desbravadores.com',
            'password' => Hash::make('password'),
            'role' => 'master',
            'is_master' => true,
            'club_id' => null,
        ]);

        // Diretor (Gestão Geral do Clube)
        $diretor = User::create([
            'name' => 'Diretor Silva',
            'email' => 'diretor@clube.com',
            'password' => Hash::make('password'),
            'role' => 'diretor',
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        // Secretário (Documentos e Cadastros)
        User::create([
            'name' => 'Secretária Ana',
            'email' => 'secretaria@clube.com',
            'password' => Hash::make('password'),
            'role' => 'secretario',
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        // Tesoureiro (Apenas Financeiro e Eventos)
        User::create([
            'name' => 'Tesoureiro Carlos',
            'email' => 'tesoureiro@clube.com',
            'password' => Hash::make('password'),
            'role' => 'tesoureiro',
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        // Instrutor (Apenas Pedagógico)
        User::create([
            'name' => 'Instrutor Marcos',
            'email' => 'instrutor@clube.com',
            'password' => Hash::make('password'),
            'role' => 'instrutor',
            'is_master' => false,
            'club_id' => $clube->id,
        ]);

        $this->command->info('✅ Equipe administrativa criada.');

        // ---------------------------------------------------------
        // 3. UNIDADES & CONSELHEIROS
        // ---------------------------------------------------------
        // Cria unidade e o usuário conselheiro correspondente para testar permissão "Minha Unidade"

        $unidades = collect();
        $dadosUnidades = [
            ['nome' => 'Águias', 'grito' => 'Voando alto, sempre avante!', 'conselheiro' => 'Conselheiro Pedro', 'email' => 'pedro@clube.com'],
            ['nome' => 'Leões', 'grito' => 'Força e coragem, somos Leões!', 'conselheiro' => 'Conselheiro João', 'email' => 'joao@clube.com'],
            ['nome' => 'Escorpiões', 'grito' => 'Pequenos no tamanho, gigantes na bravura!', 'conselheiro' => 'Conselheiro Lucas', 'email' => 'lucas@clube.com'],
            ['nome' => 'Falcões', 'grito' => 'Velocidade e precisão, Falcões em ação!', 'conselheiro' => 'Conselheira Maria', 'email' => 'maria@clube.com'],
        ];

        foreach ($dadosUnidades as $dado) {
            // Cria a Unidade
            $unidade = Unidade::create([
                'nome' => $dado['nome'],
                'grito_guerra' => $dado['grito'],
                'conselheiro' => $dado['conselheiro'], // Nome textual que o Gate verifica
            ]);
            $unidades->push($unidade);

            // Cria o Usuário Conselheiro (Login para gerir essa unidade)
            User::create([
                'name' => $dado['conselheiro'],
                'email' => $dado['email'],
                'password' => Hash::make('password'),
                'role' => 'conselheiro',
                'is_master' => false,
                'club_id' => $clube->id,
            ]);
        }
        $this->command->info('✅ Unidades e seus respectivos Conselheiros criados.');

        // ---------------------------------------------------------
        // 4. PEDAGÓGICO: CLASSES E ESPECIALIDADES
        // ---------------------------------------------------------

        // Classes
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
        // 5. DESBRAVADORES (MEMBROS)
        // ---------------------------------------------------------
        $desbravadores = collect();
        foreach ($unidades as $unidade) {
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

                // Especialidades Concluídas
                $dbv->especialidades()->attach($especialidades->random(rand(1, 5))->pluck('id'), [
                    'data_conclusao' => fake()->dateTimeBetween('-2 years', 'now')
                ]);

                // Progresso na Classe
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
        // 6. EVENTOS E ACAMPAMENTOS
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
                'descricao' => 'Evento oficial do calendário anual. Presença obrigatória.'
            ]);

            // Inscrições aleatórias
            foreach ($desbravadores as $dbv) {
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

        // Mensalidades
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
                'observacoes' => 'Inventário 2026'
            ]);
        }
        $this->command->info('✅ Patrimônio populado.');

        // ---------------------------------------------------------
        // 9. SECRETARIA (ATAS E ATOS)
        // ---------------------------------------------------------
        for ($i = 0; $i < 5; $i++) {
            Ata::create([
                'tipo' => fake()->randomElement(['Regular', 'Diretoria', 'Planejamento']),
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),
                'secretario_responsavel' => 'Secretária Ana',
                'participantes' => 'Diretoria completa.',
                'conteudo' => fake()->paragraphs(3, true),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            Ato::create([
                'tipo' => fake()->randomElement(['Nomeação', 'Exoneração']),
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao_resumida' => 'Ato oficial administrativo.',
                'texto_completo' => fake()->paragraph(),
                'desbravador_id' => $desbravadores->random()->id,
            ]);
        }
        $this->command->info('✅ Secretaria populada.');

        // ---------------------------------------------------------
        // 10. FREQUÊNCIA
        // ---------------------------------------------------------
        $datasChamada = [
            Carbon::now()->startOfWeek(Carbon::SUNDAY),
            Carbon::now()->subWeek()->startOfWeek(Carbon::SUNDAY),
        ];

        foreach ($datasChamada as $data) {
            foreach ($desbravadores as $dbv) {
                $presente = fake()->boolean(80);
                Frequencia::create([
                    'desbravador_id' => $dbv->id,
                    'data' => $data,
                    'presente' => $presente,
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
        $this->command->info('🔐 USUÁRIOS PARA TESTE (Senha padrão: "password"):');
        $this->command->info('   - Master: admin@desbravadores.com');
        $this->command->info('   - Diretor: diretor@clube.com');
        $this->command->info('   - Secretária: secretaria@clube.com');
        $this->command->info('   - Tesoureiro: tesoureiro@clube.com');
        $this->command->info('   - Instrutor: instrutor@clube.com');
        $this->command->info('   - Conselheiros: pedro@clube.com, joao@clube.com, etc.');
        $this->command->info('---------------------------------------------------------');
    }
}