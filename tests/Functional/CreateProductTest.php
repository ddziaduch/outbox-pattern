<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Adapter\MongoEventReader;
use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\OutboxPattern\Application\CreateProductCommand */
/** @covers \ddziaduch\OutboxPattern\Application\CreateProductHandler */
/** @covers \ddziaduch\OutboxPattern\Infrastructure\EventDispatcherDecorator */
/** @covers \ddziaduch\OutboxPattern\Infrastructure\OutboxRelay */
/** @covers \ddziaduch\OutboxPattern\Adapter\MongoEventReader */
class CreateProductTest extends TestCase
{
    private ObjectManager $objectManager;
    private CommandBus $commandBus;
    private MongoEventReader $eventReader;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerFactory())->create();

        $commandBus = $container->get(CommandBus::class);
        assert($commandBus instanceof CommandBus);
        $this->commandBus = $commandBus;

        $objectManager = $container->get(ObjectManager::class);
        assert($objectManager instanceof ObjectManager);
        $this->objectManager = $objectManager;

        $eventReader = $container->get(MongoEventReader::class);
        assert($eventReader instanceof MongoEventReader);
        $this->eventReader = $eventReader;

        $this->removeAllProductsFromDb();
    }

    public function testCreationPutsEventsToOutbox(): void
    {
        $this->commandBus->execute(new CreateProductCommand('test'));
        self::assertCount(
            1,
            $this->objectManager
                ->getRepository(Product::class)
                ->findAll(),
        );
        $events = $this->eventReader->read();
        self::assertCount(1, $events);
    }

    private function removeAllProductsFromDb(): void
    {
        foreach ($this->objectManager->getRepository(Product::class)->findAll() as $product) {
            $this->objectManager->remove($product);
        }

        $this->objectManager->flush();
    }
}
