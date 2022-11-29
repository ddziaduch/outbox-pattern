<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventReader;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareRepositories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\OutboxPattern\Adapter\MongoEventReader */
class MongoEventReaderTest extends TestCase
{
    public function testReading(): void
    {
        $objectFactory = static fn (int $numberOfEventsInOutbox): object => new class ($numberOfEventsInOutbox) {
            /** @var string[] */
            public array $outbox;

            public function __construct(int $numberOfEventsInOutbox)
            {
                $this->outbox = array_fill(
                    0,
                    $numberOfEventsInOutbox,
                    serialize(new \stdClass()),
                );
            }
        };

        $repositoryFactory = function (object ...$objects): ObjectRepository {
            $repository = $this->createStub(ObjectRepository::class);
            $repository->method('findBy')->willReturn($objects);

            return $repository;
        };

        $object1 = $objectFactory(numberOfEventsInOutbox: 1);
        $object2 = $objectFactory(numberOfEventsInOutbox: 2);
        $object3 = $objectFactory(numberOfEventsInOutbox: 3);

        $repository1 = $repositoryFactory($object1, $object2);
        $repository2 = $repositoryFactory($object3);

        $repositories = $this->createStub(OutboxAwareRepositories::class);
        $repositories->method('getIterator')->willReturn(new \ArrayIterator([$repository1, $repository2]));

        $expectedEvents = [
            ...array_map('unserialize', $object1->outbox),
            ...array_map('unserialize', $object2->outbox),
            ...array_map('unserialize', $object3->outbox),
        ];

        $reader = new MongoEventReader($repositories, $this->createStub(ObjectManager::class));
        $actualEvents = [...$reader->read()];
        self::assertEquals($expectedEvents, $actualEvents);
    }
}
