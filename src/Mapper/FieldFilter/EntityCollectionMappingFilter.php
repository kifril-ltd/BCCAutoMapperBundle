<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\FieldFilter;

use BCC\AutoMapperBundle\Mapper\Value\CollectionValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class EntityCollectionMappingFilter extends AbstractMappingFilter
{
    public function __construct(
        string $className,
        protected EntityManagerInterface $em,
        protected bool $canExistsEntity = false
    ) {
        parent::__construct($className);
    }

    public function filter(mixed $value): array|CollectionValue
    {
        if (null === $value) {
            return [];
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            return [];
        }

        $removedIds = [];
        foreach ($value as $key => $item) {
            if (isset($item['id'], $item['deleted']) && $item['deleted']) {
                $removedIds[] = (int) $item['id'];
                unset($value[$key]);
            }
        }
        unset($item);

        $objectFilter = new EntityMappingFilter($this->em, $this->className);
        $objectFilter->setMapper($this->getMapper());

        $values = array_map(fn ($item) => $objectFilter->filter($item), $value);

        return new CollectionValue($values, $removedIds, $this->canExistsEntity);
    }
}
