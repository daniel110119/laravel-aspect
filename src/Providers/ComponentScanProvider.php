<?php
declare(strict_types=1);

namespace Lugege\LaravelAspect\Providers;

use Carbon\Laravel\ServiceProvider;
use Lugege\LaravelAspect\Attributes\Component;
use Lugege\LaravelAspect\Attributes\Logic;
use Lugege\LaravelAspect\Attributes\Repository;
use Lugege\LaravelAspect\Attributes\Service;
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
            __DIR__.'/../config/aspect_scan.php' => config_path('aspect_scan.php'),
        ],'laravel-assets');
    }

}
