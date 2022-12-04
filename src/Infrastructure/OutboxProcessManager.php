<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;

class OutboxProcessManager
{
    public function __construct(
        private readonly EventsMemoryCache $cache,
    ) {
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
    public function prePersist(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!property_exists($object, 'outbox')) {
            return;
        }

        $outbox = $object->outbox;

        if (!is_array($outbox)) {
            return;
        }

        foreach ($this->cache as $event) {
            $outbox[] = serialize($event);
        }

        $object->outbox = $outbox;

        $this->cache->flush();
    }
}
