<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EspecialidadesOfficialSync
{
    /**
     * @return array<int, array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool}>
     */
    public function fetchCatalogFromWeb(): array
    {
        $items = [];

        foreach (EspecialidadesCatalog::categorySources() as $source) {
            $html = $this->fetchHtml($source['url']);

            preg_match_all(
                "/<figcaption><b>([A-Z]{2,4}-\\d{3})<\\/b><br><a\\s+href='([^']+)'[^>]*>(.*?)<\\/a><\\/figcaption>/is",
                $html,
                $matches,
                PREG_SET_ORDER
            );

            $seenCodes = [];

            foreach ($matches as $match) {
                $codigo = strtoupper(trim($match[1]));

                if (isset($seenCodes[$codigo])) {
                    continue;
                }
                $seenCodes[$codigo] = true;

                $nome = $this->cleanText($match[3]);
                $url = $this->normalizeUrl($this->cleanText($match[2]));

                $items[] = [
                    'area' => $source['area'],
                    'codigo' => $codigo,
                    'nome' => $nome,
                    'url_oficial' => $url,
                    'is_avancada' => EspecialidadesCatalog::isAdvanced($nome),
                ];
            }
        }

        return $items;
    }

    /**
     * @return array<int, string>
     */
    public function fetchRequirementsFromUrl(string $url): array
    {
        $html = $this->fetchHtml($url);

        preg_match_all("/<li><span class='texto'>(.*?)<\\/span><\\/li>/is", $html, $matches);

        $requisitos = [];
        foreach ($matches[1] ?? [] as $raw) {
            $desc = $this->cleanText($raw);

            if ($desc === '') {
                continue;
            }

            if (in_array($desc, $requisitos, true)) {
                continue;
            }

            $requisitos[] = $desc;
        }

        return $requisitos;
    }

    private function fetchHtml(string $url): string
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ])->timeout(45)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Falha ao buscar URL oficial: {$url} ({$response->status()})");
        }

        return $response->body();
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return 'https://mda.wiki.br/' . ltrim($url, '/');
    }

    private function cleanText(string $value): string
    {
        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = strip_tags($decoded);
        $decoded = preg_replace('/\s+/u', ' ', $decoded) ?? '';
        $decoded = trim($decoded, " \t\n\r\0\x0B.");

        return $this->fixMojibake($decoded);
    }

    private function fixMojibake(string $value): string
    {
        if (! str_contains($value, 'Ã') && ! str_contains($value, 'Â')) {
            return $value;
        }

        $converted = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $value);

        return $converted !== false && $converted !== '' ? $converted : $value;
    }
}
