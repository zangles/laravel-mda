<?php

namespace Zangles\Mda\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use Zangles\Mda\Generation\Layout;
use Zangles\Mda\Generation\ModelReference;
use Zangles\Mda\Generation\ModelResolver;
use Zangles\Mda\Generation\StubRenderer;

/**
 * Base de los comandos mda:*. Resuelve el modelo, rellena el stub y avisa
 * qué se creó y qué se salteó porque ya existía.
 */
abstract class MdaCommand extends Command
{
    /** @var list<string> archivos creados en esta ejecución */
    protected array $created = [];

    protected function layout(): Layout
    {
        return $this->laravel->make(Layout::class);
    }

    /**
     * Resuelve el argumento {model}. Devuelve null (y muestra el error) si es ambiguo.
     */
    protected function resolveModel(): ?ModelReference
    {
        try {
            $model = $this->laravel->make(ModelResolver::class)->resolve((string) $this->argument('model'));
        } catch (InvalidArgumentException $e) {
            $this->components->error($e->getMessage());

            return null;
        }

        if (! $model->exists) {
            $this->components->warn("No encontré el modelo [{$model->fqcn}]. Genero igual, pero revisá el use.");
        }

        return $model;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    protected function generate(string $stub, string $path, array $replacements): bool
    {
        $renderer = $this->laravel->make(StubRenderer::class);
        $written = $renderer->write($path, $renderer->render($stub, $replacements), (bool) $this->option('force'));
        $display = $this->relative($path);

        if (! $written) {
            $this->components->warn("[{$display}] ya existe. Usá --force para sobrescribirlo.");

            return false;
        }

        $this->created[] = $path;
        $this->components->info("[{$display}] creado.");

        return true;
    }

    /**
     * Separa "Pagos/Imputar" en ["Pagos", "Imputar"]: subcarpeta opcional y nombre de clase.
     *
     * @return array{0: ?string, 1: string}
     */
    protected function splitName(string $name): array
    {
        $name = trim(str_replace('/', '\\', $name), '\\');
        $position = strrpos($name, '\\');

        if ($position === false) {
            return [null, $name];
        }

        return [substr($name, 0, $position), substr($name, $position + 1)];
    }

    protected function withSuffix(string $name, string $suffix): string
    {
        return str_ends_with($name, $suffix) ? $name : $name.$suffix;
    }

    private function relative(string $path): string
    {
        $base = rtrim($this->laravel->basePath(), '\\/').DIRECTORY_SEPARATOR;

        return str_replace('\\', '/', str_starts_with($path, $base) ? substr($path, strlen($base)) : $path);
    }
}
