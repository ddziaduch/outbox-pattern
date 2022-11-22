<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Adapter\MongoEventScribe;
use ddziaduch\OutboxPattern\Adapter\MongoSaveProduct;
use ddziaduch\OutboxPattern\Adapter\TacticianCommandBus;
use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\CreateProductHandler;
use ddziaduch\OutboxPattern\Application\EventDispatcherDecorator;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Application\Port\SaveProduct;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\ObjectManagerFactory;
use Doctrine\Persistence\ObjectManager;
use League\Container\Container;
use League\Event\EventDispatcher;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\Locator\HandlerLocator;
use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ContainerFactory
{
    public function create(): ContainerInterface
    {
        $container = new Container();

        $container->addShared(Client::class, static fn (): Client => new Client('mongodb://mongo'));

        $container->addShared(
            ObjectManager::class,
            static fn (): ObjectManager => (new ObjectManagerFactory())->create(
                $container->get(Client::class),
            ),
        );

        $container->addShared(
            EventDispatcherInterface::class,
            static fn (): EventDispatcherInterface => new EventDispatcherDecorator(
                new MongoEventScribe(new EventsMemoryCache()),
                new EventDispatcher(),
            ),
        );

        $container
            ->add(SaveProduct::class, MongoSaveProduct::class)
            ->addArgument(ObjectManager::class)
        ;

        $container
            ->add(CreateProductHandler::class)
            ->addArgument(SaveProduct::class)
            ->addArgument(EventDispatcherInterface::class)
        ;

        $container
            ->add(HandlerLocator::class, ContainerLocator::class)
            ->addArgument($container)
            ->addArgument([
                CreateProductCommand::class => CreateProductHandler::class,
            ])
        ;

        $container
            ->add(CommandBus::class, TacticianCommandBus::class)
            ->addArgument(ObjectManager::class)
            ->addArgument(HandlerLocator::class)
        ;

        return $container;
    }
}
