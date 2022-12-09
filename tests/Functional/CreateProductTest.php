<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Domain\ProductCreated;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use ddziaduch\OutboxPattern\Infrastructure\Doctrine\Documents\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use League\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/** @coversNothing  */
class CreateProductTest extends TestCase
{
    private const PRODUCT_NAME = 'fake-product';

    private ObjectManager $objectManager;
    private Application $application;
    /** @var ProductCreated[] */
    private array $interceptedEvents;
    /** @var ObjectRepository<Product> */
    private ObjectRepository $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerFactory())->create();

        $application = $container->get(Application::class);
        assert($application instanceof Application);
        $this->application = $application;

        $objectManager = $container->get(ObjectManager::class);
        assert($objectManager instanceof ObjectManager);

        $this->objectManager = $objectManager;
        $this->productRepository = $objectManager->getRepository(Product::class);

        $this->removeAllProductsFromDb();

        $this->interceptedEvents = [];

        $dispatcher = $container->get(EventDispatcher::class);
        assert($dispatcher instanceof EventDispatcher);

        $dispatcher->subscribeTo(
            ProductCreated::class,
            function (ProductCreated $event): void {
                $this->interceptedEvents[] = $event;
            },
        );

        ob_start();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        ob_end_clean();
    }

    public function testCreationDispatchesEvent(): void
    {
        $this->runCliCommand(
            'create-product',
            ['name' => self::PRODUCT_NAME],
        );

        self::assertCount(
            1,
            $this->productRepository->findAll(),
        );

        $this->runCliCommand('dispatch-events');

        self::assertCount(1, $this->interceptedEvents);
        $event = end($this->interceptedEvents);

        self::assertInstanceOf(ProductCreated::class, $event);
        self::assertSame(self::PRODUCT_NAME, $event->productName);
    }

    private function removeAllProductsFromDb(): void
    {
        foreach ($this->productRepository->findAll() as $product) {
            $this->objectManager->remove($product);
        }

        $this->objectManager->flush();
    }

    /**
     * @param mixed[] $input
     */
    private function runCliCommand(string $commandName, array $input = []): void
    {
        $command = $this->application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);
    }
}
