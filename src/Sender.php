<?php

declare(strict_types=1);

namespace App;

use App\LogsParser\ParsedLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Sender
{
    private string $sessionId;
    private bool $withCheckpoints = false;

    public function __construct(
        private Client $client,
        private string $apiKey,
    ) {
    }

    /**
     * @param ParsedLog[] $parsedLogs
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendData(iterable $parsedLogs): ResultsAccumulator
    {
        $results = new ResultsAccumulator();

        foreach ($parsedLogs as $index => $parsedLog) {
            $results->increment('completed');

            $this->progress($results->getCount('completed'));

            // TODO: Log this parsed log with session id to database(sql/nosql) or logging system

            // Protects from accidentally changing data with update methods (PATCH/PUT).
            if ($parsedLog->getMethod() !== 'POST') {
                $results->increment('not_a_post_request');
                continue;
            }

            if ($parsedLog->getMasterUserId() === null) {
                $results->increment('missing_master_user_id');

                // TODO: do not log into memory, but into database or straight to file
                $results->addError([
                    'failed_at' => $index,
                    'id' => $parsedLog->getModelId(),
                    'master_user_id' => null,
                    'session' => $this->getSessionId(),
                ]);

                continue;
            }

            if (!$this->checkpoint($parsedLog)) {
                $results->increment('skipped');
                continue;
            }

            try {
                $this->client->request(
                    $parsedLog->getMethod(),
                    $parsedLog->getUrl(),
                    [
                        'body' => $parsedLog->getBody(),
                        'headers' => $this->getHeaders($parsedLog),
                    ],
                );
            } catch (RequestException $requestException) {
                $results->increment('failed');

                $content = json_decode((string) $requestException->getResponse()?->getBody()->getContents());

                if (isset($content->errors->id[0]) && str_contains($content->errors->id[0], 'has already been taken')) {
                    $results->increment('failed_already_existed');
                    continue;
                }

                // TODO: do not log into memory, but into database or straight to file
                $results->addError([
                    'failed_at' => $index,
                    'id' => $parsedLog->getModelId(),
                    'master_user_id' => $parsedLog->getMasterUserId(),
                    'session' => $this->getSessionId(),
                    'guzzle_response_data' => [
                        'error'      => $content->error ?? '',
                        'errors'     => $content->errors ?? '',
                        'error_code' => $content->errorCode ?? '',
                    ],
                ]);
            }
        }

        echo PHP_EOL;

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
            ' request to '  . $parsedEventLog->getUrl() .
            ' url for '     . $parsedEventLog->getMasterUserId() . PHP_EOL;

        $pb = "id = \"{$parsedEventLog->getModelId()}\" and master_user_id = \"{$parsedEventLog->getMasterUserId()}\"";

        echo $pb . PHP_EOL ;
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
