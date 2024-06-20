<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper;

use BCC\AutoMapperBundle\Mapper\Exception\InvalidClassConstructorException;
use BCC\AutoMapperBundle\Mapper\FieldAccessor\Simple;
use BCC\AutoMapperBundle\Mapper\FieldFilter\AbstractMappingFilter;
use BCC\AutoMapperBundle\Mapper\Value\CollectionValue;
use BCC\AutoMapperBundle\Mapper\Value\MapperValueInterface;
use BCC\AutoMapperBundle\Mapper\Value\SimpleValue;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Mapper maps objects and manages maps.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class Mapper
{
    /** @var array<string, array<string, MapInterface>> */
    private array $maps = [];

    /**
     * Creates and registers a default map given the source and destination types.
     */
    public function createMap(string $sourceType, string $destinationMap): DefaultMap
    {
        return $this->maps[$sourceType][$destinationMap] = new DefaultMap($sourceType, $destinationMap);
    }

    /**
     * Registers a map to the mapper.
     */
    public function registerMap(MapInterface $map): void
    {
        $this->maps[$map->getSourceType()][$map->getDestinationType()] = $map;
    }

    /**
     * Obtains a registered map for the given source and destination types.
     */
    public function getMap(string $sourceType, string $destinationType): MapInterface
    {
        if (!isset($this->maps[$sourceType])) {
            throw new \LogicException('There is no map that support this source type: ' . $sourceType);
        }

        if (!isset($this->maps[$sourceType][$destinationType])) {
            throw new \LogicException('There is no map that support this destination type: ' . $destinationType);
        }

        return $this->maps[$sourceType][$destinationType];
    }

    public function map(mixed $source, string|object $destination): object
    {
        if (is_string($destination)) {
            $destinationRef = new \ReflectionClass($destination);

            if ($destinationRef->getConstructor()?->getNumberOfRequiredParameters() > 0) {
                throw new InvalidClassConstructorException($destination);
            }
            $destination = $destinationRef->newInstance();
        }

        $map = $this->getMap(
            $this->guessType($source),
            $this->guessType($destination)
        );

        $fieldAccessors = $map->getFieldAccessors();
        $fieldFilters = $map->getFieldFilters();

        foreach ($fieldAccessors as $path => $fieldAccessor) {
            if ($fieldAccessor instanceof Simple) {
                $sourcePath = $fieldAccessor->getSourcePath();
            } elseif (!empty($map->getFieldRoutes()[$path])) {
                $sourcePath = $map->getFieldRoutes()[$path];
            } else {
                $sourcePath = $path;
            }
            if (is_array($source) && !array_key_exists($sourcePath, $source)) {
                continue;
            }

            $valueResult = $fieldAccessor->getValue($source);
            if ($valueResult instanceof MapperValueInterface) {
                $value = $valueResult->getValue();
            } else {
                $value = $valueResult;
            }

            if (isset($fieldFilters[$path])) {
                if (($filter = $fieldFilters[$path]) instanceof AbstractMappingFilter) {
                    $filter->setMapper($this);
                }

                $value = $filter->filter($value);
            }

            if ($map->getSkipNull() && null === $value) {
                continue;
            }

            if ($valueResult instanceof SimpleValue) {
                if ($map->getSkipNull() && null === $valueResult->getValue()) {
                    continue;
                }

                if ($map->getSkipNonExists() && !$valueResult->getExists()) {
                    continue;
                }
            }

            if ($map->getOverwriteIfSet()) {
                $this->mergeCollection($destination, $path, $value);
            } else {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();

                if (null == $propertyAccessor->getValue($destination, $path)) {
                    $propertyAccessor->setValue($destination, $path, $value);
                }
            }
        }

        foreach ($map->getAfterMappers() as $afterMapper) {
            $afterMapper->afterMapping($source, $destination);
        }

        return $destination;
    }

    private function mergeCollection(string|object $destination, string $path, mixed $value): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        if (!$propertyAccessor->isReadable($destination, $path)) {
            return;
        }

        $canExistsEntity = false;
        $oldValue = $propertyAccessor->getValue($destination, $path);
        $oldValueIds = [];

        if ($value instanceof CollectionValue) {
            $canExistsEntity = $value->canExistsEntity();
            // удаляем item
            foreach ($oldValue as $key => $item) {
                $itemId = (int) $item->getId();
                if (in_array($itemId, $value->getDeletedItems(), true)) {
                    unset($oldValue[$key]);
                } else {
                    $oldValueIds[] = $itemId;
                }
            }
            unset($item);
            $value = $value->getValue();
        }

        if ($oldValue instanceof Collection) {
            $oldValue = $oldValue->toArray();

            $duplicatedValueIds = [];
            // проверка возможность добавлять не новые сущности к коллекции
            if (!$canExistsEntity) {
                // если получили item с id но его не было раньше, то пропускаем его
                foreach ($value as $key => $item) {
                    if (!$item->getId()) {
                        continue;
                    }

                    if (
                        !empty($oldValueIds)
                        && !in_array((int) $item->getId(), $oldValueIds, true)
                    ) {
                        unset($value[$key]);
                    } else {
                        $duplicatedValueIds[] = $item->getId();
                    }
                }
            }

            if (!empty($duplicatedValueIds)) {
                foreach ($oldValue as $key => $oldValueItem) {
                    if (in_array($oldValueItem->getId(), $duplicatedValueIds)) {
                        unset($oldValue[$key]);
                    }
                }
            }

            $value = array_values(array_merge($oldValue, $value));
        }

        $propertyAccessor->setValue($destination, $path, $value);
    }

    private function guessType(array|object $guessable): string
    {
        if (is_array($guessable)) {
            return 'array';
        }

        return ClassUtils::getRealClass($guessable::class);
    }
}
