<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Tests\Fixtures;

class DestinationAuthor
{
    public function __construct(
        public ?string $name = null
    ) {
    }

    public static function default(): self
    {
        return new self('John');
    }
}
