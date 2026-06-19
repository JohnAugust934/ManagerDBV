<?php

namespace App\Services;

use App\Models\Club;
use Illuminate\Support\Facades\DB;

/**
 * Exclusão DEFINITIVA de um clube e de todos os seus dados.
 *
 * Faz a remoção EXPLÍCITA por club_id, em ordem de dependência, dentro de uma
 * transação — portável nos três bancos (SQLite/MySQL/Postgres) e sem depender do
 * ON DELETE CASCADE da FK de club_id (que o SQLite não possui). Os pivôs sem
 * club_id próprio são removidos pelas FKs naturais (desbravador/frequência).
 */
class ClubLifecycleService
{
    /** Tabelas com club_id direto, em ordem segura de exclusão (filhas antes das mães). */
    private const TABELAS_CLUB_ID = [
        'frequencias',
        'mensalidades',
        'atos',          // referencia desbravador_id → antes de desbravadores
        'desbravadores', // antes de unidades (unidade_id) e depois de seus filhos
        'eventos',
        'caixas',
        'patrimonios',
        'atas',
        'attendance_columns',
        'ranking_snapshots',
        'invitations',
        'unidades',
    ];

    public function delete(Club $club): void
    {
        $id = $club->id;

        DB::transaction(function () use ($club, $id) {
            $dbvIds = DB::table('desbravadores')->where('club_id', $id)->pluck('id');
            $freqIds = DB::table('frequencias')->where('club_id', $id)->pluck('id');
            $patrIds = DB::table('patrimonios')->where('club_id', $id)->pluck('id');

            // Pivôs/filhos SEM club_id próprio — removidos pela referência ao pai.
            DB::table('frequencia_column_values')->whereIn('frequencia_id', $freqIds)->delete();
            DB::table('desbravador_evento')->whereIn('desbravador_id', $dbvIds)->delete();
            DB::table('desbravador_especialidade')->whereIn('desbravador_id', $dbvIds)->delete();
            DB::table('desbravador_requisito')->whereIn('desbravador_id', $dbvIds)->delete();
            DB::table('patrimonio_manutencoes')->whereIn('patrimonio_id', $patrIds)->delete();

            // Tabelas com club_id direto, em ordem de dependência.
            foreach (self::TABELAS_CLUB_ID as $tabela) {
                DB::table($tabela)->where('club_id', $id)->delete();
            }

            // Usuários do clube (platform admins têm club_id null e não entram aqui).
            DB::table('users')->where('club_id', $id)->delete();

            $club->delete();
        });
    }
}
