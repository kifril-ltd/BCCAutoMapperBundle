<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\AfterMapper;

interface AfterMapperInterface
{
    public function afterMapping(mixed $source, string|object $destination): mixed;
}
