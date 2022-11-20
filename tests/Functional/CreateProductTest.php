<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Tests\Functional;

use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use ddziaduch\OutboxPattern\Infrastructure\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class CreateProductTest extends TestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = (new ContainerFactory())->create();
    }

    public function testCreation(): void
    {
        $bus = $this->container->get(CommandBus::class);
        assert($bus instanceof CommandBus);
        $bus->execute(new CreateProductCommand('test'));
    }
}
