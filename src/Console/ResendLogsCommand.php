<?php

declare(strict_types=1);

namespace App\Console;

use App\Service\LogResender\LogResender;
use App\Service\Sender\Sender;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:resend-logs', description: 'Command resends requests from failed logs')]
class ResendLogsCommand extends Command
{
    public function __construct(
        private LogResender $logResender,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'parser',
            shortcut: 'p',
            mode: InputOption::VALUE_REQUIRED,
            description: 'The specific type of parser that will be used to parse logs (adds minimal optimisation)',
        );

        $this->addOption(
            name: 'filter',
            shortcut: 'f',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Filter to use for logs provider (for source: "file", this is a filepath)',
        );

        $this->addOption(
            name: 'modifiers',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'Comma-separated list of modifiers to use for logs modification before parsing from source',
        );

        $this->addOption(
            name: 'source',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'A source from where logs should be extracted',
        );
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        Sender::toggleProgressTracking(true);

        $filter = $input->getOption('filter');
        $source = $input->getOption('source');
        $parser = $input->getOption('parser');
        $modifiers = array_filter(array_map('trim', explode(',', $input->getOption('modifiers') ?? '')));

        // Not using arguments so that command would be more readable and easier to use.
        if ($source === null) {
            throw new \Exception('Source option is required');
        }

        if ($parser === null) {
            throw new \Exception('Parser option is required');
        }

        $results = $this->logResender->resend($source, $filter, $parser, $modifiers);

        foreach ($results->getCounts() ?? [] as $id => $count) {
            $output->writeln("<info>{$id}: {$count}</info>");
        }

        if ($results->getException() !== null) {
            $output->writeln("<error>{$results->getException()->getMessage()}</error>");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
