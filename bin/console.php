<?php

declare(strict_types=1);

use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Presentation\CreateProductCliCommand;
use ddziaduch\OutboxPattern\Presentation\DispatchEventsCliCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$container = (new ContainerFactory())->create();

$app = new Application();

$app->add($container->get(CreateProductCliCommand::class));
$app->add($container->get(DispatchEventsCliCommand::class));

$app->run();
