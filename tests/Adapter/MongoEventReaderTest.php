<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventReader;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRoot;
use ddziaduch\OutboxPattern\Tests\Fake\FakeAggregateRootId;
use ddziaduch\OutboxPattern\Tests\Fake\FakeEvent;
use ddziaduch\OutboxPattern\Tests\Fake\FakeObjectWithOutbox;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class MongoEventReaderTest extends TestCase
{
    public function testReading(): void
    {

        $object1 = new FakeObjectWithOutbox();
        $object1->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(1))));
        $object1->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(1))));

        $object2 = new FakeObjectWithOutbox();
        $object2->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(2))));
        $object2->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(2))));
        $object2->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(3))));

        $object3 = new FakeObjectWithOutbox();
        $object3->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(2))));
        $object3->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(2))));
        $object3->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(3))));
        $object3->getOutbox()->enqueue(new FakeEvent(new FakeAggregateRoot(new FakeAggregateRootId(3))));

        $repository1 = $this->createStub(ObjectRepository::class);
        $repository1->method('findBy')->willReturn([$object1, $object2]);

        $repository2 = $this->createStub(ObjectRepository::class);
        $repository2->method('findBy')->willReturn([$object3]);

        $repositories = $this->createStub(OutboxAwareRepositories::class);
        $repositories->method('all')->willReturn([$repository1, $repository2]);

        $expectedEvents = [
            ...$object1->getOutbox(),
            ...$object2->getOutbox(),
            ...$object3->getOutbox(),
        ];

        $reader = new MongoEventReader($repositories);
        $actualEvents = [...$reader->read()];
        self::assertSame($expectedEvents, $actualEvents);
    }
}
