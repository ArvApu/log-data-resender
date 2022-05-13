<?php

declare(strict_types=1);

namespace App\LogsProvider;

use App\Client\CloudWatch\CloudWatchClient;
use App\Client\CloudWatch\CloudWatchFilter;
use App\Client\DataDog\DataDogClient;
use App\Client\DataDog\DataDogFilter;
use App\FilesManager;

class LogsProvider
{
    public function __construct(
        private DataDogClient $dataDogClient,
        private CloudWatchClient $cloudWatchClient,
        private FilesManager $filesManager,
    ) {
    }

    public function getLogs(string $source, string $filter): iterable
    {
        return yield from match ($source) {
            'dd' => $this->dataDogClient->getLogs(DataDogFilter::fromJsonString($filter)),
            'cw' => $this->cloudWatchClient->getLogs(CloudWatchFilter::fromJsonString($filter)),
            'file' => $this->getLogsFromFile($filter),
            default => [],
        };
    }

    private function getLogsFromFile(string $filepath): array
    {
        $fileContents = $this->filesManager->getFileContents($filepath);

        return $fileContents['events'] ?? $fileContents['data'];
    }
}
