<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\FieldFilter;

use BCC\AutoMapperBundle\Mapper\Exception\InvalidSourceProperty;
use Doctrine\ORM\EntityManagerInterface;

class EntityFilter implements FieldFilterInterface
{
    public function __construct(
        protected string $className,
        protected EntityManagerInterface $em
    ) {
    }

    public function filter(mixed $value): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!is_int($value)) {
            throw new InvalidSourceProperty('Entity id must be integer');
        }

        return $this->em->getRepository($this->className)->find($value);
    }
}
