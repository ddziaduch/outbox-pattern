<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventReader;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

final class MongoEventReader implements EventReader
{
    public function __construct(
        private readonly OutboxAwareRepositories $repositories,
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function read(): iterable
    {
        /** @var ObjectRepository<OutboxAware> $repository */
        foreach ($this->repositories as $repository) {
            $objects = $repository->findBy(['outbox' => ['$not' => ['$size' => 0]]]);
            foreach ($objects as $object) {
                if (!$object instanceof OutboxAware) {
                    throw new \LogicException('Expected repository to return collection of ' . OutboxAware::class);
                }

                foreach (clone $object->getOutbox() as $event) {
                    try {
                        yield $event;
                        $object->getOutbox()->detach($event);
                        $this->objectManager->persist($object);
                    } catch (\Throwable $throwable) {
                        // DLQ handling could be added here in future
                    }
                }
            }
        }
    }
}
