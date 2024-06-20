<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Tests\Fixtures;

class SourceComment
{
    public function __construct(
        public ?string $content = null
    ) {
    }

    public static function default(): self
    {
        return new self('Comment content');
    }
}
