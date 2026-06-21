<?php

if (! function_exists('mascaraCpf')) {
    /**
     * Formata e mascara um CPF para exibição: 000.***.***.00 (oculta os dígitos centrais).
     * Aceita CPF com ou sem pontuação. Retorna '—' se nulo/vazio.
     */
    function mascaraCpf(?string $cpf): string
    {
        if (! $cpf) {
            return '—';
        }

        $digits = preg_replace('/\D/', '', $cpf);

        if (strlen($digits) !== 11) {
            return '***.***.***-**';
        }

        return substr($digits, 0, 3).'.***.***.'.substr($digits, 9, 2);
    }
}

if (! function_exists('mascaraRg')) {
    /**
     * Mascara um RG para exibição, preservando apenas os últimos 2 dígitos.
     * Retorna '—' se nulo/vazio.
     */
    function mascaraRg(?string $rg): string
    {
        if (! $rg) {
            return '—';
        }

        $len = mb_strlen($rg);
        if ($len <= 2) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 2).mb_substr($rg, -2);
    }
}
