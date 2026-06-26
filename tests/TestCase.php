<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Campos de consentimento LGPD obrigatórios no cadastro de desbravadores.
     * Usar em testes que chamam desbravadores.store para não repetir os campos.
     */
    protected function consentimentoLgpd(string $responsavel = 'Responsável Teste'): array
    {
        return [
            'consentimento_lgpd' => '1',
            'consentimento_lgpd_responsavel' => $responsavel,
        ];
    }

    /**
     * Aceite dos termos obrigatório no registro por convite.
     */
    protected function aceiteTermos(): array
    {
        return ['aceite_termos' => '1'];
    }
}
