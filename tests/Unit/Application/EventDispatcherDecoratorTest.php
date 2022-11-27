<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Application;

use ddziaduch\OutboxPattern\Infrastructure\EventDispatcherDecorator;
use ddziaduch\OutboxPattern\Infrastructure\EventsMemoryCache;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\OutboxPattern\Infrastructure\EventDispatcherDecorator */
class EventDispatcherDecoratorTest extends TestCase
{
    public function testDispatching(): void
    {
        $cache = new EventsMemoryCache();
        $dispatcher = new EventDispatcherDecorator($cache);
        $event = new \stdClass();
        $dispatcher->dispatch($event);
        self::assertFalse($cache->isEmpty());
        self::assertContains($event, $cache);
    }
}
