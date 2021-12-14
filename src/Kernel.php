<?php

declare(strict_types=1);

namespace App;

use App\LogParser\LogParserFactory;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;

class Kernel
{
    public function __construct(
        private FilesManager $filesManager,
        private Sender $sender,
        private LogParserFactory $logParserFactory,
    ) {
    }

    public function run(): void
    {
        $input = $this->getCommandLineInput();

        $apiKey     = $input->getOperand('api_key');
        $parserType = $input->getOperand('parser');
        $filepath   = $input->getOperand('filepath');

        if ($input->getOption('checkpoints') !== null) {
            $this->sender->useCheckpoints();
        }

        $fileContent     = $this->filesManager->getFileContents($filepath);
        $parsedEventLogs = $this->logParserFactory->getParser($parserType)->parse($fileContent['events']);

        $this->filesManager->putContentsToFile('_last.json', json_encode($parsedEventLogs));
        $this->filesManager->putContentsToFile(basename($filepath), json_encode($parsedEventLogs));

        // Cleanup to save memory;
        unset($fileContent);

        $results = $this->sender->setApiKey($apiKey)->sendData($parsedEventLogs);

        // Cleanup to save memory;
        unset($parsedEventLogs);

        print_r($results->getCounts());
        $this->filesManager->putContentsToFile('_errors.json', json_encode($results->getErrors()));
        $this->filesManager->putContentsToFile('_no_mu_id.json', json_encode($results->getMeta('no_mu_id')));
    }

    private function getCommandLineInput(): GetOpt
    {
        $getopt = new GetOpt();

        /* Operands */
        $operandApiKey = new Operand('api_key', Operand::REQUIRED);
        $operandApiKey->setDescription('Api key used for authorization when doing HTTP requests.');
        $operandApiKey->setValidation('is_string');

        $operandParser = new Operand('parser', Operand::REQUIRED);
        $operandParser->setDescription('Type of parser that should be used to parse logs.');
        $operandParser->setValidation('is_string');

        $operandFilePath = new Operand('filepath', Operand::REQUIRED);
        $operandFilePath->setDescription('Path to file with input data.');
        $operandFilePath->setValidation('is_string');

        $getopt->addOperands([$operandApiKey, $operandParser, $operandFilePath]);

        /* Options */
        $optionCheckpoint = new Option('c', 'checkpoints');
        $optionCheckpoint->setDescription('Enable checkpoints between requests.');

        $getopt->addOptions([$optionCheckpoint]);

        try {
            $getopt->process();
        } catch (ArgumentException $argumentException) {
            die($argumentException->getMessage() . PHP_EOL);
        }

        return $getopt;
    }
}