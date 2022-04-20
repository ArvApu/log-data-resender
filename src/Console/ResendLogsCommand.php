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
        $this->addOption('checkpoints', 'c', InputOption::VALUE_REQUIRED, 'Enable checkpoints between requests.');
        $this->addOption('parser', 'p', InputOption::VALUE_REQUIRED, 'Type of parser that should be used to parse logs.');
        $this->addOption(name: 'filepath', mode: InputOption::VALUE_REQUIRED, description: 'Path to file with input data.');
        $this->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Filter to use for logs provider');

        $this->addOption(
            'logs_provider',
            'lp',
            InputOption::VALUE_OPTIONAL,
            'If file path is not set DataDog (dd) will be used as default.',
            'dd' // TODO: dd as const value
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filepath = $input->getOption('filepath');
        $parser = $input->getOption('parser');
        $logsProvider = $input->getOption('logs_provider');
        $filter = $input->getOption('filter');

        // TODO: validate input;

        if ($parser !== null) {
            $this->logsParser->setParsingStrategy($parser);
        }

        if ($input->getOption('checkpoints') !== null) {
            $this->sender->useCheckpoints();
        }

        $logs = ($filepath !== null)
            ? $this->getLogsFromFile($filepath)
            : $this->getLogsFromProvider($logsProvider, $filter);

        $parsedLogs = $this->logsParser->parse($logs);

        if (empty($parsedLogs)) {
            die('No logs to resend' . PHP_EOL);
        }

        $this->filesManager->putContentsToFile('_last.json', json_encode($parsedLogs));
        $this->filesManager->putContentsToFile(basename($filepath), json_encode($parsedLogs));

        try {
            $results = $this->sender->sendData($parsedLogs);
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }

        // Cleanup to save memory
        unset($parsedLogs);

        print_r($results->getCounts());
        $this->filesManager->putContentsToFile('_errors.json', json_encode($results->getErrors()));
        $this->filesManager->putContentsToFile('_no_mu_id.json', json_encode($results->getMeta('no_mu_id')));

        return Command::SUCCESS;
    }

    private function getLogsFromFile(string $filepath): iterable
    {
        $fileContents = $this->filesManager->getFileContents($filepath);

        return yield from ($fileContents['events'] ?? $fileContents['data']);
    }

    private function getLogsFromProvider(string $provider, ?string $filter = null): iterable
    {
        // TODO solve empty filter problem - there should always be filter if filepath not provide aka logs should be fetch from provider
        if ($filter === null) {
            return yield from [];
        }

        // TODO: create factory that selects and creates client, clients should implement interface
        if ($provider === 'dd') {
            return yield from $this->dataDogClient->getLogs(DataDogFilter::fromJsonString($filter));
        }

        if ($provider === 'cw') {
            return yield from $this->cloudWatchClient->getLogs(CloudWatchFilter::fromJsonString($filter));
        }

        return yield from [];
    }
}