<?php

declare(strict_types=1);

namespace App;

use App\LogParsers\POSLogParser;

class Kernel
{
    private FilesManager $filesManager;
    private Sender $sender;

    public function __construct(FilesManager $filesManager, Sender $sender)
    {
        $this->filesManager = $filesManager;
        $this->sender = $sender;
    }

    public function run()
    {
        $options = getopt('f:k:', ['filepath:', 'key:']);

        $filepath = $options['f'] ?? $options['filepath'] ?? null;
        $apiKey   = $options['k'] ?? $options['key'] ?? null;

        if ($filepath === null) {
            die('Path to file (filepath) is needed.' . PHP_EOL);
        }

        $fileContent     = $this->filesManager->getFileContents($filepath);
        $parsedEventLogs = (new POSLogParser())->parse($fileContent['events']);

        $this->filesManager->putContentsToFile('_last.json', json_encode($parsedEventLogs));
        $this->filesManager->putContentsToFile(basename($filepath), json_encode($parsedEventLogs));

        // Cleanup to save memory;
        unset($fileContent, $json);

        $results = $this->sender->setApiKey($apiKey)->sendData($parsedEventLogs);

        print_r($results->getCounts());
        $this->filesManager->putContentsToFile('_errors.json', json_encode($results->getErrors()));
        $this->filesManager->putContentsToFile('_no_mu_id.json', json_encode($results->getMeta('no_mu_id')));
    }
}