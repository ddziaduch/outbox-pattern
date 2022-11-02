<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\AggregateRootId;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareClassMetadata;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\Persistence\ObjectManager;

final class MongoEventScribe implements EventScribe
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly OutboxAwareClassMetadata $classMetadata,
        private readonly UnitOfWork $unitOfWork,
        private readonly OutboxAwareRepositories $repositories,
    ) {
    }

    public function write(Event $event): void
    {
        $aggregateId = $event->aggregateRootId();

        $object = $this->findObjectViaUnitOfWork($aggregateId)
            ?? $this->getObjectViaRepositories($aggregateId);

        if ($object instanceof OutboxAware) {
            $object->getOutbox()->enqueue($event);
            $this->objectManager->persist($object);
        }
    }

    private function findObjectViaUnitOfWork(AggregateRootId $aggregateId): ?object
    {
        foreach ($this->classMetadata->all() as $metadata) {
            $object = $this->unitOfWork->tryGetById($aggregateId, $metadata);
            if ($object !== false) {
                return $object;
            }
        }

        return null;
    }

    private function getObjectViaRepositories(AggregateRootId $aggregateId): object
    {
        foreach ($this->repositories->all() as $repository) {
            $object = $repository->find($aggregateId);
            if ($object !== null) {
                return $object;
            }
        }

        throw new \OutOfBoundsException('Object with ID' . $aggregateId->value() . ' not found');
    }
}
