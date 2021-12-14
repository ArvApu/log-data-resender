<?php

declare(strict_types=1);

namespace App;

use App\LogParser\LogParserFactory;
use GetOpt\ArgumentException;
use GetOpt\GetOpt;
use GetOpt\Operand;

class Kernel
{
    public function __construct(
        private FilesManager $filesManager,
        private Sender $sender,
        private LogParserFactory $logParserFactory,
    ) {
    }

    public function run()
    {
        $input = $this->getCommandLineInput();

        $filepath = $input->getOperand('filepath');
        $apiKey   = $input->getOperand('api_key');

        $fileContent     = $this->filesManager->getFileContents($filepath);
        $parsedEventLogs = $this->logParserFactory->getParser('bo')->parse($fileContent['events']);

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

        $operandApiKey = new Operand('api_key', Operand::REQUIRED);
        $operandApiKey->setDescription('Api key used for authorization when doing HTTP requests.');
        $operandApiKey->setValidation('is_string');

        $operandFilePath = new Operand('filepath', Operand::REQUIRED);
        $operandFilePath->setDescription('Path to file with input data.');
        $operandFilePath->setValidation('is_string');

        $getopt->addOperands([
            $operandApiKey,
            $operandFilePath,
        ]);

        try {
            $getopt->process();
        } catch (ArgumentException $argumentException) {
            die($argumentException->getMessage() . PHP_EOL);
        }

        return $getopt;
    }
}