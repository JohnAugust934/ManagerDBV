<?php

/**
 * Hook PostToolUse (Edit|Write): roda o teste Pest relacionado ao arquivo editado.
 *
 * Mapeia um arquivo de app/ para um <Nome>Test.php correspondente em tests/ e roda
 * APENAS esse arquivo (feedback rápido, sem a suíte inteira). Nunca bloqueia.
 *
 * Heurística do mapeamento: pega o basename sem extensão, remove sufixos comuns
 * (Controller, Service, Command) e procura <Base>Test.php em tests/.
 * Ex.: app/Models/Caixa.php          -> tests/.../CaixaTest.php
 *      app/Http/Controllers/CaixaController.php -> tests/.../CaixaTest.php
 */
$raw = stream_get_contents(STDIN);
if ($raw === false || $raw === '') {
    exit(0);
}

$payload = json_decode($raw, true);
$file = is_array($payload) ? ($payload['tool_input']['file_path'] ?? null) : null;
if (! is_string($file) || $file === '') {
    exit(0);
}

$normalized = str_replace('\\', '/', $file);

// Só reage a código de app PHP; ignora o resto (inclusive os próprios testes).
if (! str_contains($normalized, '/app/') || ! str_ends_with($normalized, '.php')) {
    exit(0);
}
if (str_contains($normalized, '/tests/')) {
    exit(0);
}

$projectDir = dirname(__DIR__, 2);
$testsDir = $projectDir.'/tests';
if (! is_dir($testsDir)) {
    exit(0);
}

$base = pathinfo($normalized, PATHINFO_FILENAME);
$base = preg_replace('/(Controller|Service|Command)$/', '', $base);
if ($base === '' || $base === null) {
    exit(0);
}

// Procura <Base>Test.php em qualquer subpasta de tests/.
$target = null;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testsDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $entry) {
    if ($entry->isFile() && $entry->getFilename() === $base.'Test.php') {
        $target = $entry->getPathname();
        break;
    }
}

if ($target === null) {
    exit(0); // sem teste correspondente — silencioso.
}

$artisan = $projectDir.'/artisan';
$cmd = escapeshellarg(PHP_BINARY).' '.escapeshellarg($artisan).' test '.escapeshellarg($target).' --compact';
exec($cmd, $output, $code);

$tail = array_slice($output, -8);
$status = $code === 0 ? '✅' : '❌';
fwrite(STDERR, "[related-tests] {$status} {$base}Test\n".implode("\n", $tail)."\n");

// Feedback informativo apenas — não bloqueia a edição.
exit(0);
