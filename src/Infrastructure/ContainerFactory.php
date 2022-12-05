<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Adapter\MongoSaveProduct;
use ddziaduch\OutboxPattern\Adapter\TacticianCommandBus;
use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\CreateProductHandler;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Application\ProductCreatedListener;
use ddziaduch\OutboxPattern\Domain\Event\ProductCreated;
use ddziaduch\OutboxPattern\Infrastructure\CommandBus\TacticianCommandBusFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\DocumentManagerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareClassMetadata;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareRepositories;
use ddziaduch\OutboxPattern\Presentation\CreateProductCliCommand;
use ddziaduch\OutboxPattern\Presentation\DispatchEventsCliCommand;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Persistence\ObjectManager;
use League\Container\Container;
use League\Event\EventDispatcher;
use League\Tactician\Container\ContainerLocator;
use MongoDB\Client;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;

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

        $container->addShared(EventDispatcher::class);

        $container->addShared(
            EventDispatcherInterface::class,
            fn(): EventDispatcherInterface => $this->get(
                $container,
                EventDispatcher::class,
            ),
        );

        $container->addShared(
            EventDispatcherDecorator::class,
            function () use ($container): EventDispatcherDecorator {
                $cache = $this->get($container, EventsMemoryCache::class);

                return new EventDispatcherDecorator($cache);
            },
        );

        $container->addShared(
            CreateProductHandler::class,
            fn () => new CreateProductHandler(
                new MongoSaveProduct($this->get($container, ObjectManager::class)),
                $this->get($container, EventDispatcherDecorator::class),
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
            OutboxProcessManager::class,
            fn (): OutboxProcessManager => new OutboxProcessManager(
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

        $container->add(
            Application::class,
            function () use ($container): Application {
                $app = new Application();

                $app->add(
                    new CreateProductCliCommand(
                          $this->get($container, CommandBus::class),
                    ),
                );
                $app->add(
                    new DispatchEventsCliCommand(
                        $this->get($container, MongoEventReader::class),
                        $this->get($container, EventDispatcherInterface::class),
                    ),
                );

                return $app;
            },
        );

        $eventManager = $this->get($container, EventManager::class);
        $outboxProcessManager = $this->get($container, OutboxProcessManager::class);
        $eventManager->addEventListener([Events::prePersist], $outboxProcessManager);

        $eventDispatcher = $this->get($container, EventDispatcher::class);
        $eventDispatcher->subscribeTo(ProductCreated::class, new ProductCreatedListener());

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
