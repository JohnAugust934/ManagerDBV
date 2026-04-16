<?php

namespace App\Console\Commands;

use App\Models\Especialidade;
use App\Models\EspecialidadeRequisito;
use App\Support\EspecialidadesCatalog;
use App\Support\EspecialidadesOfficialSync;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncEspecialidadesOficiais extends Command
{
    protected $signature = 'especialidades:sync-oficiais
                            {--dry-run : Apenas mostra as mudanças sem gravar}
                            {--requirements : Também sincroniza requisitos oficiais}
                            {--limit=0 : Limita a quantidade de especialidades para sincronizar requisitos}';

    protected $description = 'Sincroniza catálogo oficial de especialidades do MDA Wiki (com opção de prévia).';

    public function handle(EspecialidadesOfficialSync $sync): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $syncRequirements = (bool) $this->option('requirements');
        $limit = max(0, (int) $this->option('limit'));

        $this->info('Buscando catálogo oficial no MDA Wiki...');
        $catalog = $sync->fetchCatalogFromWeb();

        $this->line('Itens oficiais encontrados: ' . count($catalog));

        [$toInsert, $toUpdate] = $this->diffCatalog($catalog);

        $this->line('Novas especialidades: ' . count($toInsert));
        $this->line('Especialidades a atualizar: ' . count($toUpdate));

        if ($isDryRun) {
            $this->warn('Modo dry-run ativo: nenhuma alteração foi gravada.');
            if (! empty($toInsert)) {
                $this->line('Exemplo (insert): ' . $toInsert[0]['codigo'] . ' - ' . $toInsert[0]['nome']);
            }
            if (! empty($toUpdate)) {
                $this->line('Exemplo (update): ' . $toUpdate[0]['codigo'] . ' - ' . $toUpdate[0]['nome']);
            }
        } else {
            DB::transaction(function () use ($catalog) {
                foreach ($catalog as $item) {
                    $this->syncCatalogItem($item);
                }
            });

            Especialidade::bumpListCacheVersion();
            $this->info('Catálogo sincronizado com sucesso.');
        }

        if (! $syncRequirements) {
            return self::SUCCESS;
        }

        $query = Especialidade::query()
            ->whereNotNull('url_oficial')
            ->where('url_oficial', '!=', '')
            ->orderBy('codigo');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $especialidades = $query->get();

        $this->line('Sincronizando requisitos de ' . $especialidades->count() . ' especialidades...');

        $updatedRequirements = 0;

        foreach ($especialidades as $especialidade) {
            try {
                $requisitos = $sync->fetchRequirementsFromUrl($especialidade->url_oficial);
            } catch (\Throwable $e) {
                $this->warn("Falha em {$especialidade->codigo}: {$e->getMessage()}");
                continue;
            }

            if ($isDryRun) {
                $this->line("[dry-run] {$especialidade->codigo}: " . count($requisitos) . ' requisitos');
                continue;
            }

            DB::transaction(function () use ($especialidade, $requisitos): void {
                EspecialidadeRequisito::query()
                    ->where('especialidade_id', $especialidade->id)
                    ->delete();

                foreach ($requisitos as $index => $descricao) {
                    EspecialidadeRequisito::create([
                        'especialidade_id' => $especialidade->id,
                        'ordem' => $index + 1,
                        'descricao' => $descricao,
                    ]);
                }
            });

            $updatedRequirements++;
            $this->line("{$especialidade->codigo}: " . count($requisitos) . ' requisitos sincronizados');
        }

        if ($isDryRun) {
            $this->warn('Dry-run concluído para requisitos: nenhuma alteração gravada.');
        } else {
            $this->info("Requisitos sincronizados em {$updatedRequirements} especialidades.");
        }

        return self::SUCCESS;
    }

    /**
     * @param array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool} $item
     */
    private function syncCatalogItem(array $item): void
    {
        $byCode = Especialidade::query()->where('codigo', $item['codigo'])->first();
        $byNameArea = Especialidade::query()
            ->where('nome', $item['nome'])
            ->where('area', $item['area'])
            ->first();

        if ($byCode && $byNameArea && $byCode->id !== $byNameArea->id) {
            $this->mergeEspecialidades($byCode, $byNameArea);
            $target = Especialidade::query()->findOrFail($byNameArea->id);
        } elseif ($byCode) {
            $target = $byCode;
        } elseif ($byNameArea) {
            $target = $byNameArea;
        } else {
            $target = new Especialidade();
        }

        $target->fill([
            'nome' => $item['nome'],
            'area' => $item['area'],
            'codigo' => $item['codigo'],
            'url_oficial' => $item['url_oficial'],
            'is_oficial' => true,
            'is_avancada' => $item['is_avancada'],
            'cor_fundo' => EspecialidadesCatalog::colorByArea($item['area']),
        ]);

        $target->save();
    }

    private function mergeEspecialidades(Especialidade $from, Especialidade $to): void
    {
        if ($from->id === $to->id) {
            return;
        }

        // Mantém investiduras já existentes e migra as faltantes.
        $pivotRows = DB::table('desbravador_especialidade')
            ->where('especialidade_id', $from->id)
            ->get();

        foreach ($pivotRows as $row) {
            $exists = DB::table('desbravador_especialidade')
                ->where('desbravador_id', $row->desbravador_id)
                ->where('especialidade_id', $to->id)
                ->exists();

            if (! $exists) {
                DB::table('desbravador_especialidade')->insert([
                    'desbravador_id' => $row->desbravador_id,
                    'especialidade_id' => $to->id,
                    'data_conclusao' => $row->data_conclusao,
                    'created_at' => $row->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('desbravador_especialidade')
            ->where('especialidade_id', $from->id)
            ->delete();

        if (Schema::hasTable('especialidade_requisitos')) {
            $requirements = DB::table('especialidade_requisitos')
                ->where('especialidade_id', $from->id)
                ->orderBy('ordem')
                ->get();

            foreach ($requirements as $req) {
                $exists = DB::table('especialidade_requisitos')
                    ->where('especialidade_id', $to->id)
                    ->where('descricao', $req->descricao)
                    ->exists();

                if (! $exists) {
                    $nextOrder = (int) DB::table('especialidade_requisitos')
                        ->where('especialidade_id', $to->id)
                        ->max('ordem') + 1;

                    DB::table('especialidade_requisitos')->insert([
                        'especialidade_id' => $to->id,
                        'ordem' => $nextOrder,
                        'descricao' => $req->descricao,
                        'created_at' => $req->created_at,
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('especialidade_requisitos')
                ->where('especialidade_id', $from->id)
                ->delete();
        }

        if (Schema::hasTable('especialidade_auditorias')) {
            DB::table('especialidade_auditorias')
                ->where('especialidade_id', $from->id)
                ->update(['especialidade_id' => $to->id, 'updated_at' => now()]);
        }

        $from->delete();
    }

    /**
     * @param array<int, array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool}> $catalog
     * @return array{0: array<int, array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool}>, 1: array<int, array{area:string,codigo:string,nome:string,url_oficial:string,is_avancada:bool}>}
     */
    private function diffCatalog(array $catalog): array
    {
        $existing = Especialidade::query()
            ->whereNotNull('codigo')
            ->get()
            ->keyBy('codigo');

        $toInsert = [];
        $toUpdate = [];

        foreach ($catalog as $item) {
            $current = $existing->get($item['codigo']);

            if (! $current) {
                $toInsert[] = $item;
                continue;
            }

            if (
                $current->nome !== $item['nome'] ||
                $current->area !== $item['area'] ||
                $current->url_oficial !== $item['url_oficial'] ||
                (bool) $current->is_avancada !== (bool) $item['is_avancada'] ||
                ! $current->is_oficial
            ) {
                $toUpdate[] = $item;
            }
        }

        return [$toInsert, $toUpdate];
    }
}
