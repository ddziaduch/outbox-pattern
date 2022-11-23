<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Adapter\MongoSaveProduct;
use ddziaduch\OutboxPattern\Adapter\TacticianCommandBus;
use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\CreateProductHandler;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\DocumentManagerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\TacticianCommandBusFactory;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Persistence\ObjectManager;
use League\Container\Container;
use League\Tactician\Container\ContainerLocator;
use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $container = new Container();

        $container->addShared(Client::class, static fn (): Client => new Client('mongodb://mongo'));

        $container->addShared(EventManager::class);

        $container->addShared(
            ObjectManager::class,
            static fn (): ObjectManager => (new DocumentManagerFactory())->create(
                $container->get(Client::class),
                $container->get(EventManager::class),
            ),
        );

        $container->addShared(EventsMemoryCache::class);

        $container->addShared(
            EventDispatcherInterface::class,
            static fn (): EventDispatcherInterface => new EventDispatcherDecorator(
                $container->get(EventsMemoryCache::class),
            ),
        );

        $container->addShared(
            CreateProductHandler::class,
            static fn () => new CreateProductHandler(
                new MongoSaveProduct($container->get(ObjectManager::class)),
                $container->get(EventDispatcherInterface::class),
            ),
        );

        $container->addShared(
            CommandBus::class,
            static fn (): TacticianCommandBus => new TacticianCommandBus(
                (new TacticianCommandBusFactory())->create(
                    $container->get(ObjectManager::class),
                    new ContainerLocator($container, [
                        CreateProductCommand::class => CreateProductHandler::class,
                    ]),
                ),
            )
        );

        $container->addShared(
            OutboxProcessManager::class,
            static fn(): OutboxProcessManager => new OutboxProcessManager(
                $container->get(EventsMemoryCache::class),
            ),
        );

        $eventManager = $container->get(EventManager::class);
        assert($eventManager instanceof EventManager);

        $outboxProcessManager = $container->get(OutboxProcessManager::class);
        $eventManager->addEventListener([Events::prePersist], $outboxProcessManager);

        return $container;
    }
}
