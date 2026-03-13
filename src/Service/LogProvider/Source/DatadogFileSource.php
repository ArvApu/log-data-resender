<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Service\FileManager\FileManager;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'datadog_file')]
readonly class DatadogFileSource implements LogsProviderFileSourceInterface
{
    public function __construct(
        private FileManager $filesManager
    ) {
    }

    public function getLogs(string $filter): iterable
    {
        $fileContents = $this->filesManager->getFileContents($filter);

        return $fileContents['events'] ?? $fileContents['data'];
    }
}