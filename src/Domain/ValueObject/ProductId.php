<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\ValueObject;

class ProductId implements AggregateRootId
{
    public function __construct(private readonly string $value)
    {
    }

    public function value(): string
    {
        return $this->value;
    }
}
