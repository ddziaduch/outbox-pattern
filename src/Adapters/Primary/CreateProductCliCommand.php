<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Adapters\Primary;

use ddziaduch\OutboxPattern\Application\Ports\Primary\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Ports\Secondary\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as SymfonyCliCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-product')]
final class CreateProductCliCommand extends SymfonyCliCommand
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('name', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        if (!is_string($name)) {
            $output->writeln('The product name must be a string');

            return SymfonyCliCommand::FAILURE;
        }

        try {
            $this->commandBus->execute(new CreateProductCommand($name));
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());

            return SymfonyCliCommand::FAILURE;
        }

        $output->writeln(
            sprintf(
                '%s: Product created',
                __CLASS__,
            ),
        );

        return SymfonyCliCommand::SUCCESS;
    }
}
