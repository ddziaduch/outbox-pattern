<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventScribe;
use ddziaduch\OutboxPattern\Infrastructure\EventsMemoryCache;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRoot;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRootId;
use ddziaduch\OutboxPattern\Tests\Fake\FakeEvent;
use PHPUnit\Framework\TestCase;

class MongoEventScribeTest extends TestCase
{
    public function testWriting(): void
    {
        $event = new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(1)));
        $cache = $this->createMock(EventsMemoryCache::class);
        $cache->expects(self::once())->method('put')->with($event);
        $scribe = new MongoEventScribe($cache);
        $scribe->write($event);
    }
}
