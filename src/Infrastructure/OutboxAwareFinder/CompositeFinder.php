<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;

use ddziaduch\OutboxPattern\Domain\AggregateRootId;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAware;
use ddziaduch\OutboxPattern\Infrastructure\OutboxAwareFinder;

// TODO: cover with test
final class CompositeFinder implements OutboxAwareFinder
{
    /** @var OutboxAwareFinder[] */
    private readonly array $finders;

    public function __construct(OutboxAwareFinder ...$finders)
    {
        $this->finders = $finders;
    }

    public function find(AggregateRootId $id): ?OutboxAware
    {
        foreach ($this->finders as $finder) {
            $object = $finder->find($id);
            if ($object !== null) {
                return $object;
            }
        }

        return null;
    }
}
