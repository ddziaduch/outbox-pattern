<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Traversable;

/** @implements \IteratorAggregate<int, object> */
class EventsMemoryCache implements \IteratorAggregate
{
    /** @var array<object> */
    private array $events = [];

    public function put(object $event): void
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
