<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\FieldFilter;

use BCC\AutoMapperBundle\Mapper\Mapper;

/**
 * Provide easy access to any filter to current mapper object.
 *
 * @author Jorge Garcia Ramos <jorgegr89@gmail.com>
 */
abstract class AbstractMappingFilter implements FieldFilterInterface
{
    private Mapper $mapper;

    /**
     * AbstractMappingFilter constructor.
     */
    public function __construct(protected string $className)
    {
    }

    protected function getMapper(): Mapper
    {
        return $this->mapper;
    }

    public function setMapper(Mapper $mapper): void
    {
        $this->mapper = $mapper;
    }
}
