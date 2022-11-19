<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;

class OutboxProcessManager
{
    public function __construct(private readonly EventsMemoryCache $cache)
    {
    }

    public function __destruct()
    {
        if (!$this->cache->isEmpty()) {
            throw new \LogicException(
                'The aggregate must be persisted after dispatching it\'s events.',
            );
        }
    }

    /** @param LifecycleEventArgs<ObjectManager> $args */
    public function onPrePersist(LifecycleEventArgs $args): void
    {
        $aggregate = $args->getObject();

        if (!$aggregate instanceof OutboxAware) {
            return;
        }

        foreach ($this->cache as $key => $event) {
            if ($event->aggregateRootId()->value() === $aggregate->id()) {
                $aggregate->getOutbox()->attach($event);
            }
        }

        $this->cache->flush();
    }
}
