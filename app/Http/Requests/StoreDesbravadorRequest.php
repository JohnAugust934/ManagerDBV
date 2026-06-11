<?php

namespace App\Http\Requests;

use App\Rules\UnidadePertenceAoClube;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDesbravadorRequest extends FormRequest
{
    /**
     * A autorizacao permanece no middleware de rota (can:secretaria).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'sexo' => 'required|in:M,F',
            'cpf' => $this->cpfRule(),
            'rg' => 'nullable|string|max:20',
            'unidade_id' => ['required', 'exists:unidades,id', new UnidadePertenceAoClube],
            'classe_atual' => 'nullable|exists:classes,id',
            'email' => 'required|email',
            'telefone' => 'nullable|string',
            'endereco' => 'required|string|max:500',
            'nome_responsavel' => 'required|string|max:255',
            'telefone_responsavel' => 'required|string',
            'numero_sus' => 'required|string|max:50',
            'tipo_sanguineo' => 'nullable|string|max:3',
            'alergias' => 'nullable|string',
            'medicamentos_continuos' => 'nullable|string',
            'plano_saude' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }

    /**
     * Regra de unicidade do CPF. O update sobrescreve para ignorar o proprio registro.
     *
     * @return array<int, mixed>
     */
    protected function cpfRule(): array
    {
        return ['required', 'string', 'max:14', Rule::unique('desbravadores', 'cpf')];
    }
}
