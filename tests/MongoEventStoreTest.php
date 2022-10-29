<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests;

use ddziaduch\OutboxPattern\Adapter\MongoEventStore;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\ObjectWithOutbox;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class MongoEventStoreTest extends TestCase
{
    /** @test */
    public function testStoringObjectWithOutbox(): void
    {
        $event = $this->createStub(Event::class);

        $object = new class implements ObjectWithOutbox {
            private readonly \SplQueue $outbox;

            public function __construct()
            {
                $this->outbox = new \SplQueue();
            }

            public function getDocument(): object
            {
                return new \stdClass();
            }

            public function getOutbox(): \SplQueue
            {
                return $this->outbox;
            }
        };

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('find')->willReturn($object);
        $objectManager->expects(self::once())->method('persist')->with($object);

        $eventStore = new MongoEventStore($objectManager);
        $eventStore->store($event);

        assertSame($object->getOutbox()->dequeue(), $event);
    }

    /** @test */
    public function testStoringObjectWithoutOutbox(): void
    {
        $event = $this->createStub(Event::class);

        $object = new \stdClass();

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('find')->willReturn($object);
        $objectManager->expects(self::never())->method('persist');

        $eventStore = new MongoEventStore($objectManager);
        $eventStore->store($event);
    }
}
