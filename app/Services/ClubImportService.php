<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Importa um JSON gerado pelo ClubExportService como um NOVO clube, remapeando
 * todas as chaves primárias/estrangeiras (tenant) e resolvendo referências de
 * catálogo GLOBAL (classes, especialidades, requisitos) por chave natural.
 *
 * Tudo roda numa transação. Retorna um relatório com contagens e avisos.
 *
 * Limitações conhecidas:
 *  - O binário do logo do clube não é migrado (apenas dados do banco).
 *  - Usuários com e-mail já existente no destino NÃO são duplicados: as relações
 *    apontam para o usuário existente (e-mail é a chave de login, global).
 *  - Referências de catálogo não encontradas no destino são puladas (com aviso);
 *    o catálogo (classes/especialidades) deve ter sido semeado no destino.
 */
class ClubImportService
{
    private array $warnings = [];

    private array $counts = [];

    /**
     * Importa apenas os filhos de um clube já existente (usado pelo ClubRestoreService).
     * O clube alvo deve ter sido limpo (deleteClubChildren) antes de chamar este método.
     */
    public function importChildren(array $data, int $existingClubId): array
    {
        $this->warnings = [];
        $this->counts = [];

        $catalogo = $data['_catalogo'] ?? ['classes' => [], 'especialidades' => [], 'requisitos' => []];

        $userMap = $this->importUsers($data['users'] ?? [], $existingClubId);
        $acMap = $this->importSimple('attendance_columns', $data['attendance_columns'] ?? [], ['club_id' => $existingClubId]);
        $unidadeMap = $this->importUnidades($data['unidades'] ?? [], $existingClubId, $userMap);
        $dbvMap = $this->importDesbravadores($data['desbravadores'] ?? [], $unidadeMap, $userMap, $catalogo, $existingClubId);

        $freqMap = $this->importEach('frequencias', $data['frequencias'] ?? [], fn ($r) => [
            'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
            'club_id' => $existingClubId,
        ], requiredKeys: ['desbravador_id']);

        $this->importEach('frequencia_column_values', $data['frequencia_column_values'] ?? [], fn ($r) => [
            'frequencia_id' => $freqMap[$r['frequencia_id']] ?? null,
            'attendance_column_id' => $acMap[$r['attendance_column_id']] ?? null,
        ], requiredKeys: ['frequencia_id', 'attendance_column_id']);

        $this->importSimple('caixas', $data['caixas'] ?? [], ['club_id' => $existingClubId], remap: fn ($r) => [
            'created_by' => $userMap[$r['created_by'] ?? null] ?? null,
            'updated_by' => $userMap[$r['updated_by'] ?? null] ?? null,
        ]);

        $this->importEach('mensalidades', $data['mensalidades'] ?? [], fn ($r) => [
            'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
            'club_id' => $existingClubId,
        ], requiredKeys: ['desbravador_id']);

        $eventoMap = $this->importSimple('eventos', $data['eventos'] ?? [], ['club_id' => $existingClubId]);

        $this->importEach('desbravador_evento', $data['desbravador_evento'] ?? [], fn ($r) => [
            'evento_id' => $eventoMap[$r['evento_id']] ?? null,
            'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
        ], requiredKeys: ['evento_id', 'desbravador_id']);

        $this->importSimple('atas', $data['atas'] ?? [], ['club_id' => $existingClubId]);

        $this->importSimple('atos', $data['atos'] ?? [], ['club_id' => $existingClubId], remap: fn ($r) => [
            'desbravador_id' => $dbvMap[$r['desbravador_id'] ?? null] ?? null,
        ]);

        $patrMap = $this->importSimple('patrimonios', $data['patrimonios'] ?? [], ['club_id' => $existingClubId]);

        $this->importEach('patrimonio_manutencoes', $data['patrimonio_manutencoes'] ?? [], fn ($r) => [
            'patrimonio_id' => $patrMap[$r['patrimonio_id']] ?? null,
            'user_id' => $userMap[$r['user_id'] ?? null] ?? null,
        ], requiredKeys: ['patrimonio_id']);

        $this->importSimple('ranking_snapshots', $data['ranking_snapshots'] ?? [], ['club_id' => $existingClubId], remap: fn ($r) => [
            'generated_by' => $userMap[$r['generated_by'] ?? null] ?? null,
        ]);

        $this->importEach('desbravador_especialidade', $data['desbravador_especialidade'] ?? [], fn ($r) => [
            'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
            'especialidade_id' => $this->resolveEspecialidade($r['especialidade_id'], $catalogo),
        ], requiredKeys: ['desbravador_id', 'especialidade_id']);

        $this->importEach('desbravador_requisito', $data['desbravador_requisito'] ?? [], fn ($r) => [
            'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
            'requisito_id' => $this->resolveRequisito($r['requisito_id'], $catalogo),
            'user_id' => $userMap[$r['user_id'] ?? null] ?? null,
        ], requiredKeys: ['desbravador_id', 'requisito_id']);

        return [
            'club_id' => $existingClubId,
            'counts' => $this->counts,
            'warnings' => $this->warnings,
        ];
    }

    public function import(array $data, ?string $nomeOverride = null): array
    {
        $this->validate($data);

        return DB::transaction(function () use ($data, $nomeOverride) {
            $this->warnings = [];
            $this->counts = [];

            $catalogo = $data['_catalogo'] ?? ['classes' => [], 'especialidades' => [], 'requisitos' => []];

            $newClubId = $this->importClub($data['club'] ?? [], $nomeOverride);
            $userMap = $this->importUsers($data['users'] ?? [], $newClubId);
            $acMap = $this->importSimple('attendance_columns', $data['attendance_columns'] ?? [], ['club_id' => $newClubId]);
            $unidadeMap = $this->importUnidades($data['unidades'] ?? [], $newClubId, $userMap);
            $dbvMap = $this->importDesbravadores($data['desbravadores'] ?? [], $unidadeMap, $userMap, $catalogo, $newClubId);

            $freqMap = $this->importEach('frequencias', $data['frequencias'] ?? [], fn ($r) => [
                'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
                'club_id' => $newClubId,
            ], requiredKeys: ['desbravador_id']);

            $this->importEach('frequencia_column_values', $data['frequencia_column_values'] ?? [], fn ($r) => [
                'frequencia_id' => $freqMap[$r['frequencia_id']] ?? null,
                'attendance_column_id' => $acMap[$r['attendance_column_id']] ?? null,
            ], requiredKeys: ['frequencia_id', 'attendance_column_id']);

            $this->importSimple('caixas', $data['caixas'] ?? [], [
                'club_id' => $newClubId,
            ], remap: fn ($r) => [
                'created_by' => $userMap[$r['created_by'] ?? null] ?? null,
                'updated_by' => $userMap[$r['updated_by'] ?? null] ?? null,
            ]);

            $this->importEach('mensalidades', $data['mensalidades'] ?? [], fn ($r) => [
                'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
                'club_id' => $newClubId,
            ], requiredKeys: ['desbravador_id']);

            $eventoMap = $this->importSimple('eventos', $data['eventos'] ?? [], ['club_id' => $newClubId]);

            $this->importEach('desbravador_evento', $data['desbravador_evento'] ?? [], fn ($r) => [
                'evento_id' => $eventoMap[$r['evento_id']] ?? null,
                'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
            ], requiredKeys: ['evento_id', 'desbravador_id']);

            $this->importSimple('atas', $data['atas'] ?? [], ['club_id' => $newClubId]);

            $this->importSimple('atos', $data['atos'] ?? [], ['club_id' => $newClubId], remap: fn ($r) => [
                'desbravador_id' => $dbvMap[$r['desbravador_id'] ?? null] ?? null,
            ]);

            $patrMap = $this->importSimple('patrimonios', $data['patrimonios'] ?? [], ['club_id' => $newClubId]);

            $this->importEach('patrimonio_manutencoes', $data['patrimonio_manutencoes'] ?? [], fn ($r) => [
                'patrimonio_id' => $patrMap[$r['patrimonio_id']] ?? null,
                'user_id' => $userMap[$r['user_id'] ?? null] ?? null,
            ], requiredKeys: ['patrimonio_id']);

            $this->importSimple('ranking_snapshots', $data['ranking_snapshots'] ?? [], ['club_id' => $newClubId], remap: fn ($r) => [
                'generated_by' => $userMap[$r['generated_by'] ?? null] ?? null,
            ]);

            $this->importEach('desbravador_especialidade', $data['desbravador_especialidade'] ?? [], fn ($r) => [
                'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
                'especialidade_id' => $this->resolveEspecialidade($r['especialidade_id'], $catalogo),
            ], requiredKeys: ['desbravador_id', 'especialidade_id']);

            $this->importEach('desbravador_requisito', $data['desbravador_requisito'] ?? [], fn ($r) => [
                'desbravador_id' => $dbvMap[$r['desbravador_id']] ?? null,
                'requisito_id' => $this->resolveRequisito($r['requisito_id'], $catalogo),
                'user_id' => $userMap[$r['user_id'] ?? null] ?? null,
            ], requiredKeys: ['desbravador_id', 'requisito_id']);

            return [
                'club_id' => $newClubId,
                'counts' => $this->counts,
                'warnings' => $this->warnings,
            ];
        });
    }

    private function validate(array $data): void
    {
        if (! isset($data['club']) || ! isset($data['meta'])) {
            throw new RuntimeException('Arquivo de importação inválido: faltam as seções meta/club.');
        }
    }

    private function importClub(array $club, ?string $nomeOverride): int
    {
        $id = DB::table('clubs')->insertGetId([
            'nome' => $nomeOverride ?: ($club['nome'] ?? 'Clube importado'),
            'cidade' => $club['cidade'] ?? null,
            'associacao' => $club['associacao'] ?? null,
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->counts['clubs'] = 1;

        return $id;
    }

    /** @return array<int,int> oldUserId => newUserId */
    private function importUsers(array $users, int $newClubId): array
    {
        $map = [];
        $created = 0;
        foreach ($users as $u) {
            $existing = DB::table('users')->where('email', $u['email'])->first();
            if ($existing) {
                $map[$u['id']] = $existing->id;
                $this->warnings[] = "Usuário {$u['email']} já existe — relações apontarão para o existente.";

                continue;
            }

            $row = $this->scrub($u);
            $row['club_id'] = $newClubId;
            $row['is_platform_admin'] = false; // importado nunca é super admin
            unset($row['remember_token']);

            $map[$u['id']] = DB::table('users')->insertGetId($row);
            $created++;
        }
        $this->counts['users'] = $created;

        return $map;
    }

    /** @return array<int,int> */
    private function importUnidades(array $unidades, int $newClubId, array $userMap): array
    {
        return $this->importSimple('unidades', $unidades, ['club_id' => $newClubId], remap: fn ($r) => [
            'conselheiro_user_id' => $userMap[$r['conselheiro_user_id'] ?? null] ?? null,
        ]);
    }

    /** @return array<int,int> */
    private function importDesbravadores(array $desbravadores, array $unidadeMap, array $userMap, array $catalogo, int $newClubId): array
    {
        $map = [];
        $count = 0;
        foreach ($desbravadores as $d) {
            $novaUnidade = $unidadeMap[$d['unidade_id'] ?? null] ?? null;
            if ($novaUnidade === null) {
                $this->warnings[] = "Desbravador '{$d['nome']}' sem unidade mapeada — pulado.";

                continue;
            }

            $row = $this->scrub($d);
            $row['unidade_id'] = $novaUnidade;
            $row['club_id'] = $newClubId;
            $row['classe_atual'] = $this->resolveClasse($d['classe_atual'] ?? null, $catalogo);
            $row['created_by'] = $userMap[$d['created_by'] ?? null] ?? null;
            $row['updated_by'] = $userMap[$d['updated_by'] ?? null] ?? null;

            $map[$d['id']] = DB::table('desbravadores')->insertGetId($row);
            $count++;
        }
        $this->counts['desbravadores'] = $count;

        return $map;
    }

    /**
     * Importa linhas simples remapeando club_id e (opcionalmente) outras FKs.
     *
     * @param  array<string,mixed>  $overrides
     * @param  null|callable(array):array<string,mixed>  $remap
     * @return array<int,int> oldId => newId
     */
    private function importSimple(string $table, array $rows, array $overrides = [], ?callable $remap = null): array
    {
        $map = [];
        $count = 0;
        foreach ($rows as $r) {
            $row = $this->scrub($r);
            foreach ($overrides as $k => $v) {
                $row[$k] = $v;
            }
            if ($remap) {
                foreach ($remap($r) as $k => $v) {
                    $row[$k] = $v;
                }
            }
            $map[$r['id']] = DB::table($table)->insertGetId($row);
            $count++;
        }
        $this->counts[$table] = $count;

        return $map;
    }

    /**
     * Importa linhas exigindo que certas FKs remapeadas não sejam nulas
     * (pula + avisa quando alguma referência obrigatória não resolveu).
     *
     * @param  callable(array):array<string,mixed>  $remap
     * @param  array<int,string>  $requiredKeys
     * @return array<int,int> oldId => newId
     */
    private function importEach(string $table, array $rows, callable $remap, array $requiredKeys = []): array
    {
        $map = [];
        $count = 0;
        $skipped = 0;
        foreach ($rows as $r) {
            $remapped = $remap($r);

            $faltando = false;
            foreach ($requiredKeys as $k) {
                if (($remapped[$k] ?? null) === null) {
                    $faltando = true;
                    break;
                }
            }
            if ($faltando) {
                $skipped++;

                continue;
            }

            $row = $this->scrub($r);
            foreach ($remapped as $k => $v) {
                $row[$k] = $v;
            }

            $map[$r['id']] = DB::table($table)->insertGetId($row);
            $count++;
        }
        $this->counts[$table] = $count;
        if ($skipped > 0) {
            $this->warnings[] = "{$table}: {$skipped} linha(s) puladas por referência não resolvida.";
        }

        return $map;
    }

    /** Resolve a classe (catálogo global) pelo nome no destino. */
    private function resolveClasse($oldId, array $catalogo): ?int
    {
        if ($oldId === null) {
            return null;
        }
        $nome = $catalogo['classes'][$oldId]['nome'] ?? null;
        if ($nome === null) {
            return null;
        }
        $id = DB::table('classes')->where('nome', $nome)->value('id');
        if ($id === null) {
            $this->warnings[] = "Classe '{$nome}' não encontrada no destino — classe do desbravador ficará nula.";
        }

        return $id ? (int) $id : null;
    }

    /** Resolve a especialidade (catálogo) por código, ou nome+área. */
    private function resolveEspecialidade($oldId, array $catalogo): ?int
    {
        $ref = $catalogo['especialidades'][$oldId] ?? null;
        if ($ref === null) {
            return null;
        }

        $query = DB::table('especialidades');
        if (! empty($ref['codigo'])) {
            $id = (clone $query)->where('codigo', $ref['codigo'])->value('id');
            if ($id) {
                return (int) $id;
            }
        }

        $id = $query->where('nome', $ref['nome'])->where('area', $ref['area'])->value('id');

        return $id ? (int) $id : null;
    }

    /** Resolve o requisito (catálogo) por classe + código/descrição. */
    private function resolveRequisito($oldId, array $catalogo): ?int
    {
        $ref = $catalogo['requisitos'][$oldId] ?? null;
        if ($ref === null) {
            return null;
        }

        $classeNome = $catalogo['classes'][$ref['classe_id']]['nome'] ?? null;
        if ($classeNome === null) {
            return null;
        }

        $base = DB::table('requisitos')
            ->join('classes', 'classes.id', '=', 'requisitos.classe_id')
            ->where('classes.nome', $classeNome);

        if (! empty($ref['codigo'])) {
            $id = (clone $base)->where('requisitos.codigo', $ref['codigo'])->value('requisitos.id');
            if ($id) {
                return (int) $id;
            }
        }

        $id = $base->where('requisitos.descricao', $ref['descricao'])->value('requisitos.id');

        return $id ? (int) $id : null;
    }

    /**
     * Remove o id de origem, garante timestamps e serializa valores array (JSON).
     *
     * @return array<string,mixed>
     */
    private function scrub(array $row): array
    {
        unset($row['id']);

        foreach ($row as $k => $v) {
            if (is_array($v)) {
                $row[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }

        $row['created_at'] = $row['created_at'] ?? now();
        $row['updated_at'] = $row['updated_at'] ?? now();

        return $row;
    }
}
