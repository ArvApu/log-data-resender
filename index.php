<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

$app = new \App\Kernel(
    new \App\FilesManager(__DIR__),
    new \App\Sender(
        new \GuzzleHttp\Client()
    )
);

$app->run();