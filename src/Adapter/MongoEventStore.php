<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventStore;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\ObjectWithOutbox;
use Doctrine\Persistence\ObjectManager;

class MongoEventStore implements EventStore
{
    public function __construct(
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function store(Event $event)
    {
        $aggregateId = $event->aggregateRootId();
        $aggregateRootClassName = $event->aggregateRootClassName();

        $object = $this->objectManager->find(
            $aggregateRootClassName,
            $aggregateId,
        );

        if ($object instanceof ObjectWithOutbox) {
            $object->getOutbox()->enqueue($event);
            $this->objectManager->persist($object);
        }
    }
}
