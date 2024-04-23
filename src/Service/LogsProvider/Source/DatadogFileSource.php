<?php

declare(strict_types=1);

namespace App\Service\LogsProvider\Source;

use App\Service\FilesManager;

readonly class DatadogFileSource implements LogsProviderSourceInterface
{
    public function __construct(
        private FilesManager $filesManager
    ) {
    }

    public static function getId(): string
    {
        return 'datadog_file';
    }

    public function getLogs(string $filter): iterable
    {
        $fileContents = $this->filesManager->getFileContents($filter);

        return $fileContents['events'] ?? $fileContents['data'];
    }
}