<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Attribute\ServiceMetadata;
use App\Service\FileManager\FileManager;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'file'),
    ServiceMetadata(label: 'File', description: 'Provides logs from provided files.'),
]
readonly class FileSource implements LogsProviderFileSourceInterface
{
    public function __construct(
        private FileManager $filesManager
    ) {
    }

    public function getLogs(string $filter): iterable
    {
        return $this->filesManager->getFileContents($filter);
    }
}