<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Especialidade;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubExportService;
use App\Services\ClubImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClubExportImportRoundTripTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_import_preserva_todos_os_dados_do_clube(): void
    {
        // --- Catálogo global (compartilhado) ---
        $classeId = DB::table('classes')->insertGetId(['nome' => 'Amigo', 'cor' => '#fff', 'ordem' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $requisitoId = DB::table('requisitos')->insertGetId(['classe_id' => $classeId, 'codigo' => 'R1', 'descricao' => 'Decorar o voto', 'categoria' => 'Gerais', 'created_at' => now(), 'updated_at' => now()]);
        $especialidade = Especialidade::create(['nome' => 'Nós e Amarras', 'area' => 'Atividades Manuais', 'codigo' => 'AM-01']);

        // --- Clube de origem, com pelo menos 1 linha em cada tabela ---
        $club = Club::create(['nome' => 'Clube Origem', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $master = User::factory()->create(['club_id' => $club->id, 'role' => 'master', 'email' => 'origem@clube.com']);
        $unidade = Unidade::factory()->create(['club_id' => $club->id, 'conselheiro_user_id' => $master->id]);

        $dbv = Desbravador::create([
            'nome' => 'Joãozinho', 'ativo' => true, 'data_nascimento' => '2012-01-01', 'sexo' => 'M',
            'unidade_id' => $unidade->id, 'classe_atual' => $classeId,
        ]);
        $dbv->especialidades()->attach($especialidade->id, ['data_conclusao' => now()->toDateString()]);
        DB::table('desbravador_requisito')->insert(['desbravador_id' => $dbv->id, 'requisito_id' => $requisitoId, 'user_id' => $master->id, 'data_conclusao' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);

        $coluna = AttendanceColumn::create(['club_id' => $club->id, 'key' => 'presente', 'name' => 'Presente', 'points' => 10, 'is_fixed' => true, 'is_active' => true, 'sort_order' => 1]);
        $freq = Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()->toDateString(), 'presente' => true]);
        DB::table('frequencia_column_values')->insert(['frequencia_id' => $freq->id, 'attendance_column_id' => $coluna->id, 'checked' => true, 'points_awarded' => 10, 'created_at' => now(), 'updated_at' => now()]);

        Caixa::create(['descricao' => 'Doação', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'Doações', 'club_id' => $club->id]);
        Mensalidade::create(['desbravador_id' => $dbv->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);

        $evento = Evento::create(['nome' => 'Acampamento', 'data_inicio' => now(), 'data_fim' => now()->addDay(), 'local' => 'Sítio', 'valor' => 50, 'club_id' => $club->id]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => true, 'autorizacao_entregue' => false]);

        Ata::create(['titulo' => 'Ata 1', 'data_reuniao' => now(), 'tipo' => 'Regular', 'hora_inicio' => '09:00', 'hora_fim' => '11:00', 'local' => 'Sede', 'conteudo' => 'X', 'club_id' => $club->id]);
        Ato::create(['numero' => '001/2026', 'data' => now(), 'tipo' => 'Nomeação', 'descricao' => 'Y', 'desbravador_id' => $dbv->id, 'club_id' => $club->id]);

        $patrimonio = Patrimonio::create(['item' => 'Barraca', 'quantidade' => 2, 'valor_estimado' => 300, 'estado_conservacao' => 'Bom', 'local_armazenamento' => 'Sede', 'club_id' => $club->id]);
        DB::table('patrimonio_manutencoes')->insert(['patrimonio_id' => $patrimonio->id, 'user_id' => $master->id, 'data' => now()->toDateString(), 'estado_novo' => 'Ótimo', 'descricao' => 'Reparo', 'created_at' => now(), 'updated_at' => now()]);

        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $club->id, 'generated_by' => $master->id, 'entries' => [['id' => 1]], 'generated_at' => now()]);

        // --- Export → Import ---
        $payload = (new ClubExportService)->export($club->fresh());
        $report = (new ClubImportService)->import($payload, 'Clube Importado');

        // Novo clube criado e distinto.
        $newClubId = $report['club_id'];
        $this->assertNotSame($club->id, $newClubId);
        $this->assertDatabaseHas('clubs', ['id' => $newClubId, 'nome' => 'Clube Importado']);

        // Contagens dos dados tenant.
        $c = $report['counts'];
        $this->assertSame(1, $c['unidades']);
        $this->assertSame(1, $c['desbravadores']);
        $this->assertSame(1, $c['frequencias']);
        $this->assertSame(1, $c['frequencia_column_values']);
        $this->assertSame(1, $c['caixas']);
        $this->assertSame(1, $c['mensalidades']);
        $this->assertSame(1, $c['eventos']);
        $this->assertSame(1, $c['desbravador_evento']);
        $this->assertSame(1, $c['atas']);
        $this->assertSame(1, $c['atos']);
        $this->assertSame(1, $c['patrimonios']);
        $this->assertSame(1, $c['patrimonio_manutencoes']);
        $this->assertSame(1, $c['ranking_snapshots']);
        $this->assertSame(1, $c['attendance_columns']);
        $this->assertSame(1, $c['desbravador_especialidade']);
        $this->assertSame(1, $c['desbravador_requisito']);

        // Relações remapeadas: desbravador importado na unidade importada, classe e especialidade resolvidas.
        $novaUnidade = DB::table('unidades')->where('club_id', $newClubId)->first();
        $novoDbv = DB::table('desbravadores')->where('unidade_id', $novaUnidade->id)->first();
        $this->assertSame('Joãozinho', $novoDbv->nome);
        $this->assertSame($classeId, (int) $novoDbv->classe_atual); // catálogo resolvido por nome

        $this->assertDatabaseHas('desbravador_especialidade', ['desbravador_id' => $novoDbv->id, 'especialidade_id' => $especialidade->id]);
        $this->assertDatabaseHas('desbravador_requisito', ['desbravador_id' => $novoDbv->id, 'requisito_id' => $requisitoId]);

        // Tudo duplicado no banco (origem + importado), nada perdido.
        $this->assertSame(2, Desbravador::withoutGlobalScopes()->count());
        $this->assertSame(2, Frequencia::count());

        // Catálogo não resolvido não gerou avisos.
        $this->assertEmpty(array_filter($report['warnings'], fn ($w) => str_contains($w, 'não encontrada')));
    }

    public function test_campos_sensiveis_sao_recriptografados_e_reexport_nao_quebra(): void
    {
        // Regressão: numero_sus (cast 'encrypted') não era recriptografado no import,
        // ficava em plaintext e quebrava o re-export/backup com "The payload is invalid.".
        $club = Club::create(['nome' => 'Clube Origem', 'cidade' => 'SP']);
        $unidade = Unidade::factory()->create(['club_id' => $club->id]);

        $dbv = Desbravador::create([
            'nome' => 'Maria', 'ativo' => true, 'data_nascimento' => '2012-01-01', 'sexo' => 'F',
            'unidade_id' => $unidade->id,
            'cpf' => '12345678909',
            'rg' => '11.222.333-4',
            'numero_sus' => '700123456789012',
            'alergias' => 'Amendoim',
            'medicamentos_continuos' => 'Nenhum',
            'plano_saude' => 'Unimed',
        ]);

        $payload = (new ClubExportService)->export($club->fresh());
        $report = (new ClubImportService)->import($payload, 'Clube Importado');
        $newClubId = $report['club_id'];

        // Os valores cifrados no destino devem descriptografar para o plaintext original.
        $novoDbv = Desbravador::withoutGlobalScopes()->where('club_id', $newClubId)->first();
        $this->assertSame('11.222.333-4', $novoDbv->rg);
        $this->assertSame('700123456789012', $novoDbv->numero_sus);
        $this->assertSame('Amendoim', $novoDbv->alergias);
        $this->assertSame('Unimed', $novoDbv->plano_saude);
        $this->assertSame('12345678909', $novoDbv->cpf);

        // Re-exportar o clube importado não pode lançar DecryptException.
        $reexport = (new ClubExportService)->export(Club::find($newClubId));
        $this->assertNotEmpty($reexport['desbravadores']);
        $this->assertSame('700123456789012', $reexport['desbravadores'][0]['numero_sus']);
    }

    public function test_comando_reparar_cifrados_recripta_plaintext_e_e_idempotente(): void
    {
        $club = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $unidade = Unidade::factory()->create(['club_id' => $club->id]);
        $dbv = Desbravador::create([
            'nome' => 'Ana', 'ativo' => true, 'data_nascimento' => '2012-01-01', 'sexo' => 'F',
            'unidade_id' => $unidade->id, 'rg' => 'cifrado-ok',
        ]);

        // Simula os dois defeitos das importações antigas:
        //  - numero_sus em PLAINTEXT direto na coluna;
        //  - alergias cifrado com o helper encrypt() (serializa) → wrapper "s:N:..." ao ler.
        DB::table('desbravadores')->where('id', $dbv->id)->update([
            'numero_sus' => '700999888777666',
            'alergias' => encrypt('Poeira'),
        ]);

        // Antes do reparo: plaintext quebra; wrapper retorna serializado.
        try {
            Desbravador::withoutGlobalScopes()->find($dbv->id)->numero_sus;
            $this->fail('Esperava DecryptException no valor plaintext.');
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            // esperado
        }
        $this->assertSame('s:6:"Poeira";', Desbravador::withoutGlobalScopes()->find($dbv->id)->alergias);

        $this->artisan('desbravadores:reparar-cifrados')->assertSuccessful();

        $reparado = Desbravador::withoutGlobalScopes()->find($dbv->id);
        $this->assertSame('700999888777666', $reparado->numero_sus);
        $this->assertSame('Poeira', $reparado->alergias);
        $this->assertSame('cifrado-ok', $reparado->rg);

        // Idempotente: rodar de novo não altera nada (0 campos).
        $this->artisan('desbravadores:reparar-cifrados --dry-run')
            ->expectsOutputToContain('0 campo(s) normalizado(s)')
            ->assertSuccessful();
    }

    public function test_export_nao_inclui_remember_token_dos_usuarios(): void
    {
        $club = Club::create(['nome' => 'Clube Origem', 'cidade' => 'SP']);
        User::factory()->create([
            'club_id' => $club->id,
            'role' => 'master',
            'email' => 'master@clube.com',
            'remember_token' => 'token-de-sessao-ativo',
        ]);

        $payload = (new ClubExportService)->export($club->fresh());

        // remember_token é token de sessão ativo: não pode sair no export (risco
        // de sequestro de sessão se o arquivo vazar). password (hash) é mantido.
        $this->assertNotEmpty($payload['users']);
        foreach ($payload['users'] as $user) {
            $this->assertArrayNotHasKey('remember_token', $user);
            $this->assertArrayHasKey('password', $user);
        }
    }

    public function test_usuario_existente_nao_e_duplicado_na_importacao(): void
    {
        $club = Club::create(['nome' => 'Clube Origem', 'cidade' => 'SP']);
        $master = User::factory()->create(['club_id' => $club->id, 'role' => 'master', 'email' => 'unico@clube.com']);
        Unidade::factory()->create(['club_id' => $club->id]);

        $payload = (new ClubExportService)->export($club->fresh());
        $report = (new ClubImportService)->import($payload, 'Importado');

        // E-mail já existe (mesmo banco) → não duplica usuário.
        $this->assertSame(1, User::where('email', 'unico@clube.com')->count());
        $this->assertSame(0, $report['counts']['users']);
    }
}
