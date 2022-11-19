<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Application;

use ddziaduch\OutboxPattern\Application\EventDispatcherDecorator;
use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event\Event;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventDispatcherDecoratorTest extends TestCase
{
    public function testDispatchingWithNotDomainEvent(): void
    {
        $event = new \stdClass();

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::once())->method('dispatch')->with($event);

        $eventStore = $this->createMock(EventScribe::class);
        $eventStore->expects(self::never())->method('write');

        $decorator = new EventDispatcherDecorator($eventStore, $eventDispatcher);

        $decorator->dispatch($event);
    }

    public function testDispatchingWithDomainEvent(): void
    {
        $event = $this->createStub(Event::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher->expects(self::never())->method('dispatch');

        $eventStore = $this->createMock(EventScribe::class);
        $eventStore->expects(self::once())->method('write')->with($event);

        $decorator = new EventDispatcherDecorator($eventStore, $eventDispatcher);

        $decorator->dispatch($event);
    }
}
