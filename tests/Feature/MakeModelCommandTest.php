<?php

it('genera el mini-módulo completo de un modelo', function () {
    $this->makeModel('Main/Order');

    $this->artisan('mda:model', ['model' => 'Order'])
        ->expectsOutputToContain('$this->app->bind(\App\Repositories\OrderRepositoryInterface::class, \App\Repositories\OrderRepository::class);')
        ->assertSuccessful();

    foreach (['Finders/OrderFinder', 'Queries/OrderQueries', 'Repositories/OrderRepository', 'Repositories/OrderRepositoryInterface', 'Services/OrderService'] as $file) {
        expect($this->appFile($file.'.php'))->toBeFile()->toBeValidPhp();
    }

    expect(file_get_contents($this->appFile('Finders/OrderFinder.php')))
        ->toContain('namespace App\Finders;')
        ->toContain('use App\Models\Main\Order;')
        ->toContain('class OrderFinder')
        ->toContain('public function findById(int $id): ?Order');

    expect(file_get_contents($this->appFile('Repositories/OrderRepository.php')))
        ->toContain('class OrderRepository implements OrderRepositoryInterface');

    expect(file_get_contents($this->appFile('Services/OrderService.php')))
        ->toContain('use App\Finders\OrderFinder;')
        ->toContain('private readonly OrderFinder $finder')
        ->not->toContain('implements');
});

it('acepta la ruta del modelo cuando hay que desambiguar', function () {
    $this->makeModel('Main/Order');
    $this->makeModel('Logistic/Order');

    $this->artisan('mda:model', ['model' => 'Order'])
        ->expectsOutputToContain('Hay más de un modelo llamado [Order]')
        ->assertFailed();

    $this->artisan('mda:model', ['model' => 'Logistic/Order', '--only' => 'finder'])->assertSuccessful();

    expect(file_get_contents($this->appFile('Finders/OrderFinder.php')))
        ->toContain('use App\Models\Logistic\Order;');
});

it('genera igual si el modelo no existe, pero avisa', function () {
    $this->artisan('mda:model', ['model' => 'Invoice', '--only' => 'finder'])
        ->expectsOutputToContain('No encontré el modelo [App\Models\Invoice]')
        ->assertSuccessful();

    expect(file_get_contents($this->appFile('Finders/InvoiceFinder.php')))
        ->toContain('use App\Models\Invoice;');
});

it('respeta --only y --except', function () {
    $this->makeModel('Order');

    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder,service'])->assertSuccessful();

    expect($this->appFile('Finders/OrderFinder.php'))->toBeFile()
        ->and($this->appFile('Services/OrderService.php'))->toBeFile()
        ->and($this->appFile('Queries/OrderQueries.php'))->not->toBeFile()
        ->and($this->appFile('Repositories/OrderRepository.php'))->not->toBeFile();

    $this->artisan('mda:model', ['model' => 'Order', '--except' => 'finder,service'])->assertSuccessful();

    expect($this->appFile('Queries/OrderQueries.php'))->toBeFile()
        ->and($this->appFile('Repositories/OrderRepository.php'))->toBeFile();
});

it('rechaza componentes desconocidos', function () {
    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder,controller'])
        ->expectsOutputToContain('Componentes desconocidos: controller')
        ->assertFailed();

    expect($this->appFile('Finders/OrderFinder.php'))->not->toBeFile();
});

it('avisa si el Service queda apuntando a un Finder que no existe', function () {
    $this->makeModel('Order');

    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'service'])
        ->expectsOutputToContain('El Service usa OrderFinder, que no existe')
        ->assertSuccessful();
});

it('no pisa archivos existentes salvo con --force', function () {
    $this->makeModel('Order');
    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder'])->assertSuccessful();
    file_put_contents($this->appFile('Finders/OrderFinder.php'), '<?php // mi código');

    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder'])
        ->expectsOutputToContain('ya existe. Usá --force')
        ->assertSuccessful();

    expect(file_get_contents($this->appFile('Finders/OrderFinder.php')))->toBe('<?php // mi código');

    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder', '--force' => true])->assertSuccessful();

    expect(file_get_contents($this->appFile('Finders/OrderFinder.php')))->toContain('class OrderFinder');
});

it('agrupa por modelo cuando group_by_model está activo', function () {
    config()->set('mda.group_by_model', true);
    $this->makeModel('Order');

    $this->artisan('mda:model', ['model' => 'Order'])->assertSuccessful();

    expect($this->appFile('Repositories/Order/OrderRepository.php'))->toBeFile()
        ->and($this->appFile('Repositories/Order/OrderRepositoryInterface.php'))->toBeFile();

    expect(file_get_contents($this->appFile('Services/Order/OrderService.php')))
        ->toContain('namespace App\Services\Order;')
        ->toContain('use App\Finders\Order\OrderFinder;');
});

it('genera la interfaz del Service si se configura', function () {
    config()->set('mda.interfaces.services', true);
    config()->set('mda.interfaces.repositories', false);
    $this->makeModel('Order');

    $this->artisan('mda:model', ['model' => 'Order'])
        ->expectsOutputToContain('$this->app->bind(\App\Services\OrderServiceInterface::class, \App\Services\OrderService::class);')
        ->assertSuccessful();

    expect($this->appFile('Services/OrderServiceInterface.php'))->toBeFile()->toBeValidPhp()
        ->and($this->appFile('Repositories/OrderRepositoryInterface.php'))->not->toBeFile();

    expect(file_get_contents($this->appFile('Services/OrderService.php')))
        ->toContain('class OrderService implements OrderServiceInterface');

    expect(file_get_contents($this->appFile('Repositories/OrderRepository.php')))
        ->not->toContain('implements');
});

it('usa los stubs publicados por el proyecto', function () {
    $this->makeModel('Order');
    $published = $this->app->basePath('stubs/mda');
    mkdir($published, 0777, true);
    file_put_contents($published.'/finder.stub', "<?php\n\nnamespace {{ namespace }};\n\n// stub propio\nclass {{ class }} {}\n");

    $this->artisan('mda:model', ['model' => 'Order', '--only' => 'finder,queries'])->assertSuccessful();

    expect(file_get_contents($this->appFile('Finders/OrderFinder.php')))->toContain('// stub propio')
        ->and(file_get_contents($this->appFile('Queries/OrderQueries.php')))->not->toContain('// stub propio');
});
