<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;

use ddziaduch\OutboxPattern\Domain\AggregateRootId;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareClassMetadata;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;
use Doctrine\ODM\MongoDB\UnitOfWork;

// TODO: cover with test
final class UnitOfWorkFinder implements OutboxAwareFinder
{
    public function __construct(
        private readonly OutboxAwareClassMetadata $classMetadata,
        private readonly UnitOfWork $unitOfWork,
    ) {
    }

    public function find(AggregateRootId $id): ?OutboxAware
    {
        foreach ($this->classMetadata->all() as $metadata) {
            $object = $this->unitOfWork->tryGetById($id, $metadata);
            if ($object instanceof OutboxAware) {
                return $object;
            }
        }

        return null;
    }
}
