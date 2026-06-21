<?php

namespace App\Console\Commands;

use App\Models\Desbravador;
use App\Models\LgpdRegistro;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LgpdAnonimizarDesligados extends Command
{
    protected $signature = 'lgpd:anonimizar-desligados
                            {--anos=5 : Anos de inatividade antes de anonimizar}
                            {--dry-run : Lista candidatos sem anonimizar}
                            {--club-id= : Restringir a um clube específico}';

    protected $description = 'Anonimiza dados pessoais de desbravadores inativos há N anos (LGPD Art. 16)';

    public function handle(): int
    {
        $anos = (int) $this->option('anos');
        $dryRun = $this->option('dry-run');
        $clubId = $this->option('club-id');

        $corte = now()->subYears($anos);

        $query = Desbravador::where('ativo', false)
            ->where('updated_at', '<', $corte)
            ->whereNull('consentimento_lgpd_responsavel'); // já anonimizados têm campo nulo

        if ($clubId) {
            $query->whereHas('unidade', fn ($q) => $q->where('club_id', $clubId));
        }

        $candidatos = $query->get(['id', 'nome', 'updated_at']);

        if ($candidatos->isEmpty()) {
            $this->info('Nenhum desbravador elegível para anonimização.');
            return self::SUCCESS;
        }

        $this->table(['ID', 'Nome', 'Última atualização'], $candidatos->map(fn ($d) => [
            $d->id,
            $d->nome,
            $d->updated_at->format('d/m/Y'),
        ])->toArray());

        if ($dryRun) {
            $this->warn("--dry-run: {$candidatos->count()} registro(s) seriam anonimizados.");
            return self::SUCCESS;
        }

        if (! $this->confirm("Anonimizar {$candidatos->count()} desbravador(es)? Esta operação é IRREVERSÍVEL.")) {
            return self::SUCCESS;
        }

        $anonimizados = 0;

        foreach ($candidatos as $desbravador) {
            DB::transaction(function () use ($desbravador, &$anonimizados) {
                $hash = Str::limit(hash('sha256', $desbravador->id.$desbravador->nome), 12, '');

                $desbravador->update([
                    'nome' => "Membro #{$hash}",
                    'cpf' => null,
                    'rg' => null,
                    'email' => null,
                    'telefone' => null,
                    'endereco' => null,
                    'nome_responsavel' => null,
                    'telefone_responsavel' => null,
                    'numero_sus' => null,
                    'alergias' => null,
                    'medicamentos_continuos' => null,
                    'plano_saude' => null,
                    'foto' => null,
                    'consentimento_lgpd' => false,
                    'consentimento_lgpd_em' => null,
                    'consentimento_lgpd_responsavel' => null,
                ]);

                LgpdRegistro::create([
                    'club_id' => $desbravador->unidade?->club_id,
                    'user_id' => null,
                    'acao' => 'anonimizacao',
                    'entidade' => 'desbravador',
                    'entidade_id' => $desbravador->id,
                    'ip_origem' => 'console',
                    'metadados' => ['anos_inatividade' => (int) $this->option('anos')],
                ]);

                $anonimizados++;
            });
        }

        $this->info("✔ {$anonimizados} desbravador(es) anonimizados com sucesso.");
        return self::SUCCESS;
    }
}
