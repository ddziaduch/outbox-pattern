<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Unit\Adapter;

use ddziaduch\OutboxPattern\Adapter\MongoEventReader;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;

class MongoEventReaderTest extends TestCase
{
    public function testReading(): void
    {
        $objectFactory = static fn (object ...$outbox) => new class (...$outbox) {
            /** @var object[] */
            public array $outbox;

            public function __construct(object ...$outbox)
            {
                $this->outbox = $outbox;
            }
        };

        $repositoryFactory = function (object ...$objects) {
            $repository = $this->createStub(ObjectRepository::class);
            $repository->method('findBy')->willReturn($objects);

            return $repository;
        };

        $object1 = $objectFactory(new \stdClass(), new \stdClass());
        $object2 = $objectFactory(new \stdClass(), new \stdClass(), new \stdClass());
        $object3 = $objectFactory(new \stdClass(), new \stdClass(), new \stdClass(), new \stdClass());

        $repository1 = $repositoryFactory($object1, $object2);
        $repository2 = $repositoryFactory($object3);

        $repositories = $this->createStub(OutboxAwareRepositories::class);
        $repositories->method('getIterator')->willReturn(new \ArrayIterator([$repository1, $repository2]));

        $expectedEvents = [
            ...$object1->outbox,
            ...$object2->outbox,
            ...$object3->outbox,
        ];

        $reader = new MongoEventReader($repositories, $this->createStub(ObjectManager::class));
        $actualEvents = [...$reader->read()];
        self::assertSame($expectedEvents, $actualEvents);
    }
}
