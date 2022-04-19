<?php

declare(strict_types=1);

namespace App;

use App\Client\CloudWatch\CloudWatchClient;
use App\Client\CloudWatch\CloudWatchFilter;
use App\Client\DataDog\DataDogClient;
use App\Client\DataDog\DataDogFilter;
use App\LogsParser\LogsParser;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;
use GuzzleHttp\Client;

class Kernel
{
    public function __construct(
        private FilesManager $filesManager,
        private Sender       $sender,
        private LogsParser   $logsParser,
    ) {
    }

    public function run(): void
    {
        $input = $this->getCommandLineInput();

        $apiKey = $input->getOperand('api_key');

        $filepath     = $input->getOption('filepath');
        $parser       = $input->getOption('parser');
        $logsProvider = $input->getOption('logs_provider');
        $filter       = $input->getOption('filter');

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

        $results = $this->sender->setApiKey($apiKey)->sendData($parsedLogs);

        // Cleanup to save memory
        unset($parsedLogs);

        print_r($results->getCounts());
        $this->filesManager->putContentsToFile('_errors.json', json_encode($results->getErrors()));
        $this->filesManager->putContentsToFile('_no_mu_id.json', json_encode($results->getMeta('no_mu_id')));
    }

    private function getCommandLineInput(): GetOpt
    {
        $getopt = new GetOpt();

        // Operands
        $operandApiKey = new Operand('api_key', Operand::REQUIRED);
        $operandApiKey->setDescription('Api key used for authorization when doing HTTP requests.');
        $operandApiKey->setValidation('is_string');

        $getopt->addOperands([$operandApiKey]);

        // Options
        $optionCheckpoint = new Option('c', 'checkpoints');
        $optionCheckpoint->setDescription('Enable checkpoints between requests.');

        $optionParser = new Option('p', 'parser', GetOpt::REQUIRED_ARGUMENT);
        $optionParser->setDescription('Type of parser that should be used to parse logs.');
        $optionParser->setValidation(
            fn ($value) => in_array(
                $value,
                [
                    LogsParser::BO_LOG_TYPE_PARSER,
                    LogsParser::DD_LOG_TYPE_PARSER,
                    LogsParser::CLOUDWATCH_LOG_TYPE_PARSER,
                    LogsParser::POS_LOG_TYPE_PARSER,]
            ),
        );

        $optionFilePath = new Option('f', 'filepath', GetOpt::REQUIRED_ARGUMENT);
        $optionFilePath->setDescription('Path to file with input data.');
        $optionFilePath->setValidation('is_string');

        $optionLogsProvider = new Option('l', 'logs_provider', GetOpt::OPTIONAL_ARGUMENT);
        $optionLogsProvider->setDescription('If file path is not set DataDog (dd) will be used as default.');
        $optionLogsProvider->setDefaultValue('dd');
        $optionLogsProvider->setValidation(fn ($value) => in_array($value, ['dd', 'cw'])); // Todo: redo to enum

        $optionFilter = new Option(null, 'filter', GetOpt::OPTIONAL_ARGUMENT);
        $optionFilter->setDescription('Filter to use for logs provider');
        $optionFilter->setValidation('is_string');

        $getopt->addOptions([$optionCheckpoint, $optionParser, $optionFilePath, $optionLogsProvider, $optionFilter]);

        try {
            $getopt->process();
        } catch (ArgumentException $argumentException) {
            die($argumentException->getMessage() . PHP_EOL);
        }

        return $getopt;
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
            $client = new DataDogClient(
                new Client(),
                // TODO: take these values from configurations
                'https://api.datadoghq.eu/api/v2',
                '',
                '',
            );

            return yield from $client->getLogs(DataDogFilter::fromJsonString($filter));
        }

        if ($provider === 'cw') {
            $client = new CloudWatchClient(
                new CloudWatchLogsClient([
                    // TODO: take these values from configurations
                    'region'  => 'eu-west-1',
                    'version' => 'latest',
                    'credentials' => [
                        'key'    => '',
                        'secret' => '',
                    ],
                ])
            );

            return yield from $client->getLogs(CloudWatchFilter::fromJsonString($filter));
        }

        return yield from [];
    }
}
