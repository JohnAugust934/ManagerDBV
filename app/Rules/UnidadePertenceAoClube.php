<?php

namespace App\Rules;

use App\Models\Unidade;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Garante que a unidade informada pertence ao clube do usuario autenticado.
 *
 * Substitui a closure de validacao que estava duplicada em store()/update()
 * do DesbravadorController.
 */
class UnidadePertenceAoClube implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $clubId = auth()->user()?->club_id;

        if (! Unidade::where('id', $value)->where('club_id', $clubId)->exists()) {
            $fail('A unidade selecionada não pertence ao seu clube.');
        }
    }
}
