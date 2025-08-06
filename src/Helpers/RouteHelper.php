<?php
declare(strict_types=1);

namespace Lugege\LaravelAspect\Helpers;

use Illuminate\Support\Facades\Route;
use Lugege\LaravelAspect\Attributes\Routes\DeleteMapping;
use Lugege\LaravelAspect\Attributes\Routes\GetMapping;
use Lugege\LaravelAspect\Attributes\Routes\PostMapping;
use Lugege\LaravelAspect\Attributes\Routes\PutMapping;
use ReflectionAttribute;
use ReflectionMethod;

class RouteHelper
{

    /**
     * This function handles prefix notation for controllers. If the prefix does not start
     * with a '/', it appends it. It takes an instance of ReflectionAttribute which is
     * an attribute instance of the controller's prefix. It returns the prefix string
     * with a leading '/'.
     *
     * @param  ReflectionAttribute  $prefixAttribute  - The prefix attribute instance of the route.
     * @return string - The formatted prefix string.
     */
    public static function handleControllerPrefix(ReflectionAttribute $prefixAttribute): string
    {
        $prefix = ($prefixAttribute->newInstance())->path;

        return str_starts_with($prefix, '/') ? $prefix : '/'.$prefix;
    }

    /**
     * Iterate over provided methods and map associated routes attributes to routes.
     *
     * @param  array  $methods  Array of methods
     * @param  string  $prefix  Prefix for the route
     * @param  string  $controller  Controller responsible for the route handling
     */
    public static function handleMethodsAttributes(array $methods, string $prefix, string $controller): void
    {
        $routesMapping = [
            GetMapping::class => 'get',
            PostMapping::class => 'post',
            PutMapping::class => 'put',
            DeleteMapping::class => 'delete',
        ];
        foreach ($methods as $method) {
            $attributes = collect($method['attributes']??[])->filter(function ($attribute) use ($routesMapping) {
                return array_key_exists($attribute['class']??[], $routesMapping);
            });
            if ($attributes->isEmpty()) {
                continue;
            }

            self::mapAttributesToRoutes($attributes->first(), $prefix, $controller, $method, $routesMapping);
        }
    }


    /**
     * Maps attribute to routes by creating a new instance of attribute, formulating the uri
     * and mapping the corresponding route method in Laravel Route facade.
     *
     * @param  ReflectionAttribute  $attribute  Instance of PHP's ReflectionAttribute class identifying a route attribute
     * @param  string  $prefix  Prefix for the route uri
     * @param  string  $controller  Name of the controller handling the route
     * @param  ReflectionMethod  $method  Instance of PHP's ReflectionMethod representing a method in the controller
     * @param  array  $routesMapping  An associative array having  class references as keys and their corresponding routings as values
     */
    private static function mapAttributesToRoutes($attribute, string $prefix, string $controller, $method, array $routesMapping): void
    {
        $instance = $attribute['data']??[];
        $uri = self::buildPath($prefix,$instance['path']??'');
        Route::{$routesMapping[$attribute['class']]}($uri, [$controller, $method['name']]);
    }

    private static function buildPath(string ...$segments): string {
        return implode('/', array_filter(array_map(static function($segment) {
            $segment = trim($segment);
            $segment = trim($segment, '/');
            return rawurlencode($segment);
        }, $segments)));
    }
}
