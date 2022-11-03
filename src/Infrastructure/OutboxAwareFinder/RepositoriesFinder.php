<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;

use ddziaduch\OutboxPattern\Domain\AggregateRootId;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareRepositories;

// TODO: cover with test
final class RepositoriesFinder implements OutboxAwareFinder
{
    public function __construct(private readonly OutboxAwareRepositories $repositories,)
    {
    }

    public function find(AggregateRootId $id): ?OutboxAware
    {
        foreach ($this->repositories as $repository) {
            $object = $repository->find($id);
            if ($object instanceof OutboxAware) {
                return $object;
            }
        }

        return null;
    }
}
