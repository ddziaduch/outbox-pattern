<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Domain\Event\Event;
use Traversable;

/** @implements \IteratorAggregate<int, Event> */
class EventsMemoryCache implements \IteratorAggregate
{
    /** @var array<Event> */
    private array $events = [];

    public function put(Event $event): void
    {
        $this->events[] = $event;
    }

    public function flush(): void
    {
        $this->events = [];
    }

    public function isEmpty(): bool
    {
        return $this->events === [];
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->events);
    }
}
