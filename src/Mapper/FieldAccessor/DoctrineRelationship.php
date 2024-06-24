<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\FieldAccessor;

use BCC\AutoMapperBundle\Mapper\Exception\InvalidSourceProperty;
use BCC\AutoMapperBundle\Mapper\Mapper;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineRelationship implements FieldAccessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Mapper $mapper,
        private string $parentClass,
        private string $class,
        private string $field
    ) {
    }

    public function getValue(mixed $source): string|object|null
    {
        if (null === $source[$this->field]) {
            return null;
        }

        if (empty($source['id'])) {
            return $this->mapper->map($source[$this->field], $this->class);
        }

        $parent = $this->entityManager->getRepository($this->parentClass)->find($source['id']);

        if (!$parent) {
            throw new InvalidSourceProperty('Parent not exists');
        }

        $accessor = 'get' . ucfirst($this->field);

        return $this->mapper->map($source[$this->field], $parent->$accessor());
    }
}
