<?php

namespace App\Http\Requests;

use App\Rules\UnidadePertenceAoClube;
use App\Services\ClubContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
            'foto' => ['nullable', 'file', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'consentimento_lgpd' => 'accepted',
            'consentimento_lgpd_responsavel' => 'required|string|max:255',
        ];
    }

    /**
     * Regra de unicidade do CPF — POR CLUBE.
     * O CPF é armazenado criptografado (não-determinístico), então a unique
     * constraint usa cpf_hash (SHA-256 dos dígitos). A closure hasha o input
     * antes de comparar com cpf_hash no banco.
     *
     * @return array<int, mixed>
     */
    protected function cpfRule(): array
    {
        $clubId = ClubContext::currentClubId();

        return [
            'required',
            'string',
            'max:14',
            function ($attribute, $value, $fail) use ($clubId) {
                $hash = hash('sha256', preg_replace('/\D/', '', $value));
                $exists = DB::table('desbravadores')
                    ->where('cpf_hash', $hash)
                    ->where('club_id', $clubId)
                    ->exists();
                if ($exists) {
                    $fail('Este CPF já está cadastrado neste clube.');
                }
            },
        ];
    }
}
