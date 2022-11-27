<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Adapter\MongoEventReader;
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
            fn (): ObjectManager => (new DocumentManagerFactory())->create(
                $this->get($container, Client::class),
                $this->get($container, EventManager::class),
            ),
        );

        $container->addShared(EventsMemoryCache::class);

        $container->addShared(
            EventDispatcherInterface::class,
            function () use ($container): EventDispatcherInterface {
                $cache = $this->get($container, EventsMemoryCache::class);

                return new EventDispatcherDecorator($cache);
            },
        );

        $container->addShared(
            CreateProductHandler::class,
            fn () => new CreateProductHandler(
                new MongoSaveProduct($this->get($container, ObjectManager::class)),
                $this->get($container, EventDispatcherInterface::class),
            ),
        );

        $container->addShared(
            CommandBus::class,
            fn (): TacticianCommandBus => new TacticianCommandBus(
                (new TacticianCommandBusFactory())->create(
                    $this->get($container, ObjectManager::class),
                    new ContainerLocator($container, [
                        CreateProductCommand::class => CreateProductHandler::class,
                    ]),
                ),
            )
        );

        $container->addShared(
            OutboxRelay::class,
            fn (): OutboxRelay => new OutboxRelay(
                $this->get($container, EventsMemoryCache::class),
            ),
        );

        $container->addShared(
            OutboxAwareClassMetadata::class,
            fn (): OutboxAwareClassMetadata => new OutboxAwareClassMetadata(
                $this->get($container, ObjectManager::class),
            ),
        );

        $container->addShared(
            OutboxAwareRepositories::class,
            fn (): OutboxAwareRepositories => new OutboxAwareRepositories(
                $this->get($container, ObjectManager::class),
                $this->get($container, OutboxAwareClassMetadata::class),
            ),
        );

        $container->addShared(
            MongoEventReader::class,
            fn (): MongoEventReader => new MongoEventReader(
                $this->get($container, OutboxAwareRepositories::class),
                $this->get($container, ObjectManager::class),
            ),
        );

        $eventManager = $this->get($container, EventManager::class);
        $outboxProcessManager = $this->get($container, OutboxRelay::class);
        $eventManager->addEventListener([Events::prePersist], $outboxProcessManager);

        return $container;
    }

    /**
     * @template T
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    private function get(ContainerInterface $container, string $className): mixed
    {
        $object = $container->get($className);
        assert($object instanceof $className);

        return $object;
    }
}
