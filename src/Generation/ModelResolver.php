<?php

namespace Zangles\Mda\Generation;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

/**
 * Traduce lo que escribe el usuario ("Order", "Main/Order") al modelo real.
 *
 * Como MDA agrupa los modelos por base de datos, "Order" se busca de forma
 * recursiva dentro de la carpeta de modelos.
 */
final class ModelResolver
{
    public function __construct(
        private readonly Filesystem $files,
        private readonly string $modelsNamespace,
        private readonly string $modelsPath,
    ) {}

    public function resolve(string $input): ModelReference
    {
        $input = trim(str_replace('/', '\\', $input), '\\');

        if ($input === '') {
            throw new InvalidArgumentException('El nombre del modelo no puede estar vacío.');
        }

        if (str_contains($input, '\\')) {
            return $this->fromRelative($input);
        }

        $matches = $this->findFilesNamed($input.'.php');

        if (count($matches) > 1) {
            $options = array_map(fn (string $relative) => str_replace('\\', '/', $relative), $matches);

            throw new InvalidArgumentException(sprintf(
                'Hay más de un modelo llamado [%s]: %s. Indicá cuál, por ejemplo "%s".',
                $input,
                implode(', ', $options),
                $options[0],
            ));
        }

        if (count($matches) === 1) {
            return $this->fromRelative($matches[0]);
        }

        return new ModelReference($input, $this->modelsNamespace.'\\'.$input, false);
    }

    private function fromRelative(string $relative): ModelReference
    {
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative).'.php';
        $segments = explode('\\', $relative);

        return new ModelReference(
            name: end($segments),
            fqcn: $this->modelsNamespace.'\\'.$relative,
            exists: $this->files->exists($this->modelsPath.DIRECTORY_SEPARATOR.$relativePath),
        );
    }

    /**
     * @return list<string> rutas relativas a la carpeta de modelos, sin extensión y con "\" como separador
     */
    private function findFilesNamed(string $fileName): array
    {
        if (! $this->files->isDirectory($this->modelsPath)) {
            return [];
        }

        $matches = [];

        foreach ($this->files->allFiles($this->modelsPath) as $file) {
            if ($file->getFilename() !== $fileName) {
                continue;
            }

            $relative = substr($file->getPathname(), strlen($this->modelsPath) + 1, -4);
            $matches[] = str_replace(['/', DIRECTORY_SEPARATOR], '\\', $relative);
        }

        sort($matches);

        return $matches;
    }
}
