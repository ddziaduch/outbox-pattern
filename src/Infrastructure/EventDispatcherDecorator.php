<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventsMemoryCache $cache,
    ) {
    }

    public function dispatch(object $event): object
    {
        $this->cache->put($event);

        return $event;
    }
}
