<?php

declare(strict_types=1);

namespace ddziaduch\OutboxPattern\Presentation;

use ddziaduch\OutboxPattern\Application\CreateProductCommand;
use ddziaduch\OutboxPattern\Application\Port\CommandBus;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as CliCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'create-product')]
final class CreateProductCliCommand extends CliCommand
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

        try {
            $this->commandBus->execute(new CreateProductCommand($name));
        } catch (\Throwable $exception) {
            $output->writeln($exception->getMessage());

            return CliCommand::FAILURE;
        }

        $output->writeln('Product created');

        return CliCommand::SUCCESS;
    }
}
