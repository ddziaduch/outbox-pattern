<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Infrastructure;

use ddziaduch\OutboxPattern\Adapters\Primary\CreateProductCliCommand;
use ddziaduch\OutboxPattern\Adapters\Primary\DispatchEventsCliCommand;
use ddziaduch\OutboxPattern\Adapters\Secondary\MongoEventReader;
use ddziaduch\OutboxPattern\Adapters\Secondary\MongoSaveProduct;
use ddziaduch\OutboxPattern\Adapters\Secondary\Outbox;
use ddziaduch\OutboxPattern\Adapters\Secondary\TacticianCommandBus;
use ddziaduch\OutboxPattern\Application\CommandHandlers\CreateProductHandler;
use ddziaduch\OutboxPattern\Application\EventListeners\ProductCreatedListener;
use ddziaduch\OutboxPattern\Application\Events\ProductCreated;
use ddziaduch\OutboxPattern\Application\Ports\Primary\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Ports\Secondary\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\DocumentManagerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareClassMetadata;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\OutboxAwareRepositories;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Events;
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
            DocumentManager::class,
            fn (): DocumentManager => (new DocumentManagerFactory())->create(
                $this->get($container, Client::class),
                $this->get($container, EventManager::class),
            ),
        );

        $container->addShared(EventDispatcher::class);

        $container->addShared(
            EventDispatcherInterface::class,
            fn(): EventDispatcherInterface => $this->get(
                $container,
                EventDispatcher::class,
            ),
        );

        $container->addShared(
            CreateProductHandler::class,
            fn () => new CreateProductHandler(
                new MongoSaveProduct($this->get($container, DocumentManager::class)),
                $this->get($container, Outbox::class),
            ),
        );

        $container->addShared(
            CommandBus::class,
            fn (): TacticianCommandBus => new TacticianCommandBus(
                (new TacticianCommandBusFactory())->create(
                    new ContainerLocator($container, [
                        CreateProductCommand::class => CreateProductHandler::class,
                    ]),
                ),
            )
        );

        $container->addShared(Outbox::class);

        $container->addShared(
            OutboxAwareClassMetadata::class,
            fn (): OutboxAwareClassMetadata => new OutboxAwareClassMetadata(
                $this->get($container, DocumentManager::class),
            ),
        );

        $container->addShared(
            OutboxAwareRepositories::class,
            fn (): OutboxAwareRepositories => new OutboxAwareRepositories(
                $this->get($container, DocumentManager::class),
                $this->get($container, OutboxAwareClassMetadata::class),
            ),
        );

        $container->addShared(
            MongoEventReader::class,
            fn (): MongoEventReader => new MongoEventReader(
                $this->get($container, OutboxAwareRepositories::class),
                $this->get($container, DocumentManager::class),
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
        $outbox = $this->get($container, Outbox::class);
        $eventManager->addEventListener([Events::prePersist], $outbox);

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
