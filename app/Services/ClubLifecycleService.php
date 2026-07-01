<?php

namespace App\Services;

use App\Models\Club;
use App\Support\TenantTables;
use Illuminate\Support\Facades\DB;

/**
 * Exclusão DEFINITIVA de um clube e de todos os seus dados.
 *
 * Faz a remoção EXPLÍCITA por club_id, em ordem de dependência, dentro de uma
 * transação — portável nos três bancos (SQLite/MySQL/Postgres) e sem depender do
 * ON DELETE CASCADE da FK de club_id (que o SQLite não possui). A lista de
 * tabelas vem do registro único App\Support\TenantTables (ver Candidato B).
 */
class ClubLifecycleService
{
    public function delete(Club $club): void
    {
        $id = $club->id;

        DB::transaction(function () use ($club, $id) {
            // Pivôs/filhos SEM club_id próprio — removidos pela referência ao pai.
            foreach (TenantTables::childTables() as $tabela => $rel) {
                $parentIds = DB::table($rel['via'])->where('club_id', $id)->pluck('id');
                DB::table($tabela)->whereIn($rel['fk'], $parentIds)->delete();
            }

            // Tabelas com club_id direto, em ordem de dependência.
            foreach (TenantTables::clubIdTables() as $tabela) {
                DB::table($tabela)->where('club_id', $id)->delete();
            }

            // Usuários do clube (platform admins têm club_id null e não entram aqui).
            DB::table('users')->where('club_id', $id)->delete();

            $club->delete();
        });
    }
}
