<?php

declare(strict_types=1);

use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Presentation\CreateProductCommand;
use Symfony\Component\Console\Application;

require __DIR__.'/../vendor/autoload.php';

$container = (new ContainerFactory())->create();

$app = new Application();
$app->add(
    new CreateProductCommand(
        $container->get(
            CommandBus::class,
        ),
    ),
);
$app->run();
