<?php

declare(strict_types=1);

namespace App\Client\DataDog;

use App\Constant\Value\DataDogEndpoint;
use App\Service\LogsProvider\Source\LogsProviderSourceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class DataDogClient implements LogsProviderSourceInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $host,
        private readonly string $appKey,
        private readonly string $apiKey,
    ) {
    }

    public static function getId(): string
    {
        return 'datadog';
    }

    /**
     * @throws \Exception
     */
    public function getLogs(string $filter): iterable
    {
        $data = json_decode($filter, true);

        if ($data === null) {
            throw new \Exception('Invalid json string provided for data dog filter');
        }

        $data['filter'] ??= $data;

        $data['page'] = [
            'cursor' => null,
            // Max limit is 5000, but we use smaller chunks to preserve memory
            'limit' => 1000,
        ];

        $data['sort'] = 'timestamp';

        do {
            $response = $this->request(DataDogEndpoint::LOG_SEARCH, Request::METHOD_POST, $data);

            $headers = $response->getHeaders();
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response === null) {
                break;
            }

            foreach ($response['data'] ?? [] as $log) {
                yield $log;
            }

            $data['page']['cursor'] = $response['meta']['page']['after'] ?? null;

            if (((int) $headers['x-ratelimit-remaining'][0]) > 0 || $data['page']['cursor'] === null) {
                continue;
            }

            // Adding one second buffer to avoid rare occurrences of rate limit not being reset yet
            sleep(((int) $headers['x-ratelimit-reset'][0]) + 1);
        } while ($data['page']['cursor'] !== null);
    }

    protected function request(string $endpoint, string $method, array $data = []): ResponseInterface
    {
        try {
            return $this->client->request($method, $this->getUrl($endpoint), $this->getOptions($method, $data));
        } catch (RequestException $e) {
            // TODO: log this exception to logger and then return null or stop whole process?
            die($e->getMessage());
        } catch (GuzzleException $e) {
            // This is critical exception so we must stop
            die($e->getMessage());
        }
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

        $options['headers'] = [
            'DD-API-KEY' => $this->apiKey,
            'DD-APPLICATION-KEY' => $this->appKey,
        ];

        if (in_array($method, [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH])) {
            $options['json'] = $data;
        } else {
            $options['query'] = $data;
        }

        return $options;
    }
}
