<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Traversable;

class OutboxAwareRepositories implements \IteratorAggregate
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly OutboxAwareClassMetadata $classMetadata,
    ) {
    }

    /** @return Traversable<ObjectRepository<OutboxAware>> */
    public function getIterator(): Traversable
    {
        foreach ($this->classMetadata->all() as $metadata) {
            yield $this->objectManager->getRepository($metadata->getName());
        }
    }
}
