<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;

class FakeAggregateRootId implements AggregateRootId
{
    public function __construct(private readonly int $value)
    {
    }

    public function value(): int
    {
        return $this->value;
    }
}
