<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Fake;

use ddziaduch\OutboxPattern\Domain\AggregateRootId;
use ddziaduch\OutboxPattern\Domain\Event;

class FakeEvent implements Event
{
    public function id(): string
    {
        // TODO: Implement id() method.
    }

    public function __construct(private readonly FakeAggregateRoot $aggregateRoot)
    {
    }

    public function aggregateRootId(): AggregateRootId
    {
        return $this->aggregateRoot->id();
    }

    public function aggregateRootClassName(): string
    {
        return FakeAggregateRoot::class;
    }
}
