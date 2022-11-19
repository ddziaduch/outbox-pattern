<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\Event;

use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;
use ddziaduch\OutboxPattern\Domain\ValueObject\ProductId;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductCreated implements Event
{
    private readonly UuidInterface $id;

    public function __construct(private readonly ProductId $productId)
    {
        $this->id = Uuid::uuid4();
    }

    public function id(): string
    {
        return $this->id->toString();
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->productId;
    }
}
