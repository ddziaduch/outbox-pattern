<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\Aggregate\AggregateRoot;
use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;

class FakeAggregateRoot implements AggregateRoot
{
    public function __construct(private readonly FakeAggregateRootId $id)
    {
    }

    public function id(): AggregateRootId
    {
        return $this->id;
    }
}
