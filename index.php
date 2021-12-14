<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use App\FilesManager;
use App\Kernel;
use App\LogParser\LogParserFactory;
use App\Sender;
use GuzzleHttp\Client;

$app = new Kernel(
    new FilesManager(__DIR__),
    new Sender(new Client()),
    new LogParserFactory(),
);

$app->run();