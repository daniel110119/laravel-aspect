<?php
declare(strict_types=1);

namespace Daniel\LaravelAspect;

use Daniel\LaravelAspect\Attributes\Routes\Prefix;
use Daniel\LaravelAspect\Helpers\ControllerHelper;
use Daniel\LaravelAspect\Helpers\RouteHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class EasyRouter
{

    public static function register(): void
    {
        $scan_dir = config('aspect_scan.scan_dir');
        foreach ($scan_dir as $dir) {
            $controllerPath = app()->path($dir);
            $controllerFiles = ControllerHelper::scanForFiles($controllerPath);
            // Scan attributes in controllers and register routes.
            foreach ($controllerFiles as $file) {
                try {
                    $controller = ControllerHelper::convertPathToNamespace($file);

                    $reflectionClass = new ReflectionClass($controller);

                } catch (ReflectionException $e) {
                    dd($e->getMessage());
                }

                $controllerPrefixAttributes = collect($reflectionClass->getAttributes(Prefix::class));
                $prefix = $controllerPrefixAttributes->isEmpty() ? '' : RouteHelper::handleControllerPrefix($controllerPrefixAttributes->first());

                $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                RouteHelper::handleMethodsAttributes($methods, $prefix, $controller);
            }
        }
    }
}
