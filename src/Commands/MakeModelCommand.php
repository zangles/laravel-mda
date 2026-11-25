<?php

namespace Zangles\Mda\Commands;

use Zangles\Mda\Generation\Layout;
use Zangles\Mda\Generation\ModelReference;

/**
 * Genera el "mini-módulo" de un modelo: Finder, Queries, Repository y Service.
 */
class MakeModelCommand extends MdaCommand
{
    protected $signature = 'mda:model
        {model : Modelo, por ejemplo Order o Main/Order}
        {--only= : Solo estos componentes, separados por coma (finder,queries,repository,service)}
        {--except= : Todos menos estos componentes}
        {--force : Sobrescribe los archivos que ya existen}';

    protected $description = 'Crea el Finder, Queries, Repository y Service de un modelo (MDA)';

    private const COMPONENTS = ['finder', 'queries', 'repository', 'service'];

    public function handle(): int
    {
        $components = $this->selectedComponents();

        if ($components === null) {
            return self::FAILURE;
        }

        $model = $this->resolveModel();

        if ($model === null) {
            return self::FAILURE;
        }

        foreach ($components as $component) {
            match ($component) {
                'finder' => $this->makeFinder($model),
                'queries' => $this->makeQueries($model),
                'repository' => $this->makeRepository($model),
                'service' => $this->makeService($model, $components),
            };
        }

        $this->showBindings($model, $components);

        return self::SUCCESS;
    }

    /**
     * @return list<string>|null
     */
    private function selectedComponents(): ?array
    {
        $only = $this->listOption('only');
        $except = $this->listOption('except');
        $unknown = array_diff([...$only, ...$except], self::COMPONENTS);

        if ($unknown !== []) {
            $this->components->error(sprintf(
                'Componentes desconocidos: %s. Los válidos son: %s.',
                implode(', ', $unknown),
                implode(', ', self::COMPONENTS),
            ));

            return null;
        }

        $selected = $only === [] ? self::COMPONENTS : $only;

        return array_values(array_intersect(self::COMPONENTS, array_diff($selected, $except)));
    }

    /**
     * @return list<string>
     */
    private function listOption(string $name): array
    {
        $value = (string) $this->option($name);

        return array_values(array_filter(array_map('trim', explode(',', strtolower($value)))));
    }

    private function makeFinder(ModelReference $model): void
    {
        $this->makeClass(Layout::FINDERS, 'finder', $model->name.'Finder', $model);
    }

    private function makeQueries(ModelReference $model): void
    {
        $this->makeClass(Layout::QUERIES, 'queries', $model->name.'Queries', $model);
    }

    private function makeRepository(ModelReference $model): void
    {
        $this->makeWithOptionalInterface(Layout::REPOSITORIES, 'repository', $model->name.'Repository', $model);
    }

    /**
     * @param  list<string>  $components
     */
    private function makeService(ModelReference $model, array $components): void
    {
        $layout = $this->layout();
        $finderClass = $model->name.'Finder';
        $finderFqcn = $layout->fqcn(Layout::FINDERS, $finderClass, $model->name);

        $this->makeWithOptionalInterface(Layout::SERVICES, 'service', $model->name.'Service', $model, [
            'finderClass' => $finderClass,
            'finderFqcn' => $finderFqcn,
        ]);

        $finderPath = $layout->pathFor(Layout::FINDERS, $finderClass, $model->name);

        if (! in_array('finder', $components, true) && ! file_exists($finderPath)) {
            $this->components->warn("El Service usa {$finderClass}, que no existe. Crealo con: php artisan mda:model {$model->name} --only=finder");
        }
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function makeWithOptionalInterface(string $component, string $stub, string $class, ModelReference $model, array $extra = []): void
    {
        $withInterface = (bool) config("mda.interfaces.{$component}", false);
        $interface = $class.'Interface';

        if ($withInterface) {
            $this->makeClass($component, $stub.'-interface', $interface, $model, $extra);
        }

        $this->makeClass($component, $stub, $class, $model, [
            'implements' => $withInterface ? ' implements '.$interface : '',
            ...$extra,
        ]);
    }

    /**
     * @param  array<string, string>  $extra
     */
    private function makeClass(string $component, string $stub, string $class, ModelReference $model, array $extra = []): void
    {
        $layout = $this->layout();

        $this->generate($stub, $layout->pathFor($component, $class, $model->name), [
            'namespace' => $layout->namespaceFor($component, $model->name),
            'class' => $class,
            'model' => $model->name,
            'modelFqcn' => $model->fqcn,
            ...$extra,
        ]);
    }

    /**
     * Las interfaces no sirven de nada sin su binding. No toco los providers
     * del proyecto: muestro la línea para que la pegues donde corresponda.
     *
     * @param  list<string>  $components
     */
    private function showBindings(ModelReference $model, array $components): void
    {
        $layout = $this->layout();
        $lines = [];

        foreach (['repository' => Layout::REPOSITORIES, 'service' => Layout::SERVICES] as $name => $component) {
            if (! in_array($name, $components, true) || ! config("mda.interfaces.{$component}", false)) {
                continue;
            }

            $class = $layout->fqcn($component, $model->name.ucfirst($name), $model->name);
            $lines[] = sprintf('$this->app->bind(\\%sInterface::class, \\%s::class);', $class, $class);
        }

        if ($lines === []) {
            return;
        }

        $this->newLine();
        $this->components->info('Registrá los bindings en un ServiceProvider (por ejemplo AppServiceProvider::register):');

        foreach ($lines as $line) {
            $this->line('    '.$line);
        }

        $this->newLine();
    }
}
