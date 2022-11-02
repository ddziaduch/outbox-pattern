<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class MongoEventScribeTest extends TestCase
{
    public function testWritingObjectWithOutbox(): void
    {
        $event = $this->createStub(Event::class);

        $object = new class implements OutboxAware {
            /** @var \SplQueue<Event> */
            private readonly \SplQueue $outbox;

            public function __construct()
            {
                $this->outbox = new \SplQueue();
            }

            public function getOutbox(): \SplQueue
            {
                return $this->outbox;
            }
        };

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('find')->willReturn($object);
        $objectManager->expects(self::once())->method('persist')->with($object);

        $scribe = new MongoEventScribe($objectManager);
        $scribe->write($event);

        self::assertSame($object->getOutbox()->dequeue(), $event);
    }

    public function testWritingObjectWithoutOutbox(): void
    {
        $event = $this->createStub(Event::class);

        $object = new \stdClass();

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('find')->willReturn($object);
        $objectManager->expects(self::never())->method('persist');

        $scribe = new MongoEventScribe($objectManager);
        $scribe->write($event);
    }
}
