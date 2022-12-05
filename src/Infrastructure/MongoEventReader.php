<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareRepositories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

final class MongoEventReader
{
    public function __construct(
        private readonly OutboxAwareRepositories $repositories,
        private readonly ObjectManager $objectManager,
    ) {
    }

    /** @return iterable<object> */
    public function read(): iterable
    {
        /** @var ObjectRepository<object> $repository */
        foreach ($this->repositories as $repository) {
            $objects = $repository->findBy(['outbox' => ['$not' => ['$size' => 0]]]);
            foreach ($objects as $object) {
                $this->assertObjectIsValid($object);

                foreach ($object->outbox as $serializedEvent) {
                    $event = unserialize($serializedEvent);
                    $this->assertEventIsValid($event);
                    yield $event;
                }

                $object->outbox = [];
                $this->objectManager->persist($object);
            }
        }

        $this->objectManager->flush();
    }

    private function assertObjectIsValid(mixed $object): void
    {
        if (!property_exists($object, 'outbox')) {
            throw new \LogicException('Expected object to have property outbox');
        }

        if (!is_array($object->outbox)) {
            throw new \LogicException('Expected objects outbox to be an array');
        }
    }

    private function assertEventIsValid(mixed $event): void
    {
        if (!is_object($event)) {
            throw new \LogicException('Expected event to be an object');
        }
    }
}
