<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapter;

use ddziaduch\OutboxPattern\Application\Command;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\DoctrineMongoODMFlushMiddleware;
use Doctrine\Persistence\ObjectManager;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use League\Tactician\Plugins\LockingMiddleware;

class TacticianCommandBus implements CommandBus
{
    private readonly \League\Tactician\CommandBus $bus;

    public function __construct(
        ObjectManager $objectManager,
        HandlerLocator $locator,
    ) {
        $this->bus = new \League\Tactician\CommandBus(
            [
                new LockingMiddleware(),
                $this->createCommandHandlerMiddleware($locator),
                new DoctrineMongoODMFlushMiddleware($objectManager),
            ]
        );
    }

    public function execute(Command $command): void
    {
        $this->bus->handle($command);
    }

    private function createCommandHandlerMiddleware(HandlerLocator $locator): CommandHandlerMiddleware
    {
        $nameExtractor = new ClassNameExtractor();
        $inflector = new InvokeInflector();

        return new CommandHandlerMiddleware($nameExtractor, $locator, $inflector);
    }
}
