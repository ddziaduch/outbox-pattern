<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\Event;

use Ramsey\Uuid\Uuid;

class ProductCreated
{
    public readonly string $id;

    public function __construct(
        public readonly string $productId,
        public readonly string $productName,
    ) {
        $this->id = Uuid::uuid4()->toString();
    }
}
