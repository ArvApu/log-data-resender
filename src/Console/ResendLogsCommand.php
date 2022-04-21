<?php

declare(strict_types=1);

namespace App\Console;

use App\FilesManager;
use App\LogsParser\LogsParser;
use App\LogsProvider\LogsProvider;
use App\Sender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResendLogsCommand extends Command
{
    public function __construct(
        private FilesManager $filesManager,
        private Sender       $sender,
        private LogsParser   $logsParser,
        private LogsProvider $logsProvider
    ) {
        parent::__construct();
    }

    protected static $defaultName = 'app:resend-logs';
    protected static $defaultDescription = 'Command resends requests from failed logs';

    protected function configure(): void
    {
        $this->addOption(
            name: 'checkpoints',
            shortcut: 'c',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Enable checkpoints between requests',
        );

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
            name: 'source',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'A source from where logs should be extracted.',
            // TODO: dd as const/enum value. Other possible values are: cw (cloudwatch), dd (data dog) and file
            default: 'dd',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->prepare($input);

        $filter = $input->getOption('filter');
        $source = $input->getOption('source');

        $logs = $this->logsProvider->getLogs($source, $filter);

        $parsedLogs = $this->logsParser->parse($logs);

        try {
            $results = $this->sender->sendData($parsedLogs);
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());

            return Command::FAILURE;
        }

        // Cleanup to save memory
        unset($parsedLogs);

        foreach ($results->getCounts() as $id => $count) {
            $output->writeln("<info>{$id}: {$count}</info>");
        }

        $this->filesManager->putContentsToFile('_errors.json', json_encode($results->getErrors()));

        return Command::SUCCESS;
    }

    private function prepare(InputInterface $input): void
    {
        if ($input->getOption('parser') !== null) {
            $this->logsParser->setParsingStrategy($input->getOption('parser'));
        }

        if ($input->getOption('checkpoints') !== null) {
            $this->sender->useCheckpoints();
        }
    }
}