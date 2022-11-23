<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

class ProductId
{
    public readonly string $value;

    public function __construct()
    {
        $this->value = Uuid::uuid4()->toString();
    }
}
