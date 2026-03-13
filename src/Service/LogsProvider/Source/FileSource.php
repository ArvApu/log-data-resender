<?php

declare(strict_types=1);

namespace App\Service\LogsProvider\Source;

use App\Service\FilesManager;

readonly class FileSource implements LogsProviderFileSourceInterface
{
    public function __construct(
        private FilesManager $filesManager
    ) {
    }

    public static function getId(): string
    {
        return 'file';
    }

    public function getLogs(string $filter): iterable
    {
        return $this->filesManager->getFileContents($filter);
    }
}