<?php

namespace App\Http\Requests;

use App\Services\ClubContext;
use Illuminate\Validation\Rule;

class UpdateDesbravadorRequest extends StoreDesbravadorRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'ativo' => 'boolean',
            // Consentimento já foi registrado no cadastro — não exigir novamente na edição
            'consentimento_lgpd' => 'nullable|boolean',
            'consentimento_lgpd_responsavel' => 'nullable|string|max:255',
        ]);
    }

    /**
     * Ignora o proprio desbravador na checagem de unicidade do CPF.
     *
     * @return array<int, mixed>
     */
    protected function cpfRule(): array
    {
        return ['required', 'string', 'max:14',
            Rule::unique('desbravadores', 'cpf')
                ->where('club_id', ClubContext::currentClubId())
                ->ignore($this->route('desbravador'))];
    }
}
