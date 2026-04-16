<?php

namespace App\Support;

use Illuminate\Support\Str;

class EspecialidadesCatalog
{
    /**
     * @return array<int, array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool}>
     */
    public static function all(): array
    {
        $raw = require database_path('data/especialidades_oficiais.php');

        return array_values(array_map(function (array $item): array {
            $item['is_avancada'] = self::isAdvanced($item['nome']);

            return $item;
        }, $raw));
    }

    public static function isAdvanced(string $nome): bool
    {
        return Str::contains(self::normalizeForSearch($nome), 'avancado');
    }

    public static function normalizeForSearch(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->squish()
            ->value();
    }

    public static function colorByArea(string $area): string
    {
        return match ($area) {
            'ADRA' => '#1D4ED8',
            'Artes e Habilidades Manuais' => '#9333EA',
            'Atividades Agrícolas' => '#8B5A2B',
            'Atividades Missionárias e Comunitárias' => '#1E3A8A',
            'Atividades Profissionais' => '#B91C1C',
            'Atividades Recreativas' => '#059669',
            'Ciência e Saúde' => '#DC2626',
            'Estudos da Natureza' => '#FFFFFF',
            'Habilidades Domésticas' => '#D97706',
            'Mestrados' => '#111827',
            default => '#9CA3AF',
        };
    }

    /**
     * @return array<int, array{area:string,url:string}>
     */
    public static function categorySources(): array
    {
        return [
            ['area' => 'ADRA', 'url' => 'https://mda.wiki.br/ADRA'],
            ['area' => 'Artes e Habilidades Manuais', 'url' => 'https://mda.wiki.br/Artes_e_Habilidades_Manuais'],
            ['area' => 'Atividades Agrícolas', 'url' => 'https://mda.wiki.br/Atividades_Agr%C3%ADcolas'],
            ['area' => 'Atividades Missionárias e Comunitárias', 'url' => 'https://mda.wiki.br/Atividades_Mission%C3%A1rias_e_Comunit%C3%A1rias'],
            ['area' => 'Atividades Profissionais', 'url' => 'https://mda.wiki.br/Atividades_Profissionais'],
            ['area' => 'Atividades Recreativas', 'url' => 'https://mda.wiki.br/Atividades_Recreativas'],
            ['area' => 'Ciência e Saúde', 'url' => 'https://mda.wiki.br/Ci%C3%AAncia_e_Sa%C3%BAde'],
            ['area' => 'Estudos da Natureza', 'url' => 'https://mda.wiki.br/Estudos_da_Natureza'],
            ['area' => 'Habilidades Domésticas', 'url' => 'https://mda.wiki.br/Habilidades_Dom%C3%A9sticas'],
            ['area' => 'Mestrados', 'url' => 'https://mda.wiki.br/Mestrados'],
        ];
    }
}
