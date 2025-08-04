<?php
declare(strict_types=1);

namespace Daniel\LaravelAspect\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Daniel\LaravelAspect\Attributes\Autowired;
use Daniel\LaravelAspect\Attributes\Value;
use ReflectionClass;

trait Injectable {
    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $reflectionClazz = new ReflectionClass($this);
        foreach ($reflectionClazz->getProperties() as $property) {
            $autowired = $property->getAttributes(Autowired::class);
            if ($autowired) {
                $property->setValue($this, app()->make($property->getType()->getName()));
            }
            $values = $property->getAttributes(Value::class);
            if (isset($values[0])) {
                $instance = $values[0]->newInstance();
                $pattern = $instance->pattern;
                $defaultValue = $instance->defaultValue;
                $property->setValue($this, Config::get($pattern, $defaultValue));
            }
        }
    }
}
