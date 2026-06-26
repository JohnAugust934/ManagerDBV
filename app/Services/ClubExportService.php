<?php

namespace App\Services;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use Illuminate\Support\Facades\DB;

/**
 * Exporta TODOS os dados de um único clube para um array serializável (JSON),
 * incluindo tabelas relacionais e pivôs, de forma a permitir reimportação
 * (consolidação) com remapeamento de IDs via ClubImportService.
 *
 * Referências a catálogo GLOBAL (classes, especialidades, requisitos) são
 * acompanhadas de uma seção `_catalogo` com chaves naturais, para que o destino
 * resolva os IDs corretos mesmo que difiram entre instalações.
 *
 * Usa withoutGlobalScopes()/DB direto deliberadamente: o filtro de clube é
 * sempre explícito por club_id (roda tanto para platform admin quanto master).
 */
class ClubExportService
{
    public const FORMAT_VERSION = 2;

    public function export(Club $club): array
    {
        $clubId = $club->id;

        // club_id direto (Fase 1) em vez de whereHas('unidade'/'desbravador.unidade'):
        // a subquery da relação aplicaria o ClubScope do Unidade/Desbravador e, sob
        // impersonação de outro clube, exportaria dados errados.
        $desbravadorIds = Desbravador::withoutGlobalScopes()->where('club_id', $clubId)->pluck('id');

        $eventoIds = Evento::withoutGlobalScopes()->where('club_id', $clubId)->pluck('id');
        $patrimonioIds = Patrimonio::withoutGlobalScopes()->where('club_id', $clubId)->pluck('id');

        $frequencias = DB::table('frequencias')->whereIn('desbravador_id', $desbravadorIds)->get();
        $frequenciaIds = $frequencias->pluck('id');

        $desbEspecialidade = DB::table('desbravador_especialidade')->whereIn('desbravador_id', $desbravadorIds)->get();
        $desbRequisito = DB::table('desbravador_requisito')->whereIn('desbravador_id', $desbravadorIds)->get();

        return [
            'meta' => [
                'format_version' => self::FORMAT_VERSION,
                'exported_at' => now()->toIso8601String(),
                'club_id' => $clubId,
                'club_nome' => $club->nome,
                'versao' => config('app.version', '1.0'),
            ],
            'club' => $club->toArray(),
            // DB direto (não o model) para incluir o hash de senha — necessário para
            // reimportar usuários sem perder o login. O arquivo é restrito a admins.
            // remember_token é removido: é um token de sessão ativo e não deve sair
            // no export (sequestro de sessão se o arquivo vazar); o import o ignora.
            'users' => $this->rows(
                DB::table('users')->where('club_id', $clubId)->get()
                    ->map(function ($u) {
                        unset($u->remember_token);

                        return $u;
                    })
            ),
            'attendance_columns' => AttendanceColumn::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'unidades' => Unidade::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'desbravadores' => Desbravador::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'frequencias' => $this->rows($frequencias),
            'frequencia_column_values' => $this->rows(
                DB::table('frequencia_column_values')->whereIn('frequencia_id', $frequenciaIds)->get()
            ),
            'caixas' => Caixa::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'mensalidades' => Mensalidade::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'eventos' => Evento::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'desbravador_evento' => $this->rows(
                DB::table('desbravador_evento')->whereIn('desbravador_id', $desbravadorIds)->get()
            ),
            'atas' => Ata::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'atos' => Ato::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'patrimonios' => Patrimonio::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'patrimonio_manutencoes' => $this->rows(
                DB::table('patrimonio_manutencoes')->whereIn('patrimonio_id', $patrimonioIds)->get()
            ),
            'ranking_snapshots' => RankingSnapshot::withoutGlobalScopes()->where('club_id', $clubId)->get()->toArray(),
            'desbravador_especialidade' => $this->rows($desbEspecialidade),
            'desbravador_requisito' => $this->rows($desbRequisito),

            // Resolução de catálogo global (chaves naturais) para os IDs referenciados.
            '_catalogo' => $this->catalogo($desbravadorIds, $desbEspecialidade, $desbRequisito),
        ];
    }

    public function filename(Club $club): string
    {
        return 'export-'.str($club->nome)->slug().'-'.now()->format('Y-m-d').'.json';
    }

    /**
     * Mapas de chaves naturais para os IDs de catálogo usados por este clube,
     * permitindo ao importador resolver os IDs equivalentes no destino.
     */
    private function catalogo($desbravadorIds, $desbEspecialidade, $desbRequisito): array
    {
        $classeIds = DB::table('desbravadores')
            ->whereIn('id', $desbravadorIds)
            ->whereNotNull('classe_atual')
            ->pluck('classe_atual')
            ->unique();

        $requisitoIds = $desbRequisito->pluck('requisito_id')->unique();
        $requisitos = DB::table('requisitos')->whereIn('id', $requisitoIds)->get();

        // Os requisitos referenciam classes (precisamos do nome da classe para resolver).
        $classeIds = $classeIds->merge($requisitos->pluck('classe_id'))->unique();

        $especialidadeIds = $desbEspecialidade->pluck('especialidade_id')->unique();

        return [
            'classes' => DB::table('classes')->whereIn('id', $classeIds)
                ->get()->mapWithKeys(fn ($c) => [$c->id => ['nome' => $c->nome]])->all(),
            'especialidades' => DB::table('especialidades')->whereIn('id', $especialidadeIds)
                ->get()->mapWithKeys(fn ($e) => [$e->id => [
                    'codigo' => $e->codigo ?? null,
                    'nome' => $e->nome,
                    'area' => $e->area,
                ]])->all(),
            'requisitos' => $requisitos->mapWithKeys(fn ($r) => [$r->id => [
                'classe_id' => $r->classe_id,
                'codigo' => $r->codigo ?? null,
                'descricao' => $r->descricao,
            ]])->all(),
        ];
    }

    /** Converte uma coleção de linhas (stdClass) do query builder em arrays. */
    private function rows($collection): array
    {
        return $collection->map(fn ($row) => (array) $row)->all();
    }
}
