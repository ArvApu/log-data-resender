<?php

declare(strict_types=1);

namespace App\Service\LogProvider\Source;

use App\Client\DataDog\DataDogClient;

readonly class DataDogSource implements LogsProviderSourceInterface
{
    public function __construct(
        private DataDogClient $client,
    ) {
    }

    public static function getId(): string
    {
        return 'datadog';
    }

    public function getLogs(string $filter): iterable
    {
        return $this->client->getLogs($filter);
    }
}