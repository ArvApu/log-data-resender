<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Service\FilesManager;

readonly class DatadogFileSource implements LogsProviderFileSourceInterface
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