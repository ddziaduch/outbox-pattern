<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\ValueObject;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductId implements AggregateRootId
{
    private function __construct(private readonly UuidInterface $uuid)
    {
    }

    public static function new(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function restore(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public function value(): string
    {
        return $this->uuid->toString();
    }
}
