<?php

declare(strict_types=1);

namespace App\Console;

use App\Client\CloudWatch\CloudWatchClient;
use App\Client\CloudWatch\CloudWatchFilter;
use App\Client\DataDog\DataDogClient;
use App\Client\DataDog\DataDogFilter;
use App\FilesManager;
use App\LogsParser\LogsParser;
use App\Sender;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResendLogsCommand extends Command
{
    public function __construct(
        private FilesManager     $filesManager,
        private Sender           $sender,
        private LogsParser       $logsParser,
        private DataDogClient    $dataDogClient,
        private CloudWatchClient $cloudWatchClient,
    )
    {
        parent::__construct();
    }

    protected static $defaultName = 'app:resend-logs';

    protected function configure(): void
    {
        $this->addOption(
            name: 'checkpoints',
            shortcut: 'c',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Enable checkpoints between requests.',
        );

        $this->addOption(
            name: 'parser',
            shortcut: 'p',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Type of parser that should be used to parse logs.',
        );

        $this->addOption(
            name: 'filter',
            shortcut: 'f',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Filter to use for logs provider. If it is file logs provider this should filepath.',
        );

        $this->addOption(
            name: 'logs-provider',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'If file path is not set DataDog (dd) will be used as default.',
            // TODO: dd as const/enum value. Other possible values are: cw (cloudwatch), dd (data dog) and file
            default: 'dd',
        );

        // TODO: validate input
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parser = $input->getOption('parser');
        $logsProvider = $input->getOption('logs-provider');
        $filter = $input->getOption('filter');

        if ($parser !== null) {
            $this->logsParser->setParsingStrategy($parser);
        }

        if ($input->getOption('checkpoints') !== null) {
            $this->sender->useCheckpoints();
        }

        $logs = $this->getLogs($logsProvider, $filter);

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

    private function getLogs(string $provider, string $filter): iterable
    {
        // TODO: create factory that selects and creates client, clients should implement interface
        if ($provider === 'dd') {
            return yield from $this->dataDogClient->getLogs(DataDogFilter::fromJsonString($filter));
        }

        if ($provider === 'cw') {
            return yield from $this->cloudWatchClient->getLogs(CloudWatchFilter::fromJsonString($filter));
        }

        if ($provider === 'file') {
            $fileContents = $this->filesManager->getFileContents($filter);

            return yield from ($fileContents['events'] ?? $fileContents['data']);
        }

        return yield from [];
    }
}