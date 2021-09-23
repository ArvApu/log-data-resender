<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

$app = new \App\Kernel(
    new \App\LogParsers\PosLogParser(),
    new \GuzzleHttp\Client()
);

$app->run();