<?php

declare(strict_types=1);

namespace App\Client\DataDog;

use App\Constant\Value\DataDogEndpoint;
use App\Service\LogsProvider\Source\LogsProviderSourceInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

readonly class DataDogClient implements LogsProviderSourceInterface
{
    public function __construct(
        #[Autowire(param: 'app.client.datadog.host')]
        private string $host,
        #[Autowire(env: 'string:DD_APP_KEY')]
        private string $appKey,
        #[Autowire(env: 'string:DD_API_KEY')]
        private string $apiKey,
        private HttpClientInterface $httpClient,
    ) {
    }

    public static function getId(): string
    {
        return 'datadog';
    }

    /**
     * @throws \Exception
     */
    public function getLog(string $filter): ?array
    {
        $data = json_decode($filter, true);

        if ($data === null) {
            throw new \Exception('Invalid json string provided for data dog filter');
        }

        $data['filter'] ??= $data;

        $data['page'] = [
            'cursor' => null,
            'limit' => 1,
        ];

        $response = $this->request(DataDogEndpoint::LOG_SEARCH, Request::METHOD_POST, $data);

        $headers = $response['headers'];
        $response = json_decode($response['content'], true);

        if (((int) $headers['x-ratelimit-remaining'][0]) > 0) {
            return $response['data'][0] ?? null;
        }

        // Adding one second buffer to avoid rare occurrences of rate limit not being reset yet
        sleep(((int) $headers['x-ratelimit-reset'][0]) + 1);

        return $response['data'][0] ?? null;
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

            $headers = $response['headers'];
            $response = json_decode($response['content'], true);

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

    protected function request(string $endpoint, string $method, array $data = []): array
    {
        $url = $this->getUrl($endpoint);
        $options = $this->getOptions($method, $data);

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $headers = $response->getHeaders();
            $content = $response->getContent();

            return [
                'headers' => $headers,
                'content' => $content,
            ];

        } catch (TransportExceptionInterface | HttpExceptionInterface $e) {
            // TODO: log this exception to logger and then return null or stop whole process?
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
