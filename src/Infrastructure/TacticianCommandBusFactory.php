<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use League\Tactician\CommandBus;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\HandlerLocator;
use League\Tactician\Handler\MethodNameInflector\InvokeInflector;
use League\Tactician\Plugins\LockingMiddleware;

class TacticianCommandBusFactory
{
    public function create(
        HandlerLocator $locator,
    ): CommandBus {
        return new CommandBus(
            [
                new LockingMiddleware(),
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
