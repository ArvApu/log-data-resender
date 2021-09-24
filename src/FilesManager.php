<?php

declare(strict_types=1);

namespace App;

class FilesManager
{
    private const DEFAULT_OUTPUT_DIRECTORY = 'output';

    private string $outputDir;
    private string $projectRootPath;

    public function __construct(string $projectRootPath, ?string $outputDir = null)
    {
        $this->projectRootPath = $projectRootPath;
        $this->outputDir = $outputDir ?? self::DEFAULT_OUTPUT_DIRECTORY;
    }

    public function putContentsToFile(string $filename, $content)
    {
        if (!file_put_contents("$this->projectRootPath/$this->outputDir/$filename", $content)) {
            die("Oops! Error creating {$filename} file..." . PHP_EOL);
        }
    }

    public function getFileContents(string $filepath): array
    {
        $file = file_get_contents($filepath);
        return json_decode($file, true);
    }
}