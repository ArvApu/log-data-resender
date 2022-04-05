<?php

declare(strict_types=1);

namespace App;

use App\LogsParser\LogsParser;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;

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

        $apiKey     = $input->getOperand('api_key');
        $filepath   = $input->getOperand('filepath');

        $parser = $input->getOption('parser');

        if ($parser !== null) {
            $this->logsParser->setParsingStrategy($parser);
        }

        if ($input->getOption('checkpoints') !== null) {
            $this->sender->useCheckpoints();
        }

        $parsedLogs = $this->logsParser->parse($this->getLogs($filepath));

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

        $operandFilePath = new Operand('filepath', Operand::REQUIRED);
        $operandFilePath->setDescription('Path to file with input data.');
        $operandFilePath->setValidation('is_string');

        $getopt->addOperands([$operandApiKey, $operandFilePath]);

        // Options
        $optionCheckpoint = new Option('c', 'checkpoints');
        $optionCheckpoint->setDescription('Enable checkpoints between requests.');

        $optionParser = new Option('p', 'parser', GetOpt::REQUIRED_ARGUMENT);
        $optionParser->setDescription('Type of parser that should be used to parse logs.');
        $optionParser->setValidation('is_string');

        $getopt->addOptions([$optionCheckpoint, $optionParser]);

        try {
            $getopt->process();
        } catch (ArgumentException $argumentException) {
            die($argumentException->getMessage() . PHP_EOL);
        }

        return $getopt;
    }

    private function getLogs(string $filepath): iterable
    {
        $fileContents = $this->filesManager->getFileContents($filepath);

        return yield from ($fileContents['events'] ?? $fileContents['data']);
    }
}
