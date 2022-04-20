<?php

declare(strict_types=1);

namespace App;

class FilesManager
{
    private const DEFAULT_OUTPUT_DIRECTORY = 'output';

    public function __construct(
        private string $projectDir,
        private string $outputDir = self::DEFAULT_OUTPUT_DIRECTORY,
    ) {
    }

    public function putContentsToFile(string $filename, mixed $content): void
    {
        if (!file_put_contents("$this->projectDir/$this->outputDir/$filename", $content)) {
            die("Oops! Error creating {$filename} file..." . PHP_EOL);
        }
    }

    public function getFileContents(string $filepath): array
    {
        $file = file_get_contents($filepath);

        if ($file === false) {
            return [];
        }

        return json_decode($file, true);
    }
}
