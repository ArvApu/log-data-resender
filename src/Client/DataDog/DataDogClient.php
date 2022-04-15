<?php

declare(strict_types=1);

namespace App\Client\DataDog;

use App\Constant\Value\DataDogEndpoint;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class DataDogClient
{
    public function __construct(
        private Client $client,
        private string $host,
        private string $appKey,
        private string $apiKey,
    ) {
    }

    public function getLogs(Filter $filter): iterable
    {
        $data = [
            'filter' => $filter,
            'page' => [
                'cursor' => null,
                // Max limit is 5000, but we use smaller chunks to preserve memory
                'limit' => 100,
            ],
        ];

        do {
            $startTime = microtime(true);

            $response = $this->request(DataDogEndpoint::LOG_SEARCH, 'POST', $data);

            if ($response === null) {
                break;
            }

            foreach ($response['data'] ?? [] as $log) {
                yield $log;
            }

            $data['page']['cursor'] = $response['meta']['page']['after'] ?? null;

            $endTime = microtime(true);

            $executionTime = (int) round($endTime - $startTime);

            // Needed for rate limit
            $minimumExecutionTime = 3;

            if ($executionTime < $minimumExecutionTime && $data['page']['cursor'] !== null) {
                sleep($minimumExecutionTime - $executionTime);
            }
        } while ($data['page']['cursor'] !== null);
    }

    protected function request(string $endpoint, string $method, array $data = []): ?array
    {
        try {
            $response = $this->client->request($method, $this->getUrl($endpoint), $this->getOptions($method, $data));
        } catch (RequestException) {
            // TODO log this exception to logger
            return null;
        } catch (GuzzleException $e) {
            // This is critical exception so we must stop
            die($e->getMessage());
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
                'DD-API-KEY' => $this->apiKey,
                'DD-APPLICATION-KEY' => $this->appKey,
            ];
        } else {
            $options['query'] = $data;
        }

        return $options;
    }
}