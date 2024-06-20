<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper;

use BCC\AutoMapperBundle\Mapper\AfterMapper\AfterMapperInterface;
use BCC\AutoMapperBundle\Mapper\FieldAccessor\FieldAccessorInterface;
use BCC\AutoMapperBundle\Mapper\FieldFilter\FieldFilterInterface;

/**
 * MapInterface defines a map interface.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
interface MapInterface
{
    /**
     * @return FieldAccessorInterface[] An array of field accessors
     */
    public function getFieldAccessors(): array;

    /**
     * @return FieldFilterInterface[] An array of field filters
     */
    public function getFieldFilters(): array;

    /**
     * The source type
     */
    public function getSourceType(): string;

    /**
     * The destination type
     */
    public function getDestinationType(): string;

    /** @return array<string,string> */
    public function getFieldRoutes(): array;

    public function getSkipNull(): bool;

    public function getSkipNonExists(): bool;

    public function getOverwriteIfSet(): bool;

    /** @return AfterMapperInterface[] */
    public function getAfterMappers(): array;
}
