<?php

/**
 * Hook PostToolUse (Edit|Write): formata com Laravel Pint apenas o arquivo tocado.
 *
 * Lê o JSON do hook no stdin, extrai tool_input.file_path e roda
 * `./vendor/bin/pint <arquivo>` se for um .php existente dentro do projeto.
 * Sempre sai com 0 — formatação nunca deve bloquear a edição.
 *
 * Convenção do CLAUDE.md: manter limpos apenas os arquivos que você tocar.
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

// Só PHP.
if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') {
    exit(0);
}

if (! is_file($file)) {
    exit(0);
}

$projectDir = dirname(__DIR__, 2);

// Resolve o binário do Pint (Windows usa pint.bat / pint; Unix usa pint).
$candidates = [
    $projectDir.'/vendor/bin/pint.bat',
    $projectDir.'/vendor/bin/pint',
];
$pint = null;
foreach ($candidates as $candidate) {
    if (is_file($candidate)) {
        $pint = $candidate;
        break;
    }
}
if ($pint === null) {
    exit(0);
}

$cmd = escapeshellarg($pint).' '.escapeshellarg($file);
exec($cmd, $output, $code);

// Mostra o resultado no stderr só para contexto; nunca bloqueia.
if ($code !== 0 && ! empty($output)) {
    fwrite(STDERR, "[pint] ".implode("\n", $output)."\n");
}

exit(0);
