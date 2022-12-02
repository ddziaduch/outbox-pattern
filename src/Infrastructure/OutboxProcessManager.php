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
        $objectReflection = new \ReflectionClass($object);

        if (!$objectReflection->hasProperty('outbox')) {
            return;
        }

        $outboxReflection = $objectReflection->getProperty('outbox');
        $outbox = $outboxReflection->getValue($object);

        if (!is_array($outbox)) {
            return;
        }

        foreach ($this->cache as $event) {
            $outbox[] = serialize($event);
        }
        $outboxReflection->setValue($object, $outbox);

        $this->cache->flush();
    }
}
