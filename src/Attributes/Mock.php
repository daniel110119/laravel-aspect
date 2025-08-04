<?php

declare(strict_types=1);

namespace Daniel\LaravelAspect\Attributes;

use Attribute;
use Daniel\LaravelAspect\MockType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mock
{

    /**
     * Mock constructor.
     *
     * @param string $value The value to be used for mocking
     * @param string $comment The comment to be used for mocking
     * @param MockType $type The type of the value to be used for mocking
     */
    public function __construct(
        public string $value,
        public string $comment = '',
        public MockType $type = MockType::STRING,
    )
    {
    }
}
