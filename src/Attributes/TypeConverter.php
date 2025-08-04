<?php

declare(strict_types=1);

namespace Lugege\LaravelAspect\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TypeConverter
{

    public function __construct(
        public string $value
    ) {

    }
}
