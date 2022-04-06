<?php

declare(strict_types=1);

namespace App\Client\DataDog;

use App\Constant\Value\DataDogEndpoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class DataDogClient
{
    private string $host = 'https://api.datadoghq.eu/api/v2'; // TODO configurable

    public function __construct(private Client $client)
    {
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLogs(Filter $filter): iterable
    {
        $logsCount = 0;

        $data = [
            'filter' => $filter,
            'page' => [
                'cursor' => null,
                'limit' => 100, // Max limit is 5000
            ],
        ];

        do {
            $response = $this->request(DataDogEndpoint::LOG_SEARCH, 'POST', $data);

            if ($response === null) {
                break;
            }

            foreach ($response['data'] ?? [] as $log) {
                $logsCount++;
                echo "\rLogs: {$logsCount}";

                yield $log;
            }

            $data['page']['cursor'] = $response['meta']['page']['after'] ?? null;

            // Needed for rate limit
            // TODO make dynamic, if program is running more than 3 seconds between requests skip sleep
            // TODO: also if cursor is null, that means it is last request so it should skip sleep also
            sleep(3);
        } while ($data['page']['cursor'] !== null);

        echo PHP_EOL;
    }

    protected function request(string $endpoint, string $method, array $data = []): ?array
    {
        try {
            $response = $this->client->request($method, $this->getUrl($endpoint), $this->getOptions($method, $data));
        } catch (RequestException $exception) {
            // TODO log this and continue working? return null;
            die($exception->getMessage());
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getUrl(string $endpoint): string
    {
        return "{$this->host}{$endpoint}";
    }

    private function getOptions(string $method, array $data): array
    {
        $options = [];

        if (empty($data)) {
            return $options;
        }

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $data;
            $options['headers'] = [
                // TODO: somehow get the key, maybe when creating a client add these headers
                'DD-API-KEY' => 'todo',
                'DD-APPLICATION-KEY' => 'todo ',
            ];
        } else {
            $options['query'] = $data;
        }

        return $options;
    }
}