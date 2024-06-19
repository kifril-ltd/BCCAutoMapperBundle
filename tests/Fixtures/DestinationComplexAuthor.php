<?php

namespace BCC\AutoMapperBundle\Tests\Fixtures;

class DestinationComplexAuthor
{
    private $name;

    /**
     * DestinationComplexAuthor constructor.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}
