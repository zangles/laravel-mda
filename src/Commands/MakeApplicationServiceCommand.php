<?php

namespace Zangles\Mda\Commands;

use Zangles\Mda\Generation\Layout;

class MakeApplicationServiceCommand extends MdaCommand
{
    protected $signature = 'mda:application-service
        {name : Nombre del flujo, por ejemplo Checkout (queda CheckoutApplicationService)}
        {--force : Sobrescribe el archivo si ya existe}';

    protected $description = 'Crea un Application Service (orquestación e infraestructura de un flujo) (MDA)';

    public function handle(): int
    {
        [$subNamespace, $name] = $this->splitName((string) $this->argument('name'));
        $class = $this->withSuffix($name, 'ApplicationService');
        $layout = $this->layout();

        $this->generate('application-service', $layout->pathFor(Layout::APPLICATION_SERVICES, $class, null, $subNamespace), [
            'namespace' => $layout->namespaceFor(Layout::APPLICATION_SERVICES, null, $subNamespace),
            'class' => $class,
        ]);

        return self::SUCCESS;
    }
}
