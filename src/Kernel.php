<?php

declare(strict_types=1);

namespace App;

use App\Parsers\JsonParser;
use GuzzleHttp\Client as Guzzle;

class Kernel
{
    private JsonParser $jsonParser;
    private Guzzle $guzzle;

    public function __construct(JsonParser $jsonParser, Guzzle $guzzle)
    {
        $this->jsonParser = $jsonParser;
        $this->guzzle = $guzzle;
    }

    public function run()
    {
        $options = getopt('f:i:', ['filepath:', 'index:']);

        $filepath = $options['f'] ?? $options['filepath'] ?? null;

        if ($filepath === null) {
            die('Path to file (filepath) is needed.' . PHP_EOL);
        }

        $fileContent = $this->getFileContents($filepath);

        $parsed = [];

        foreach ($fileContent['events'] as $event) {
            $info = json_decode($event['event']['json']['info']['info'] ?? '', true);

            if ($info === null) {
                continue;
            }

            parse_str($info['REQUEST_BODY'], $requestBodyObject);

            array_push($parsed, $this->jsonParser->decodeParametersFromObject($requestBodyObject));
        }

        $index = $options['i'] ?? $options['index'] ?? null;

        $json = $index === null ? json_encode($parsed) : json_encode($parsed[$index - 1]); // TODO: check if index is not out of bounds

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
}