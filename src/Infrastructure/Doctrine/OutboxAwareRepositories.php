<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\Doctrine;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Traversable;

/** @implements \IteratorAggregate<ObjectRepository<object>> */
class OutboxAwareRepositories implements \IteratorAggregate
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly OutboxAwareClassMetadata $classMetadata,
    ) {
    }

    /** @return Traversable<ObjectRepository<object>> */
    public function getIterator(): Traversable
    {
        foreach ($this->classMetadata as $metadata) {
            yield $this->objectManager->getRepository($metadata->getName());
        }
    }
}
