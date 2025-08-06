<?php
declare(strict_types=1);

namespace Lugege\LaravelAspect;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Lugege\LaravelAspect\Attributes\Routes\Prefix;
use Lugege\LaravelAspect\Helpers\ControllerHelper;
use Lugege\LaravelAspect\Helpers\RouteHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class EasyRouter
{
    private const ROUTE_CACHE_KEY = 'laravel_aspect_routes_cache';

    public static function register(): void
    {
        if ($cachedRoutes = self::getCachedRoutes()) {
            self::registerRoutes($cachedRoutes);
            return;
        }
        $scan_dir = config('laravel_aspect.scan_dir',[]);
        $routes = [];
        foreach ($scan_dir as $dir) {
            $controllerPath = app()->path($dir);
            $controllerFiles = ControllerHelper::scanForFiles($controllerPath);
            foreach ($controllerFiles as $file) {
                try {
                    $controller = ControllerHelper::convertPathToNamespace($file);
                    if ($controller == null) {
                        continue;
                    }
                    $reflectionClass = new ReflectionClass($controller);
                } catch (ReflectionException $e) {
                    dd($e->getMessage());
                }
                $controllerPrefixAttributes = collect($reflectionClass->getAttributes(Prefix::class));
                $prefix = $controllerPrefixAttributes->isEmpty() ? '' : RouteHelper::handleControllerPrefix($controllerPrefixAttributes->first());
                $methods = self::getControllerMethodData($reflectionClass);
                $routes[] = [
                    'prefix' => $prefix,
                    'methods' => $methods,
                    'controller' => $controller,
                ];
            }
        }
        if (!empty($routes)) {
            self::cacheRoutes($routes);
            self::registerRoutes($routes);
        }
    }
    private static function getControllerMethodData(ReflectionClass $reflectionClass): array
    {
        $methodData = [];
        foreach ($reflectionClass->getMethods() as $method) {
            if (!$method->isPublic()) continue;
            if (str_starts_with($method->getName(), '__')) continue;
            $methodData[] = [
                'name' => $method->getName(),
                'attributes' => self::extractAttributes($method),
                'parameters' => self::extractParameters($method)
            ];
        }

        return $methodData;
    }

    private static function extractAttributes(\ReflectionMethod $method): array
    {
        $attributes = [];

        foreach ($method->getAttributes() as $attribute) {
            $attributeInstance = $attribute->newInstance();

            // 只提取可序列化的属性数据
            $attributes[] = [
                'class' => $attribute->getName(),
                'data' => method_exists($attributeInstance, 'toArray')
                    ? $attributeInstance->toArray()
                    : get_object_vars($attributeInstance)
            ];
        }

        return $attributes;
    }

    private static function extractParameters(\ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $param) {
            $parameters[] = [
                'name' => $param->getName(),
                'type' => $param->getType()?->getName(),
                'position' => $param->getPosition(),
                'hasDefault' => $param->isDefaultValueAvailable(),
            ];
        }

        return $parameters;
    }

    private static function registerRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            RouteHelper::handleMethodsAttributes($route['methods'], $route['prefix'], $route['controller']);
        }
    }
    private static function getCachedRoutes(): ?array
    {
        if (config('laravel_aspect.cache.cache_enabled', true)) {
            $cache = App::make('laravel_aspect.cache');
            return $cache->get(self::ROUTE_CACHE_KEY);
        }
        return null;
    }
    private static function cacheRoutes(array $routes): void
    {
        if (config('laravel_aspect.cache.cache_enabled', true)) {
            $cache = App::make('laravel_aspect.cache');
            $cache->put(
                self::ROUTE_CACHE_KEY,
                $routes,
                config('laravel_aspect.cache.cache_ttl', 86400)
            );
        }
    }
}
