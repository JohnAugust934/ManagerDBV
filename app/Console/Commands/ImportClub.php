<?php

namespace App\Console\Commands;

use App\Services\ClubImportService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Importa um clube a partir de um JSON gerado pelo ClubExportService, criando um
 * NOVO clube no banco multi-tenant atual (consolidação de instalações).
 *
 * Uso:
 *   php artisan tenant:import-club storage/app/export-clube.json
 *   php artisan tenant:import-club arquivo.json --name="Novo Nome do Clube"
 *
 * SEMPRE rode `php artisan backup:run` antes. O catálogo (classes/especialidades)
 * deve estar semeado no destino para que progresso de classe e especialidades
 * sejam resolvidos.
 */
class ImportClub extends Command
{
    protected $signature = 'tenant:import-club
        {file : Caminho do arquivo JSON exportado}
        {--name= : Sobrescreve o nome do clube importado}';

    protected $description = 'Importa um clube de um JSON (ClubExportService) como novo clube';

    public function handle(ClubImportService $importer): int
    {
        $file = $this->argument('file');

        if (! is_file($file)) {
            $this->error("Arquivo não encontrado: {$file}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (! is_array($data)) {
            $this->error('JSON inválido ou ilegível.');

            return self::FAILURE;
        }

        $this->info('Clube de origem: '.($data['meta']['club_nome'] ?? '(desconhecido)'));

        try {
            $report = $importer->import($data, $this->option('name'));
        } catch (Throwable $e) {
            $this->error('Falha na importação (nada foi gravado): '.$e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->newLine();
        $this->info("✅ Clube importado com ID #{$report['club_id']}.");
        $this->table(['Tabela', 'Registros importados'], collect($report['counts'])
            ->map(fn ($qtd, $tabela) => [$tabela, $qtd])
            ->values()
            ->all());

        if (! empty($report['warnings'])) {
            $this->newLine();
            $this->warn('Avisos:');
            foreach (array_unique($report['warnings']) as $aviso) {
                $this->line('  • '.$aviso);
            }
        }

        $this->newLine();
        $this->info('Defina/ajuste o platform admin e valide os logins importados.');

        return self::SUCCESS;
    }
}
