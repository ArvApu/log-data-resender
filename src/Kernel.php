<?php

declare(strict_types=1);

namespace App;

use App\LogParsers\LogParser;
use App\LogParsers\ParsedLog;
use GuzzleHttp\Client as Guzzle;

class Kernel
{
    private LogParser $logParser;
    private Guzzle $guzzle;

    public function __construct(LogParser $logParser, Guzzle $guzzle)
    {
        $this->logParser = $logParser;
        $this->guzzle = $guzzle;
    }

    public function run()
    {
        $options = getopt('f:i:', ['filepath:', 'index:']);

        $filepath = $options['f'] ?? $options['filepath'] ?? null;
        $index    = $options['i'] ?? $options['index'] ?? null;

        if ($index !== null) {
            $index = (int) $index;
        }

        if ($filepath === null) {
            die('Path to file (filepath) is needed.' . PHP_EOL);
        }

        $fileContent = $this->getFileContents($filepath);

        $parsedEventLogs = $this->logParser->parse($fileContent['events']);

        $json = $this->transformToJson($parsedEventLogs, $index);

        $this->putContentsToFile('./output/_last.json', $json);
        $this->putContentsToFile('./output/' . basename($filepath), $json);
    }

    private function getFileContents(string $filepath)
    {
        $file = file_get_contents($filepath);
        return json_decode($file, true);
    }

    private function putContentsToFile(string $filename, $content)
    {
        if (!file_put_contents($filename, $content)) {
            die("Oops! Error creating {$filename} file..." . PHP_EOL);
        }
    }

    /**
     * @param ParsedLog[] $parsed
     * @param int|null $index
     * @return string
     */
    private function transformToJson(array $parsed, ?int $index = null): string
    {
        if ($index === null) {
            return json_encode($parsed);
        }

        if (!isset($parsed[$index - 1])) {
            die('Index is out of bounds.');
        }

        return json_encode($parsed[$index - 1]);
    }
}