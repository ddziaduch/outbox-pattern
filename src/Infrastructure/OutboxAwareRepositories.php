<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

class OutboxAwareRepositories
{
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly OutboxAwareClassMetadata $classMetadata,
    ) {
    }

    /** @return iterable<ObjectRepository<OutboxAware>> */
    public function all(): iterable
    {
        foreach ($this->classMetadata->all() as $metadata) {
            yield $this->objectManager->getRepository($metadata->getName());
        }
    }
}
