<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Versão vigente do termo de privacidade (LGPD)
    |--------------------------------------------------------------------------
    |
    | Ao mudar o texto do termo em resources/views/privacidade/termo.blade.php,
    | incremente esta versão. Consentimentos antigos guardam a versão que foi
    | aceita, permitindo exigir reconsentimento sem perder o histórico.
    |
    */
    'versao_termo' => env('VERSAO_TERMO_PRIVACIDADE', '2026.1'),
];
