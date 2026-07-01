<?php

namespace App\Services;

use App\Models\Caixa;

/**
 * Ponto único de lançamento no caixa a partir de eventos de domínio
 * (pagamento de mensalidade, inscrição/estorno de evento).
 *
 * Antes a construção da linha de Caixa (campos, resolução de club_id) estava
 * copiada em MensalidadeController e EventoController. Centralizar aqui evita que
 * as regras de lançamento divirjam e dá um único ponto de teste para o efeito
 * colateral financeiro (ver Candidato C).
 */
class LancamentoCaixaService
{
    public function entrada(string $categoria, string $descricao, $valor, ?int $clubId = null): Caixa
    {
        return $this->registrar('entrada', $categoria, $descricao, $valor, $clubId);
    }

    public function saida(string $categoria, string $descricao, $valor, ?int $clubId = null): Caixa
    {
        return $this->registrar('saida', $categoria, $descricao, $valor, $clubId);
    }

    private function registrar(string $tipo, string $categoria, string $descricao, $valor, ?int $clubId): Caixa
    {
        return Caixa::create([
            'descricao' => $descricao,
            'tipo' => $tipo,
            'categoria' => $categoria,
            'valor' => $valor,
            'data_movimentacao' => now(),
            'club_id' => $clubId ?? ClubContext::currentClubId(),
        ]);
    }
}
