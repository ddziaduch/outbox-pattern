<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;
use Doctrine\Persistence\ObjectManager;

final class MongoEventScribe implements EventScribe
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly OutboxAwareFinder $finder,
    ) {
    }

    public function write(Event $event): void
    {
        $aggregateId = $event->aggregateRootId();

        $object = $this->finder->find($aggregateId);

        if ($object === null) {
            throw new \LogicException('Object with ID' . $aggregateId->value() . ' not found');
        }

        $object->getOutbox()->enqueue($event);
        $this->objectManager->persist($object);
    }
}
