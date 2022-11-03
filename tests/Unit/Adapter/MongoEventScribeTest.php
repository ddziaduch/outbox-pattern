<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRoot;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRootId;
use ddziaduch\OutboxPattern\Tests\Fake\FakeEvent;
use ddziaduch\OutboxPattern\Tests\Fake\FakeObjectWithOutbox;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class MongoEventScribeTest extends TestCase
{
    public function testWritingSuccess(): void
    {
        $event = new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(1)));

        $object = new FakeObjectWithOutbox();

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::once())->method('persist')->with($object);

        $finder = $this->createStub(OutboxAwareFinder::class);
        $finder->method('find')->with($event->aggregateRootId())->willReturn($object);

        $scribe = new MongoEventScribe($objectManager, $finder);
        $scribe->write($event);

        self::assertSame($object->getOutbox()->dequeue(), $event);
    }

    public function testWritingFailure(): void
    {
        $event = $this->createStub(Event::class);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->expects(self::never())->method('persist');

        $finder = $this->createStub(OutboxAwareFinder::class);
        $finder->method('find')->willReturn(null);

        $scribe = new MongoEventScribe($objectManager, $finder);
        
        $this->expectException(\LogicException::class);
        $scribe->write($event);
    }
}
