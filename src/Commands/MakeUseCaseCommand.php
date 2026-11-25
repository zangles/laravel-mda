<?php

namespace Zangles\Mda\Commands;

use Zangles\Mda\Generation\Layout;

class MakeUseCaseCommand extends MdaCommand
{
    protected $signature = 'mda:use-case
        {name : Nombre del caso de uso, por ejemplo CheckoutProcess o Pagos/ImputarPago}
        {--force : Sobrescribe el archivo si ya existe}';

    protected $description = 'Crea un Use Case (reglas de negocio que cruzan modelos) (MDA)';

    public function handle(): int
    {
        [$subNamespace, $class] = $this->splitName((string) $this->argument('name'));
        $layout = $this->layout();

        $this->generate('use-case', $layout->pathFor(Layout::USE_CASES, $class, null, $subNamespace), [
            'namespace' => $layout->namespaceFor(Layout::USE_CASES, null, $subNamespace),
            'class' => $class,
        ]);

        return self::SUCCESS;
    }
}
