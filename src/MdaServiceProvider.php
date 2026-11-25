<?php

namespace Zangles\Mda;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Zangles\Mda\Commands\MakeActionCommand;
use Zangles\Mda\Commands\MakeApplicationServiceCommand;
use Zangles\Mda\Commands\MakeDtoCommand;
use Zangles\Mda\Commands\MakeModelCommand;
use Zangles\Mda\Commands\MakeUseCaseCommand;
use Zangles\Mda\Generation\Layout;
use Zangles\Mda\Generation\ModelResolver;
use Zangles\Mda\Generation\StubRenderer;

class MdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mda.php', 'mda');

        // bind y no singleton: así un cambio de config (por ejemplo en tests) se respeta.
        $this->app->bind(Layout::class, fn () => new Layout(
            rootNamespace: $this->rootNamespace(),
            rootPath: $this->rootPath(),
            folders: (array) config('mda.folders'),
            groupByModel: (bool) config('mda.group_by_model', false),
        ));

        $this->app->bind(ModelResolver::class, fn () => new ModelResolver(
            files: new Filesystem,
            modelsNamespace: config('mda.models.namespace') ?? $this->rootNamespace().'\\Models',
            modelsPath: config('mda.models.path') ?? $this->rootPath().DIRECTORY_SEPARATOR.'Models',
        ));

        $this->app->bind(StubRenderer::class, fn () => new StubRenderer(
            files: new Filesystem,
            packageStubsPath: __DIR__.'/../stubs',
            publishedStubsPath: $this->app->basePath('stubs'.DIRECTORY_SEPARATOR.'mda'),
        ));
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            MakeModelCommand::class,
            MakeActionCommand::class,
            MakeDtoCommand::class,
            MakeUseCaseCommand::class,
            MakeApplicationServiceCommand::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/mda.php' => config_path('mda.php'),
        ], 'mda-config');

        $this->publishes([
            __DIR__.'/../stubs' => $this->app->basePath('stubs/mda'),
        ], 'mda-stubs');
    }

    private function rootNamespace(): string
    {
        return rtrim(config('mda.namespace') ?? $this->app->getNamespace(), '\\');
    }

    private function rootPath(): string
    {
        return rtrim(config('mda.path') ?? $this->app->path(), '\\/');
    }
}
