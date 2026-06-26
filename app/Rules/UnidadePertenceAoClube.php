<?php

namespace App\Rules;

use App\Models\Unidade;
use App\Services\ClubContext;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Garante que a unidade informada pertence ao clube ativo.
 *
 * Usa ClubContext (não auth()->user()->club_id) para que o platform admin em
 * modo suporte (impersonação) valide contra o clube que está atendendo — um
 * platform admin tem club_id = null e, do contrário, falharia em toda unidade.
 *
 * Substitui a closure de validacao que estava duplicada em store()/update()
 * do DesbravadorController.
 */
class UnidadePertenceAoClube implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $clubId = ClubContext::currentClubId();

        if (! $clubId || ! Unidade::where('id', $value)->where('club_id', $clubId)->exists()) {
            $fail('A unidade selecionada não pertence ao seu clube.');
        }
    }
}
