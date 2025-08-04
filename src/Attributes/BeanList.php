<?php
declare(strict_types=1);

namespace Daniel\LaravelAspect\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BeanList
{

    public function __construct(
        public string $value
    )
    {
    }
}
