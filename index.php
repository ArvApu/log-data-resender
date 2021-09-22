<?php

declare(strict_types=1);

require('./src/JsonParser.php');

$options = getopt('f:i:', ['filepath:', 'index:']);

$filepath = $options['f'] ?? $options['filepath'] ?? null;

if ($filepath === null) {
    die('Path to file (filepath) is needed.' . PHP_EOL);
}

$fileContent = getFileContents($filepath);

$parsed = [];

$jsonParser = new JsonParser();

foreach ($fileContent['events'] as $event) {
    $info = json_decode($event['event']['json']['info']['info'], true);

    parse_str($info['REQUEST_BODY'], $requestBodyObject);

    array_push($parsed, $jsonParser->decodeParametersFromObject($requestBodyObject));
}

$index = $options['i'] ?? $options['index'] ?? null;

$json = $index === null ? json_encode($parsed) : json_encode($parsed[$index - 1]); // TODO: check if index is not out of bounds

putContentsToFile('./output/_last.json', $json);
putContentsToFile('./output/' . basename($filepath), $json);

if ($index === null) {
    echo 'JSON file created successfully.' . PHP_EOL;
} else {
    echo $parsed[$index - 1]['id'];
}

function getFileContents(string $filepath)
{
    $file = file_get_contents($filepath);
    return json_decode($file, true);
}

function putContentsToFile(string $filename, $content)
{
    if (!file_put_contents($filename, $content)) {
        die("Oops! Error creating {$filename} file..." . PHP_EOL);
    }
}