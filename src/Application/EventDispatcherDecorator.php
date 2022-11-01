<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Application;

use ddziaduch\OutboxPattern\Application\Port\EventStore;
use ddziaduch\OutboxPattern\Domain\Event;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly EventStore $eventStore,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function dispatch(object $event): object
    {
        if ($event instanceof Event) {
            $this->eventStore->store($event);
        } else {
            $this->dispatcher->dispatch($event);
        }

        return $event;
    }
}
