<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\Event\Event;
use ddziaduch\OutboxPattern\Domain\ValueObject\AggregateRootId;

class FakeEvent implements Event
{
    public function id(): string
    {
        throw new \LogicException('implement me');
    }

    public function __construct(private readonly FakeAggregateRoot $aggregateRoot)
    {
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRoot->id();
    }
}
