<?php

declare(strict_types=1);

namespace BCC\AutoMapperBundle\Mapper\Value;

class CollectionValue implements MapperValueInterface
{
    public function __construct(
        protected mixed $value,
        protected array $deletedItems,
        protected bool $canExistsEntity = false
    ) {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDeletedItems(): array
    {
        return $this->deletedItems;
    }

    public function canExistsEntity(): bool
    {
        return $this->canExistsEntity;
    }
}
