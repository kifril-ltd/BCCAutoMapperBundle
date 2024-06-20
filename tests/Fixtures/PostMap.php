<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Tests\Fixtures;

use BCC\AutoMapperBundle\Mapper\AbstractMap;

/**
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class PostMap extends AbstractMap
{
    public function __construct()
    {
        $this->buildDefaultMap();
        $this->route('title', 'name');
    }

    public function getDestinationType(): string
    {
        return 'BCC\AutoMapperBundle\Tests\Fixtures\DestinationPost';
    }

    public function getSourceType(): string
    {
        return 'BCC\AutoMapperBundle\Tests\Fixtures\SourcePost';
    }
}
