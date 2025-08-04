<?php

declare(strict_types=1);

namespace Daniel\LaravelAspect\Attributes\Routes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Response
{
    public function __construct(
        public string $clazz
    ) {
    }
}
