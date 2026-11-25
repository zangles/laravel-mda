<?php

namespace Zangles\Mda\Commands;

use Zangles\Mda\Generation\Layout;

class MakeDtoCommand extends MdaCommand
{
    protected $signature = 'mda:dto
        {model : Modelo al que pertenece el DTO, por ejemplo Order}
        {name : Nombre del DTO, por ejemplo CreateOrder}
        {--force : Sobrescribe el archivo si ya existe}';

    protected $description = 'Crea un DTO de un modelo (MDA)';

    public function handle(): int
    {
        $model = $this->resolveModel();

        if ($model === null) {
            return self::FAILURE;
        }

        $layout = $this->layout();
        $class = $this->withSuffix((string) $this->argument('name'), 'DTO');

        $this->generate('dto', $layout->pathFor(Layout::DTOS, $class, $model->name), [
            'namespace' => $layout->namespaceFor(Layout::DTOS, $model->name),
            'class' => $class,
            'purpose' => 'Transporta datos entre capas con un contrato explícito.',
        ]);

        return self::SUCCESS;
    }
}
