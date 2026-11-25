<?php

it('genera una Action con su DTO', function () {
    $this->makeModel('Main/Order');

    $this->artisan('mda:action', ['model' => 'Order', 'name' => 'UpdateOrderStatus'])->assertSuccessful();

    $action = $this->appFile('Actions/Order/UpdateOrderStatusAction.php');
    $dto = $this->appFile('DTO/Order/UpdateOrderStatusDTO.php');

    expect($action)->toBeFile()->toBeValidPhp()
        ->and($dto)->toBeFile()->toBeValidPhp();

    expect(file_get_contents($action))
        ->toContain('namespace App\Actions\Order;')
        ->toContain('use App\DTO\Order\UpdateOrderStatusDTO;')
        ->toContain('use App\Repositories\OrderRepositoryInterface;')
        ->toContain('private readonly OrderRepositoryInterface $repository')
        ->toContain('public function handle(UpdateOrderStatusDTO $dto): void');

    expect(file_get_contents($dto))
        ->toContain('namespace App\DTO\Order;')
        ->toContain('class UpdateOrderStatusDTO');
});

it('no duplica el sufijo Action', function () {
    $this->artisan('mda:action', ['model' => 'Order', 'name' => 'CreateOrderAction'])->assertSuccessful();

    expect($this->appFile('Actions/Order/CreateOrderAction.php'))->toBeFile()
        ->and($this->appFile('DTO/Order/CreateOrderDTO.php'))->toBeFile();
});

it('genera una Action sin DTO', function () {
    $this->artisan('mda:action', ['model' => 'Order', 'name' => 'PurgeOrders', '--no-dto' => true])->assertSuccessful();

    expect($this->appFile('Actions/Order/PurgeOrdersAction.php'))->toBeFile()->toBeValidPhp()
        ->and($this->appFile('DTO/Order/PurgeOrdersDTO.php'))->not->toBeFile();

    expect(file_get_contents($this->appFile('Actions/Order/PurgeOrdersAction.php')))
        ->toContain('public function handle(): void');
});

it('la Action depende de la clase del Repository si no hay interfaces', function () {
    config()->set('mda.interfaces.repositories', false);

    $this->artisan('mda:action', ['model' => 'Order', 'name' => 'CreateOrder'])->assertSuccessful();

    expect(file_get_contents($this->appFile('Actions/Order/CreateOrderAction.php')))
        ->toContain('use App\Repositories\OrderRepository;')
        ->toContain('private readonly OrderRepository $repository');
});

it('respeta los nombres de carpeta configurados', function () {
    config()->set('mda.folders.dtos', 'DTOs');

    $this->artisan('mda:dto', ['model' => 'Order', 'name' => 'CreateOrder'])->assertSuccessful();

    expect($this->appFile('DTOs/Order/CreateOrderDTO.php'))->toBeFile()->toBeValidPhp();
    expect(file_get_contents($this->appFile('DTOs/Order/CreateOrderDTO.php')))->toContain('namespace App\DTOs\Order;');
});

it('genera un Use Case, con subcarpeta opcional', function () {
    $this->artisan('mda:use-case', ['name' => 'CheckoutProcess'])->assertSuccessful();
    $this->artisan('mda:use-case', ['name' => 'Pagos/ImputarPago'])->assertSuccessful();

    expect($this->appFile('UseCases/CheckoutProcess.php'))->toBeFile()->toBeValidPhp()
        ->and($this->appFile('UseCases/Pagos/ImputarPago.php'))->toBeFile()->toBeValidPhp();

    expect(file_get_contents($this->appFile('UseCases/Pagos/ImputarPago.php')))
        ->toContain('namespace App\UseCases\Pagos;')
        ->toContain('public function execute(): void');
});

it('genera un Application Service con el sufijo', function () {
    $this->artisan('mda:application-service', ['name' => 'Checkout'])->assertSuccessful();

    expect($this->appFile('ApplicationServices/CheckoutApplicationService.php'))->toBeFile()->toBeValidPhp();
    expect(file_get_contents($this->appFile('ApplicationServices/CheckoutApplicationService.php')))
        ->toContain('class CheckoutApplicationService');
});
