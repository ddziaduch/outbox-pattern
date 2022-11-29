<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\CommandBus;

use Doctrine\Persistence\ObjectManager;
use League\Tactician\Middleware;

class DoctrineMongoODMFlushMiddleware implements Middleware
{
    public function __construct(private readonly ObjectManager $objectManager)
    {
    }

    /**
     * @inheritDoc
     */
    public function execute($command, callable $next): mixed
    {
        $output = $next($command);

        $this->objectManager->flush();

        return $output;
    }
}
