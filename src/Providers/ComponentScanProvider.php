<?php
declare(strict_types=1);

namespace Lugege\LaravelAspect\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Lugege\LaravelAspect\Attributes\Component;
use Lugege\LaravelAspect\Attributes\Logic;
use Lugege\LaravelAspect\Attributes\Repository;
use Lugege\LaravelAspect\Attributes\Service;
use Lugege\LaravelAspect\Cache\CacheRepository;
use Lugege\LaravelAspect\Cache\FileStore;
use Lugege\LaravelAspect\Helpers\NamespaceHelpers;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;

class ComponentScanProvider extends ServiceProvider
{

    /**
     * @throws ReflectionException
     */
    public function register(): void
    {
        $classes = $this->scanClasses(app()->path());
        foreach ($classes as $clazz) {
            $reflectionClazz = new ReflectionClass($clazz);
            $interfaces = $reflectionClazz->getInterfaceNames();
            foreach ($interfaces as $interfaceClazz) {
                app()->singleton($interfaceClazz, fn() => $reflectionClazz->newInstance());
            }
        }
        $this->app->singleton('laravel_aspect.cache', function ($app) {
            $path = config('laravel_aspect.cache.cache_path',
                storage_path('runtime/aspect/cache'));
            return new CacheRepository(new FileStore($app['files'], $path,'0776'));
        });
    }

    /**
     * @throws ReflectionException
     */
    public function scanClasses(string $basePath): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($basePath);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);
        $classes = [];
        foreach ($recursiveIterator as $file) {
            if ($file->isFile() && str_contains($file->getFilename(), '.php')) {
                $clazz = NamespaceHelpers::path2namespace($file->getPathname());
                try {
                    $reflection = new ReflectionClass($clazz);
                } catch (ReflectionException $_) {
                    continue;
                }
                $isEmpty = collect($reflection->getAttributes())->filter(fn ($attribute) => in_array($attribute->getName(), [
                    Component::class, Logic::class, Service::class, Repository::class
                ]))->isEmpty();
                if ($isEmpty) {
                    continue;
                }
                $classes[] = $clazz;
            }
        }

        return $classes;
    }


    public function boot(): void
    {

        $this->publishes([
            __DIR__.'/../config/laravel_aspect.php' => config_path('laravel_aspect.php'),
        ],'config');


        if(config('laravel_aspect.autoRoute',false)){
            $this->loadRoutesFrom(__DIR__.'/../Router/Route.php');
        }
    }

}
