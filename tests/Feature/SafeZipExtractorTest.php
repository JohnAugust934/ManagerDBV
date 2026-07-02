<?php

namespace Tests\Feature;

use App\Support\SafeZipExtractor;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

/**
 * Extrator de ZIP com proteção a path traversal (Candidato D) — consolida a
 * lógica antes duplicada em BackupController e ClubRestoreService.
 */
class SafeZipExtractorTest extends TestCase
{
    private string $work;

    protected function setUp(): void
    {
        parent::setUp();
        $this->work = storage_path('framework/testing/zip-'.uniqid());
        File::ensureDirectoryExists($this->work);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->work);
        parent::tearDown();
    }

    private function makeZip(string $name, array $entries): string
    {
        $path = $this->work.'/'.$name;
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::CREATE);
        foreach ($entries as $entryName => $content) {
            $zip->addFromString($entryName, $content);
        }
        $zip->close();

        return $path;
    }

    public function test_extrai_entradas_validas(): void
    {
        $zip = $this->makeZip('ok.zip', [
            'database/dump.sql' => 'CREATE TABLE x;',
            'app/public/foto.txt' => 'ola',
        ]);
        $dest = $this->work.'/out';

        (new SafeZipExtractor)->extract($zip, $dest);

        $this->assertFileExists($dest.'/database/dump.sql');
        $this->assertSame('ola', file_get_contents($dest.'/app/public/foto.txt'));
    }

    public function test_bloqueia_path_traversal(): void
    {
        $zip = $this->makeZip('evil.zip', ['../../etc/passwd' => 'x']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/path traversal|caminhos inválidos/i');

        (new SafeZipExtractor)->extract($zip, $this->work.'/out');
    }

    public function test_require_non_empty_rejeita_zip_vazio(): void
    {
        $zip = $this->makeZip('vazio.zip', []);

        $this->expectException(\RuntimeException::class);

        (new SafeZipExtractor)->extract($zip, $this->work.'/out', requireNonEmpty: true);
    }

    public function test_zip_corrompido_lanca_excecao(): void
    {
        $path = $this->work.'/corrompido.zip';
        file_put_contents($path, 'isto-nao-e-um-zip');

        $this->expectException(\RuntimeException::class);

        (new SafeZipExtractor)->extract($path, $this->work.'/out');
    }
}
