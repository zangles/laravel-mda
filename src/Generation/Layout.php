<?php

namespace Zangles\Mda\Generation;

use InvalidArgumentException;

/**
 * Sabe dónde vive cada componente de MDA: su namespace y su ruta en disco.
 */
final class Layout
{
    public const ACTIONS = 'actions';

    public const DTOS = 'dtos';

    public const FINDERS = 'finders';

    public const QUERIES = 'queries';

    public const REPOSITORIES = 'repositories';

    public const SERVICES = 'services';

    public const USE_CASES = 'use_cases';

    public const APPLICATION_SERVICES = 'application_services';

    /** Componentes que son un único archivo por modelo. */
    private const PER_MODEL_FILE = [self::FINDERS, self::QUERIES, self::REPOSITORIES, self::SERVICES];

    /**
     * @param  array<string, string>  $folders
     */
    public function __construct(
        private readonly string $rootNamespace,
        private readonly string $rootPath,
        private readonly array $folders,
        private readonly bool $groupByModel,
    ) {}

    /**
     * El mismo layout, pero dentro de una subcarpeta de la app. Sirve para MDA
     * por features: within('Features/Prefacturacion') genera en
     * App\Features\Prefacturacion\Finders en vez de App\Finders.
     */
    public function within(?string $scope): self
    {
        $scope = trim(str_replace('/', '\\', (string) $scope), '\\');

        if ($scope === '') {
            return $this;
        }

        return new self(
            rootNamespace: $this->rootNamespace.'\\'.$scope,
            rootPath: $this->rootPath.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $scope),
            folders: $this->folders,
            groupByModel: $this->groupByModel,
        );
    }

    /**
     * Namespace del componente. Para Actions y DTOs (y para los demás si
     * group_by_model está activo) se agrega la subcarpeta del modelo.
     */
    public function namespaceFor(string $component, ?string $model = null, ?string $subNamespace = null): string
    {
        $segments = [$this->rootNamespace, $this->folder($component)];

        if ($model !== null && $this->groupsByModel($component)) {
            $segments[] = $model;
        }

        if ($subNamespace !== null && $subNamespace !== '') {
            $segments[] = $subNamespace;
        }

        return implode('\\', $segments);
    }

    public function pathFor(string $component, string $class, ?string $model = null, ?string $subNamespace = null): string
    {
        $namespace = $this->namespaceFor($component, $model, $subNamespace);
        $relative = substr($namespace, strlen($this->rootNamespace) + 1);

        return $this->rootPath.DIRECTORY_SEPARATOR
            .str_replace('\\', DIRECTORY_SEPARATOR, $relative).DIRECTORY_SEPARATOR
            .$class.'.php';
    }

    public function fqcn(string $component, string $class, ?string $model = null): string
    {
        return $this->namespaceFor($component, $model).'\\'.$class;
    }

    private function groupsByModel(string $component): bool
    {
        if (in_array($component, [self::ACTIONS, self::DTOS], true)) {
            return true;
        }

        return $this->groupByModel && in_array($component, self::PER_MODEL_FILE, true);
    }

    private function folder(string $component): string
    {
        return $this->folders[$component]
            ?? throw new InvalidArgumentException("No hay carpeta configurada para el componente [{$component}].");
    }
}
