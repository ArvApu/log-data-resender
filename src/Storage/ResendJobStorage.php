<?php

declare(strict_types=1);

namespace App\Storage;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class ResendJobStorage
{
    public function __construct(
        #[Autowire(param: 'app.storage.local.resend_job_path')]
        private string $baseDir,
    ) {
    }

    public function storeUploadedFilter(UploadedFile $file, int $jobId): string
    {
        $targetDir = "{$this->baseDir}/{$jobId}";

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException("Failed to create job directory: {$targetDir}");
        }

        $extension = $file->guessExtension();
        $filename = $extension === null ? 'filter' : "filter.{$extension}";

        $movedFile = $file->move($targetDir, $filename);

        return $movedFile->getPathname();
    }
}
