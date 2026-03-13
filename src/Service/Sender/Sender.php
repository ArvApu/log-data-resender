<?php

declare(strict_types=1);

namespace App\Service\Sender;

use App\Constant\Enum\ResultCategory;
use App\Log\SenderLogger;
use App\Service\LogParser\ParsedLog;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class Sender
{
    private string $sessionId;

    public function __construct(
        #[Autowire(env: 'string:MASTER_KEY')]
        private readonly string $apiKey,
        private readonly HttpClientInterface $httpClient,
        private readonly SenderLogger $logger,
    ) {
    }

    /**
     * @param ParsedLog[] $parsedLogs
     */
    public function sendData(iterable $parsedLogs): ResultsAccumulator
    {
        $results = new ResultsAccumulator();

        try {
            foreach ($parsedLogs as $index => $parsedLog) {
                $results->increment(ResultCategory::COMPLETED);

                $this->progress($results->getCount(ResultCategory::COMPLETED));

                // Protects from accidentally changing data with update methods (PATCH/PUT).
                if ($parsedLog->isSecuredForPost() && $parsedLog->getMethod() !== Request::METHOD_POST) {
                    $results->increment(ResultCategory::NOT_POST);

                    continue;
                }

                if ($parsedLog->getMasterUserId() === null) {
                    $results->increment(ResultCategory::MISSING_MASTER_USER_ID);

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

                $this->sendParsedLog($parsedLog, $results, $index);
            }
        } catch (\Throwable $exception) {
            $results->setException($exception);
        } finally {
            echo PHP_EOL;

            unset($this->sessionId);

            return $results;
        }
    }

    public function sendParsedLog(ParsedLog $parsedLog, ResultsAccumulator $results, int $index): void
    {
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
            $results->increment(ResultCategory::FAILED);
            $content = null;
            $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;

            if ($response) {
                $content = json_decode($response->getContent(false));
            }

            if (isset($content->errors->id[0]) && str_contains($content->errors->id[0], 'has already been taken')) {
                $results->increment(ResultCategory::ALREADY_EXISTED);

                return;
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
