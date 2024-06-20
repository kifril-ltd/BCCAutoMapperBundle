<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\FieldFilter;

use Doctrine\ORM\EntityManagerInterface;

class EntityMappingFilter extends AbstractMappingFilter
{
    public function __construct(
        protected EntityManagerInterface $em,
        string $className,
    ) {
        parent::__construct($className);
    }

    public function filter(mixed $value): string|object|null
    {
        if (!$value) {
            return null;
        }

        $entity = null;
        if (isset($value['id'])) {
            $entity = $this->em->getRepository($this->className)->find($value['id']);
        }

        return $this->getMapper()->map($value, $entity ?: $this->className);
    }
}
