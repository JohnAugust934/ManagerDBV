<?php

namespace App\Http\Requests;

use App\Services\ClubContext;
use Illuminate\Support\Facades\DB;
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
     * Unicidade de CPF (por clube) excluindo o próprio desbravador.
     * Usa cpf_hash (SHA-256) porque o campo cpf está criptografado.
     *
     * @return array<int, mixed>
     */
    protected function cpfRule(): array
    {
        $clubId = ClubContext::currentClubId();
        $desbravador = $this->route('desbravador');
        $excludeId = is_object($desbravador) ? $desbravador->id : (int) $desbravador;

        return [
            'required',
            'string',
            'max:14',
            function ($attribute, $value, $fail) use ($clubId, $excludeId) {
                $hash = hash('sha256', preg_replace('/\D/', '', $value));
                $exists = DB::table('desbravadores')
                    ->where('cpf_hash', $hash)
                    ->where('club_id', $clubId)
                    ->where('id', '!=', $excludeId)
                    ->exists();
                if ($exists) {
                    $fail('Este CPF já está cadastrado neste clube.');
                }
            },
        ];
    }
}
