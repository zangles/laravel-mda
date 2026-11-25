<?php

namespace Zangles\Mda\Commands;

use Zangles\Mda\Generation\Layout;

/**
 * Genera una Action (operación que modifica estado) y, por defecto, su DTO.
 */
class MakeActionCommand extends MdaCommand
{
    protected $signature = 'mda:action
        {model : Modelo dueño de la Action, por ejemplo Order}
        {name : Nombre de la Action, por ejemplo UpdateOrderStatus}
        {--no-dto : No genera el DTO; handle() queda sin parámetros}
        {--force : Sobrescribe los archivos que ya existen}';

    protected $description = 'Crea una Action de un modelo y su DTO (MDA)';

    public function handle(): int
    {
        $model = $this->resolveModel();

        if ($model === null) {
            return self::FAILURE;
        }

        $layout = $this->layout();
        $class = $this->withSuffix((string) $this->argument('name'), 'Action');
        $baseName = substr($class, 0, -strlen('Action'));

        $repositoryClass = $model->name.'Repository'.(config('mda.interfaces.repositories', false) ? 'Interface' : '');

        $replacements = [
            'namespace' => $layout->namespaceFor(Layout::ACTIONS, $model->name),
            'class' => $class,
            'model' => $model->name,
            'repositoryClass' => $repositoryClass,
            'repositoryFqcn' => $layout->fqcn(Layout::REPOSITORIES, $repositoryClass, $model->name),
        ];

        if ($this->option('no-dto')) {
            $this->generate('action-plain', $layout->pathFor(Layout::ACTIONS, $class, $model->name), $replacements);

            return self::SUCCESS;
        }

        $dtoClass = $baseName.'DTO';

        $this->generate('dto', $layout->pathFor(Layout::DTOS, $dtoClass, $model->name), [
            'namespace' => $layout->namespaceFor(Layout::DTOS, $model->name),
            'class' => $dtoClass,
            'purpose' => "Datos de entrada de {$class}.",
        ]);

        $this->generate('action', $layout->pathFor(Layout::ACTIONS, $class, $model->name), [
            ...$replacements,
            'dtoClass' => $dtoClass,
            'dtoFqcn' => $layout->fqcn(Layout::DTOS, $dtoClass, $model->name),
        ]);

        return self::SUCCESS;
    }
}
