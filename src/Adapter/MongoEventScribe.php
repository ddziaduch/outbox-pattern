<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event\Event;
use ddziaduch\OutboxPattern\Infrastructure\EventsMemoryCache;

final class MongoEventScribe implements EventScribe
{
    public function __construct(
        private readonly EventsMemoryCache $cache
    ) {
    }

    public function write(Event $event): void
    {
        $this->cache->put($event);
    }
}
