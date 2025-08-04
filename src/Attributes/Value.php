<?php
declare(strict_types=1);

namespace Daniel\LaravelAspect\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Value
{

    public function __construct(public string $pattern, public $defaultValue = null)
    {
    }
}
