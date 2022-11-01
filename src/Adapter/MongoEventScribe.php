<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use Doctrine\Persistence\ObjectManager;

final class MongoEventScribe implements EventScribe
{
    public function __construct(
        private readonly ObjectManager $objectManager,
    ) {
    }

    public function write(Event $event): void
    {
        $aggregateId = $event->aggregateRootId();
        $aggregateRootClassName = $event->aggregateRootClassName();

        $object = $this->objectManager->find(
            $aggregateRootClassName,
            $aggregateId,
        );

        if ($object instanceof OutboxAware) {
            $object->getOutbox()->enqueue($event);
            $this->objectManager->persist($object);
        }
    }
}
