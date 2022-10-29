<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests;

use ddziaduch\OutboxPattern\Application\EventDispatcherDecorator;
use ddziaduch\OutboxPattern\Application\Port\EventStore;
use ddziaduch\OutboxPattern\Domain\Event;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcherDecoratorTest extends TestCase
{
    public function testDispatchingWithNotDomainEvent(): void
    {
        $event = new \stdClass();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch')->with($event);

        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects(self::never())->method('store');

        $decorator = new EventDispatcherDecorator($eventStore, $eventDispatcher);

        $decorator->dispatch($event);
    }

    public function testDispatchingWithDomainEvent(): void
    {
        $event = $this->createStub(Event::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $eventStore = $this->createMock(EventStore::class);
        $eventStore->expects(self::once())->method('store')->with($event);

        $decorator = new EventDispatcherDecorator($eventStore, $eventDispatcher);

        $decorator->dispatch($event);
    }
}
