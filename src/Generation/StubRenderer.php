<?php

namespace Zangles\Mda\Generation;

use Illuminate\Filesystem\Filesystem;

/**
 * Rellena un stub y lo escribe en disco.
 *
 * Si el proyecto publicó los stubs (php artisan vendor:publish --tag=mda-stubs),
 * se usan los de stubs/mda/ en vez de los del paquete.
 */
final class StubRenderer
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $packageStubsPath,
        private readonly string $publishedStubsPath,
    ) {}

    /**
     * @param  array<string, string>  $replacements  clave => valor, para placeholders {{ clave }}
     */
    public function render(string $stub, array $replacements): string
    {
        $contents = $this->files->get($this->stubPath($stub));

        foreach ($replacements as $key => $value) {
            $contents = str_replace(['{{ '.$key.' }}', '{{'.$key.'}}'], $value, $contents);
        }

        return $contents;
    }

    /**
     * Escribe el archivo. Devuelve false si ya existía y no se pidió sobrescribir.
     */
    public function write(string $path, string $contents, bool $force = false): bool
    {
        if (! $force && $this->files->exists($path)) {
            return false;
        }

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $contents);

        return true;
    }

    private function stubPath(string $stub): string
    {
        $published = $this->publishedStubsPath.DIRECTORY_SEPARATOR.$stub.'.stub';

        return $this->files->exists($published)
            ? $published
            : $this->packageStubsPath.DIRECTORY_SEPARATOR.$stub.'.stub';
    }
}
