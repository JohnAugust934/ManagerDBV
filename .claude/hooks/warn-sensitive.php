<?php

/**
 * Hook PreToolUse (Edit|Write): avisa ao tocar arquivos sensíveis — não bloqueia.
 *
 * Lê o JSON do hook no stdin, extrai tool_input.file_path e, se casar com um
 * padrão sensível, escreve um aviso no stderr e sai com 0 (avisa e segue).
 *
 * config/backup.php já causou várias falhas em produção (ver memória do agente
 * "backup-gotchas-producao"). Os lockfiles e .env não devem mudar sem intenção.
 */
$raw = stream_get_contents(STDIN);
if ($raw === false || $raw === '') {
    exit(0);
}

$payload = json_decode($raw, true);
if (! is_array($payload)) {
    exit(0);
}

$file = $payload['tool_input']['file_path'] ?? null;
if (! is_string($file) || $file === '') {
    exit(0);
}

// Normaliza separadores para casar em Windows e Unix.
$normalized = str_replace('\\', '/', $file);
$base = basename($normalized);

$warnings = [];

if ($base === '.env' || str_starts_with($base, '.env.')) {
    $warnings[] = '⚠️  Editando arquivo .env — segredos/credenciais. Confirme que a mudança é intencional e não vai ser commitada.';
}

if ($base === 'composer.lock' || $base === 'package-lock.json') {
    $warnings[] = "⚠️  Editando lockfile ({$base}) à mão — prefira o gerenciador (composer/npm) para manter o lock consistente.";
}

if (str_ends_with($normalized, 'config/backup.php')) {
    $warnings[] = '⚠️  config/backup.php já causou várias falhas em produção (zip volátil, criptografia, discos de nuvem). Revise a memória "backup-gotchas-producao" e o CLAUDE.md antes de mudar.';
}

if (! empty($warnings)) {
    fwrite(STDERR, implode("\n", $warnings)."\n");
}

// Avisa e segue: nunca bloqueia.
exit(0);
