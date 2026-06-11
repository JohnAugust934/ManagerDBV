<?php

namespace App\Http\Requests;

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
        ]);
    }

    /**
     * Ignora o proprio desbravador na checagem de unicidade do CPF.
     *
     * @return array<int, mixed>
     */
    protected function cpfRule(): array
    {
        return ['required', 'string', 'max:14', Rule::unique('desbravadores', 'cpf')->ignore($this->route('desbravador'))];
    }
}
