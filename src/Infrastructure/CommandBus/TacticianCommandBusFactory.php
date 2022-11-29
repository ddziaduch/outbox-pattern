<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure\CommandBus;

use Doctrine\Persistence\ObjectManager;
use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use League\Tactician\Plugins\LockingMiddleware;

class TacticianCommandBusFactory
{
    public function create(
        ObjectManager $objectManager,
        HandlerLocator $locator,
    ): CommandBus {
        return new CommandBus(
            [
                new LockingMiddleware(),
                new DoctrineMongoODMFlushMiddleware($objectManager),
                $this->commandHandlerMiddleware($locator),
            ]
        );
    }

    private function commandHandlerMiddleware(HandlerLocator $locator): CommandHandlerMiddleware
    {
        $nameExtractor = new ClassNameExtractor();
        $inflector = new InvokeInflector();

        return new CommandHandlerMiddleware($nameExtractor, $locator, $inflector);
    }
}
