<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Port\EventScribe;
use ddziaduch\OutboxPattern\Domain\Event;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;

final class MongoEventScribe implements EventScribe
{
    /** @var array<Event> */
    private array $events = [];

    public function __construct() {
    }

    public function __destruct()
    {
        if (!empty($this->events)) {
            throw new \LogicException(
                'The aggregate must be persisted after dispatching it\'s events.',
            );
        }
    }

    public function write(Event $event): void
    {
        $this->events[] = $event;
    }

    /** @param LifecycleEventArgs<ObjectManager> $args */
    public function onPrePersist(LifecycleEventArgs $args): void
    {
        $aggregate = $args->getObject();

        if (!$aggregate instanceof OutboxAware) {
            return;
        }

        foreach ($this->events as $key => $event) {
            if ($event->aggregateRootId()->value() === $aggregate->id()) {
                $aggregate->getOutbox()->attach($event);
                unset($this->events[$key]);
            }
        }
    }
}
