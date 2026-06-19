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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

if (! function_exists(__NAMESPACE__.'\\fake')) {
    /**
     * Fallback para ambientes de produção sem fakerphp/faker (composer --no-dev).
     */
    function fake(): object
    {
        static $faker = null;

        if (\function_exists('\\fake')) {
            return \fake();
        }

        if ($faker === null) {
            $faker = new SeederFallbackFaker;
        }

        return $faker;
    }
}

final class SeederFallbackFaker
{
    private int $emailCounter = 1;

    public function randomElement(array $items): mixed
    {
        if ($items === []) {
            return null;
        }

        return $items[array_rand($items)];
    }

    public function name(?string $gender = null): string
    {
        $male = ['João', 'Pedro', 'Lucas', 'Gabriel', 'Rafael', 'Mateus', 'Bruno', 'Daniel'];
        $female = ['Ana', 'Maria', 'Beatriz', 'Júlia', 'Larissa', 'Carolina', 'Vitória', 'Fernanda'];
        $surnames = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Lima', 'Pereira', 'Costa', 'Almeida'];

        $first = $gender === 'female'
            ? $this->randomElement($female)
            : ($gender === 'male' ? $this->randomElement($male) : $this->randomElement(array_merge($male, $female)));

        return trim($first.' '.$this->randomElement($surnames));
    }

    public function dateTimeBetween(string $startDate = '-30 years', string $endDate = 'now'): \DateTime
    {
        $start = strtotime($startDate) ?: time() - 86400;
        $end = strtotime($endDate) ?: time();

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return (new \DateTime)->setTimestamp(random_int($start, $end));
    }

    public function unique(): self
    {
        return $this;
    }

    public function safeEmail(): string
    {
        $value = sprintf('seed.user.%04d@example.org', $this->emailCounter);
        $this->emailCounter++;

        return $value;
    }

    public function phoneNumber(): string
    {
        return sprintf('(11) 9%04d-%04d', random_int(1000, 9999), random_int(1000, 9999));
    }

    public function address(): string
    {
        return sprintf(
            'Rua %s, %d - São Paulo/SP',
            $this->randomElement(['das Flores', 'do Sol', 'das Acácias', 'Central', 'da Esperança']),
            random_int(10, 9999)
        );
    }

    public function numerify(string $pattern): string
    {
        return preg_replace_callback('/#/', fn () => (string) random_int(0, 9), $pattern) ?? $pattern;
    }

    public function boolean(int $chanceOfGettingTrue = 50): bool
    {
        return random_int(1, 100) <= max(0, min(100, $chanceOfGettingTrue));
    }

    public function randomFloat(int $maxDecimals = 2, float $min = 0, float $max = 1): float
    {
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }

        $factor = 10 ** max(0, $maxDecimals);

        return round(random_int((int) round($min * $factor), (int) round($max * $factor)) / $factor, $maxDecimals);
    }

    public function paragraphs(int $nb = 3, bool $asText = false): array|string
    {
        $base = [
            'Reunião com foco em planejamento mensal e alinhamento das atividades.',
            'Definição de responsabilidades da diretoria para o próximo ciclo.',
            'Avaliação das ações realizadas e melhoria dos processos internos.',
            'Organização de cronograma para eventos e classes dos desbravadores.',
            'Registro de decisões e próximos passos para acompanhamento.',
        ];

        $paragraphs = [];
        for ($i = 0; $i < max(1, $nb); $i++) {
            $paragraphs[] = $this->randomElement($base);
        }

        return $asText ? implode("\n\n", $paragraphs) : $paragraphs;
    }

    public function sentence(int $nbWords = 6): string
    {
        $words = [
            'planejamento', 'diretoria', 'clube', 'atividade', 'investidura', 'desbravadores',
            'organização', 'reunião', 'mensal', 'alinhamento', 'calendário', 'objetivos',
        ];

        $parts = [];
        for ($i = 0; $i < max(1, $nbWords); $i++) {
            $parts[] = $this->randomElement($words);
        }

        return ucfirst(implode(' ', $parts)).'.';
    }
}

class DatabaseSeeder extends Seeder
{
    /**
     * Quantas chamadas (datas de frequência) gerar por clube. Mínimo do pedido = 4;
     * usamos 6 para dar folga ao ranking sem empates (mais resolução de pontos).
     */
    private const NUM_CHAMADAS = 6;

    /**
     * Definição dos 5 clubes — todos na cidade de São Paulo, um por Associação
     * (campo administrativo). Todos os logins seguem o padrão <cargo>.<slug>@clube.com.
     */
    private const CLUBES = [
        ['nome' => 'Clube Orion', 'slug' => 'orion', 'associacao' => 'Associação Paulistana'],
        ['nome' => 'Clube Aurora', 'slug' => 'aurora', 'associacao' => 'Associação Paulista Central'],
        ['nome' => 'Clube Vega', 'slug' => 'vega', 'associacao' => 'Associação Paulista Leste'],
        ['nome' => 'Clube Sirius', 'slug' => 'sirius', 'associacao' => 'Associação Paulista Sul'],
        ['nome' => 'Clube Antares', 'slug' => 'antares', 'associacao' => 'Associação Paulista Sudeste'],
    ];

    /** Pool de nomes de unidade — 4 por clube, sem repetir entre clubes (20 nomes). */
    private const NOMES_UNIDADES = [
        'Águias', 'Leões', 'Falcões', 'Tigres',
        'Panteras', 'Lobos', 'Raposas', 'Corujas',
        'Gaviões', 'Escorpiões', 'Cobras', 'Jaguares',
        'Búfalos', 'Ursos', 'Andorinhas', 'Pumas',
        'Linces', 'Tubarões', 'Golfinhos', 'Águias Reais',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->call(\Database\Seeders\MasterOnlySeeder::class);

            return;
        }

        $this->command->info('🌱 Iniciando população do banco (multi-tenant: 5 clubes)...');

        // ---------------------------------------------------------
        // 0. CATÁLOGO GLOBAL (classes + especialidades) — compartilhado
        // ---------------------------------------------------------
        $this->call([
            ClassesSeeder::class,
            EspecialidadesSeeder::class,
        ]);

        $classes = Classe::orderBy('ordem')->get();
        $especialidades = Especialidade::all();

        // ---------------------------------------------------------
        // 1. SUPER ADMIN DE PLATAFORMA (cross-tenant, sem clube)
        // ---------------------------------------------------------
        User::updateOrCreate(['email' => 'admin@clube.com'], [
            'name' => 'Administrador da Plataforma',
            'password' => Hash::make('password'),
            'role' => 'platform_admin',
            'is_master' => false,
            'is_platform_admin' => true,
            'club_id' => null,
        ]);
        $this->command->info('🛡️  Platform admin: admin@clube.com / password');

        // ---------------------------------------------------------
        // 2. CLUBES (5) — cada um isolado por club_id
        // ---------------------------------------------------------
        foreach (self::CLUBES as $indice => $def) {
            $this->semearClube($indice, $def, $classes, $especialidades);
        }

        $this->command->info('---------------------------------------------------------');
        $this->command->info('🚀 5 clubes prontos. Logins por clube: <cargo>.<slug>@clube.com');
        $this->command->info('   Cargos: master / diretor / secretaria / tesoureiro / instrutor / conselheiro{1-4}');
        $this->command->info('   Slugs: orion, aurora, vega, sirius, antares (ex.: diretor.orion@clube.com).');
        $this->command->info('   Todas as senhas: password');
        $this->command->info('---------------------------------------------------------');
    }

    /**
     * Semeia um clube completo: equipe, 4 unidades, ~30 desbravadores distribuídos
     * por todas as classes, especialidades, frequência (ranking sem empates),
     * eventos, financeiro, patrimônio e documentos.
     */
    private function semearClube(int $indice, array $def, Collection $classes, Collection $especialidades): void
    {
        $slug = $def['slug'];

        $clube = Club::firstOrCreate(['nome' => $def['nome']], [
            'cidade' => 'São Paulo',
            'associacao' => $def['associacao'],
            'is_active' => true,
            'logo' => null,
        ]);

        $this->command->info("🏢 [{$clube->nome}] — {$def['associacao']}");

        // -- Equipe administrativa (logins padrão <cargo>.<slug>@clube.com) ----
        $cargos = [
            ['cargo' => 'master', 'role' => 'master', 'is_master' => true, 'nome' => "Master {$slug}"],
            ['cargo' => 'diretor', 'role' => 'diretor', 'is_master' => false, 'nome' => "Diretor {$slug}"],
            ['cargo' => 'secretaria', 'role' => 'secretario', 'is_master' => false, 'nome' => "Secretária {$slug}"],
            ['cargo' => 'tesoureiro', 'role' => 'tesoureiro', 'is_master' => false, 'nome' => "Tesoureiro {$slug}"],
            ['cargo' => 'instrutor', 'role' => 'instrutor', 'is_master' => false, 'nome' => "Instrutor {$slug}"],
        ];

        $diretor = null;
        foreach ($cargos as $c) {
            $email = "{$c['cargo']}.{$slug}@clube.com";

            $user = User::firstOrCreate(['email' => $email], [
                'name' => $c['nome'],
                'password' => Hash::make('password'),
                'role' => $c['role'],
                'is_master' => $c['is_master'],
                'is_platform_admin' => false,
                'club_id' => $clube->id,
            ]);

            if ($c['role'] === 'diretor') {
                $diretor = $user;
            }
        }

        // -- 4 unidades + conselheiros ----------------------------------------
        $nomesUnidades = array_slice(self::NOMES_UNIDADES, $indice * 4, 4);

        $emailsConselheiros = array_map(fn (int $n) => "conselheiro{$n}.{$slug}@clube.com", [1, 2, 3, 4]);

        $unidades = collect();
        foreach ($nomesUnidades as $pos => $nome) {
            $conselheiroUser = User::firstOrCreate(['email' => $emailsConselheiros[$pos]], [
                'name' => "Conselheiro {$nome}",
                'password' => Hash::make('password'),
                'role' => 'conselheiro',
                'is_master' => false,
                'is_platform_admin' => false,
                'club_id' => $clube->id,
            ]);

            $unidade = Unidade::firstOrCreate(
                ['nome' => $nome, 'club_id' => $clube->id],
                [
                    'grito_guerra' => "Avante, {$nome}!",
                    'conselheiro' => "Conselheiro {$nome}",
                    'conselheiro_user_id' => $conselheiroUser->id,
                    'no_ranking' => true, // true = PARTICIPA do ranking
                ]
            );
            $unidades->push($unidade);
        }

        // -- ~30 desbravadores, distribuídos por TODAS as classes -------------
        $desbravadores = collect();
        $classeIndex = 0;
        foreach ($unidades as $unidade) {
            $qtd = random_int(7, 8); // 4 unidades x 7-8 ≈ 28-32 ≈ 30
            for ($i = 0; $i < $qtd; $i++) {
                // Round-robin nas classes garante cobertura de todas elas.
                $classe = $classes[$classeIndex % $classes->count()];
                $classeIndex++;

                $sexo = fake()->randomElement(['M', 'F']);
                // Idade coerente com a ordem da classe (Amigo≈10 ... Líder Máster Av.≈18+).
                $idade = 9 + (int) $classe->ordem;

                $dbv = Desbravador::create([
                    'ativo' => true,
                    'nome' => fake()->name($sexo === 'M' ? 'male' : 'female'),
                    'data_nascimento' => fake()->dateTimeBetween("-{$idade} years", '-'.($idade - 1).' years'),
                    'sexo' => $sexo,
                    'unidade_id' => $unidade->id,
                    'club_id' => $clube->id,
                    'classe_atual' => $classe->id,
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

                // Especialidades distribuídas no cadastro (1 a 4 por desbravador).
                if ($especialidades->isNotEmpty()) {
                    $dbv->especialidades()->attach(
                        $especialidades->random(random_int(1, 4))->pluck('id'),
                        ['data_conclusao' => fake()->dateTimeBetween('-2 years', 'now')]
                    );
                }

                // Progresso de requisitos da classe atual.
                if ($classe->requisitos->count() > 0) {
                    $reqs = $classe->requisitos->random(min(4, $classe->requisitos->count()));
                    foreach ($reqs as $req) {
                        $dbv->requisitosCumpridos()->attach($req->id, [
                            'user_id' => $diretor?->id,
                            'data_conclusao' => now()->subDays(random_int(1, 90)),
                        ]);
                    }
                }

                $desbravadores->push($dbv);
            }
        }

        // -- Frequência + ranking SEM EMPATES ---------------------------------
        $this->semearFrequencia($clube->id, $desbravadores);

        // -- 5 eventos --------------------------------------------------------
        $this->semearEventos($clube->id, $def, $desbravadores);

        // -- Financeiro: caixa + mensalidades ---------------------------------
        $this->semearFinanceiro($clube->id, $desbravadores);

        // -- Patrimônio -------------------------------------------------------
        $this->semearPatrimonio($clube->id);

        // -- Documentos: 3 atas + 3 atos --------------------------------------
        $this->semearDocumentos($clube->id, $desbravadores);

        $this->command->info("   ✅ {$desbravadores->count()} desbravadores, ".$unidades->count().' unidades, 5 eventos, financeiro, patrimônio e 6 documentos.');
    }

    /**
     * Gera NUM_CHAMADAS datas de frequência e atribui a cada desbravador um total
     * de pontos ÚNICO dentro do clube (escada de múltiplos de 5), eliminando
     * empates no ranking. Os pontos são distribuídos de forma equilibrada pelas
     * chamadas (em colunas legadas presente/pontual/biblia/uniforme).
     */
    private function semearFrequencia(int $clubId, Collection $desbravadores): void
    {
        $datas = collect(range(0, self::NUM_CHAMADAS - 1))
            ->map(fn (int $semanasAtras) => Carbon::now()
                ->subWeeks($semanasAtras)
                ->startOfWeek(Carbon::SUNDAY)
                ->format('Y-m-d'));

        // Total máximo por desbravador = NUM_CHAMADAS * 30 pts = NUM_CHAMADAS * 6 "unidades de 5".
        $maxUnidades = self::NUM_CHAMADAS * 6;

        // Embaralha para que o topo do ranking não fique alinhado por unidade.
        $ordenados = $desbravadores->shuffle()->values();

        foreach ($ordenados as $posicao => $dbv) {
            // Alvo único e decrescente: 36, 35, 34, ... (em unidades de 5 pts).
            $unidadesAlvo = max(0, $maxUnidades - $posicao);

            foreach ($this->distribuirUnidades($unidadesAlvo, self::NUM_CHAMADAS) as $i => $unidadesChamada) {
                Frequencia::firstOrCreate(
                    ['desbravador_id' => $dbv->id, 'data' => $datas[$i]],
                    array_merge(['club_id' => $clubId], $this->unidadesParaColunas($unidadesChamada))
                );
            }
        }
    }

    /**
     * Distribui $total "unidades de 5 pontos" o mais uniformemente possível entre
     * $chamadas (cada chamada cabe 0..6 unidades = 0..30 pts).
     *
     * @return int[]
     */
    private function distribuirUnidades(int $total, int $chamadas): array
    {
        $base = intdiv($total, $chamadas);
        $resto = $total % $chamadas;

        $resultado = [];
        for ($i = 0; $i < $chamadas; $i++) {
            $resultado[$i] = min(6, $base + ($i < $resto ? 1 : 0));
        }

        return $resultado;
    }

    /**
     * Converte "unidades de 5 pts" (0..6) nas colunas booleanas legadas, priorizando
     * presença (presente=10, uniforme=10, pontual=5, biblia=5).
     *
     * @return array{presente: bool, pontual: bool, biblia: bool, uniforme: bool}
     */
    private function unidadesParaColunas(int $unidades): array
    {
        $presente = $pontual = $biblia = $uniforme = false;
        $r = $unidades;

        if ($r >= 2) {
            $presente = true;
            $r -= 2;
        }
        if ($r >= 2) {
            $uniforme = true;
            $r -= 2;
        }
        if ($r >= 1) {
            $pontual = true;
            $r -= 1;
        }
        if ($r >= 1) {
            $biblia = true;
            $r -= 1;
        }

        return compact('presente', 'pontual', 'biblia', 'uniforme');
    }

    private function semearEventos(int $clubId, array $def, Collection $desbravadores): void
    {
        $assoc = $def['associacao'];

        $lista = [
            ['nome' => 'Acampamento de Instrução', 'local' => 'Chácara Oliveira', 'valor' => 120.00, 'inicio' => '-2 months', 'fim' => '-2 months +2 days'],
            ['nome' => 'Caminhada Noturna', 'local' => 'Trilha do Morro', 'valor' => 0.00, 'inicio' => '-1 month', 'fim' => '-1 month'],
            ['nome' => "Campori da {$assoc}", 'local' => 'Parque de Acampamentos', 'valor' => 280.00, 'inicio' => '+1 month', 'fim' => '+1 month +4 days'],
            ['nome' => 'Investidura de Classes', 'local' => 'Igreja Central', 'valor' => 15.00, 'inicio' => '+2 months', 'fim' => '+2 months'],
            ['nome' => 'Dia Mundial dos Desbravadores', 'local' => 'Ginásio de Esportes', 'valor' => 0.00, 'inicio' => '+5 months', 'fim' => '+5 months'],
        ];

        foreach ($lista as $evt) {
            $evento = Evento::create([
                'nome' => $evt['nome'],
                'local' => $evt['local'],
                'valor' => $evt['valor'],
                'data_inicio' => date('Y-m-d H:i:s', strtotime($evt['inicio'])),
                'data_fim' => date('Y-m-d H:i:s', strtotime($evt['fim'])),
                'descricao' => 'Evento oficial do calendário anual.',
                'club_id' => $clubId,
            ]);

            $passado = strtotime($evt['inicio']) < time();
            foreach ($desbravadores as $dbv) {
                if (fake()->boolean($passado ? 80 : 40)) {
                    $evento->desbravadores()->attach($dbv->id, [
                        'pago' => ($evento->valor == 0) || fake()->boolean(60),
                        'autorizacao_entregue' => fake()->boolean(70),
                    ]);
                }
            }
        }
    }

    private function semearFinanceiro(int $clubId, Collection $desbravadores): void
    {
        // Movimentações avulsas de caixa.
        for ($i = 0; $i < 30; $i++) {
            $tipo = fake()->randomElement(['entrada', 'saida']);
            Caixa::create([
                'descricao' => $tipo === 'entrada'
                    ? fake()->randomElement(['Doação', 'Venda de Pizza', 'Cantina', 'Oferta Especial'])
                    : fake()->randomElement(['Material de Secretaria', 'Gás', 'Manutenção Barracas', 'Lanche']),
                'tipo' => $tipo,
                'categoria' => $tipo === 'entrada' ? 'Receitas Diversas' : 'Despesas Operacionais',
                'valor' => fake()->randomFloat(2, 20, 300),
                'data_movimentacao' => fake()->dateTimeBetween('-6 months', 'now'),
                'club_id' => $clubId,
            ]);
        }

        // Mensalidades dos últimos 3 meses.
        $meses = [
            now()->startOfMonth()->subMonths(2),
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth(),
        ];

        foreach ($meses as $data) {
            foreach ($desbravadores as $dbv) {
                $status = fake()->boolean(70) ? 'pago' : 'pendente';
                Mensalidade::firstOrCreate(
                    ['desbravador_id' => $dbv->id, 'mes' => $data->month, 'ano' => $data->year],
                    [
                        'club_id' => $clubId,
                        'valor' => 15.00,
                        'status' => $status,
                        'data_pagamento' => $status === 'pago' ? $data->copy()->addDays(random_int(1, 10)) : null,
                    ]
                );
            }
        }
    }

    private function semearPatrimonio(int $clubId): void
    {
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
                'observacoes' => 'Inventário Inicial '.now()->year,
                'club_id' => $clubId,
            ]);
        }
    }

    private function semearDocumentos(int $clubId, Collection $desbravadores): void
    {
        // 3 atas
        for ($i = 0; $i < 3; $i++) {
            Ata::create([
                'titulo' => 'Reunião Administrativa nº '.($i + 1),
                'tipo' => fake()->randomElement(['Regular', 'Diretoria', 'Planejamento']),
                'data_reuniao' => fake()->dateTimeBetween('-6 months', 'now'),
                'hora_inicio' => '09:00',
                'hora_fim' => '11:00',
                'local' => 'Sede do Clube',
                'secretario_responsavel' => 'Secretaria do Clube',
                'participantes' => 'Diretoria completa e Conselheiros.',
                'conteudo' => fake()->paragraphs(3, true),
                'club_id' => $clubId,
            ]);
        }

        // 3 atos
        for ($i = 0; $i < 3; $i++) {
            Ato::create([
                'numero' => str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT).'/'.now()->year,
                'tipo' => fake()->randomElement(['Nomeação', 'Exoneração']),
                'data' => fake()->dateTimeBetween('-6 months', 'now'),
                'descricao' => fake()->sentence(10),
                'desbravador_id' => $desbravadores->random()->id,
                'club_id' => $clubId,
            ]);
        }
    }
}
