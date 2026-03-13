<?php

declare(strict_types=1);

namespace App\Service\Sender;

use App\Log\SenderLogger;
use App\Service\LogParser\ParsedLog;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Sender
{
    private string $sessionId;
    private bool $withCheckpoints = false;

    public function __construct(
        #[Autowire(env: 'string:MASTER_KEY')]
        private readonly string $apiKey,
        private readonly HttpClientInterface $httpClient,
        private readonly SenderLogger $logger,
    ) {
    }

    /**
     * @param ParsedLog[] $parsedLogs
     * @throws \Exception
     */
    public function sendData(iterable $parsedLogs): ResultsAccumulator
    {
        $results = new ResultsAccumulator();

        foreach ($parsedLogs as $index => $parsedLog) {
            $results->increment('completed');

            $this->progress($results->getCount('completed'));

            // Protects from accidentally changing data with update methods (PATCH/PUT).
            if ($parsedLog->isSecuredForPost() && $parsedLog->getMethod() !== 'POST') {
                $results->increment('not_a_post_request');

                continue;
            }

            if ($parsedLog->getMasterUserId() === null) {
                $results->increment('missing_master_user_id');

                $this->logger->warning(
                    'Missing master user id',
                    [
                        'failed_at' => $index,
                        'model_id' => $parsedLog->getModelId(),
                        'master_user_id' => null,
                        'session' => $this->getSessionId(),
                    ],
                );

                continue;
            }

            if (!$this->checkpoint($parsedLog)) {
                $results->increment('skipped');

                continue;
            }

            try {
                $response = $this->httpClient->request(
                    $parsedLog->getMethod(),
                    $parsedLog->getUrl(),
                    [
                        'body' => $parsedLog->getBody(),
                        'headers' => $this->getHeaders($parsedLog),
                    ],
                );

                // To ensure the request is actually sent and to trigger any potential exceptions related to the request.
                $response->getHeaders();
            } catch (TransportExceptionInterface|HttpExceptionInterface $e) {
                $results->increment('failed');
                $content = null;
                $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

                if ($response) {
                    $content = json_decode($response->getContent(false));
                }

                if (isset($content->errors->id[0]) && str_contains($content->errors->id[0], 'has already been taken')) {
                    $results->increment('failed_already_existed');

                    continue;
                }

                $this->logger->error(
                    $content->error ?? 'Failed to send request',
                    [
                        'failed_at' => $index,
                        'model_id' => $parsedLog->getModelId(),
                        'master_user_id' => $parsedLog->getMasterUserId(),
                        'session' => $this->getSessionId(),
                        'response_data' => [
                            'error' => $content->error ?? '',
                            'errors' => $content->errors ?? '',
                            'error_code' => $content->errorCode ?? '',
                        ],
                    ],
                );
            }
        }

        echo PHP_EOL;

        unset($this->sessionId);

        return $results;
    }

    public function useCheckpoints(): self
    {
        $this->withCheckpoints = true;

        return $this;
    }

    /**
     * @return bool return true to pass this checkpoint, false to stop
     */
    private function checkpoint(?ParsedLog $parsedEventLog = null): bool
    {
        if (!$this->withCheckpoints || $parsedEventLog === null) {
            return true;
        }

        echo PHP_EOL .
            'Going to do ' . $parsedEventLog->getMethod() .
            ' request to ' . $parsedEventLog->getUrl() .
            ' url for ' . $parsedEventLog->getMasterUserId() . PHP_EOL;

        $pb = "id = \"{$parsedEventLog->getModelId()}\" and master_user_id = \"{$parsedEventLog->getMasterUserId()}\"";

        echo $pb . PHP_EOL;
        shell_exec('echo ' . escapeshellarg($pb) . ' | pbcopy');

        echo 'Continue? [yes/no/abort]' . PHP_EOL;

        $input = trim((string) fgets(STDIN));

        if ($input === 'abort') {
            die('Aborted' . PHP_EOL);
        }

        return $input === 'yes' || $input === 'y';
    }

    private function progress(int $done): void
    {
        echo "\rProgress: {$done}";
    }

    private function getHeaders(ParsedLog $parsedLog): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Cookie' => "PHPSESSID={$this->getSessionId()}",
            'Wallmob-Api-Key' => $this->apiKey,
            'Wallmob-Overwrite-Id' => $parsedLog->getMasterUserId(),
        ];
    }

    private function getSessionId(): string
    {
        $this->sessionId ??= (string) session_create_id();

        return $this->sessionId;
    }
}
