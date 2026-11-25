<?php

namespace Zangles\Mda\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use Zangles\Mda\MdaServiceProvider;

abstract class TestCase extends Orchestra
{
    /** Carpeta "app/" falsa donde escriben los generadores en cada test. */
    protected string $appPath;

    protected function setUp(): void
    {
        $this->appPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-mda-tests'.DIRECTORY_SEPARATOR.uniqid('app_', true);

        parent::setUp();

        (new Filesystem)->ensureDirectoryExists($this->appPath);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->appPath);
        (new Filesystem)->deleteDirectory($this->app->basePath('stubs'.DIRECTORY_SEPARATOR.'mda'));

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [MdaServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('mda.namespace', 'App');
        $app['config']->set('mda.path', $this->appPath);
    }

    protected function appFile(string $relative): string
    {
        return $this->appPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }

    protected function makeModel(string $relative): void
    {
        $segments = explode('/', $relative);
        $class = array_pop($segments);
        $namespace = implode('\\', ['App\\Models', ...$segments]);

        $path = $this->appFile('Models/'.$relative.'.php');
        (new Filesystem)->ensureDirectoryExists(dirname($path));
        file_put_contents($path, "<?php\n\nnamespace {$namespace};\n\nclass {$class} {}\n");
    }
}
