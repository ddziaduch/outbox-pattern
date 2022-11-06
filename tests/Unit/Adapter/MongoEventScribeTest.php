<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRoot;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRootId;
use ddziaduch\OutboxPattern\Tests\Fake\FakeEvent;
use ddziaduch\OutboxPattern\Tests\Fake\FakeObjectWithOutbox;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

class MongoEventScribeTest extends TestCase
{
    public function testWritingSuccess(): void
    {
        $event = new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(1)));

        $object = new FakeObjectWithOutbox($event->aggregateRootId()->value());

        $scribe = new MongoEventScribe();
        $scribe->write($event);
        $scribe->onPrePersist(
            new LifecycleEventArgs(
                $object,
                $this->createStub(ObjectManager::class),
            ),
        );

        $outbox = $object->getOutbox();
        self::assertCount(1, $outbox);
        self::assertTrue($outbox->contains($event));
    }

    public function testWritingFailure(): void
    {
        $event = $this->createStub(Event::class);

        $scribe = new MongoEventScribe();
        $scribe->write($event);

        $this->expectException(\LogicException::class);
        unset($scribe);
    }
}
