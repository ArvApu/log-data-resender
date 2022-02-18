<?php

declare(strict_types=1);

namespace App;

use App\LogsParser\ParsedLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Sender
{
    private ?string $apiKey = null;
    private bool $withCheckpoints = false;

    public function __construct(
        private Client $client,
    ) {
    }

    /**
     * @param  ParsedLog[] $parsedEventLogs
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendData(array $parsedEventLogs): ResultsAccumulator
    {
        $results = new ResultsAccumulator();

        if (!isset($this->apiKey)) {
            die('No API key provided');
        }

        $total = count($parsedEventLogs);

        foreach ($parsedEventLogs as $index => $parsedEventLog) {
            $results->increment('completed');

            $this->progress($results->getCount('completed'), $total);

            // Protects from accidentally changing data with update methods (PATCH/PUT).
            if ($parsedEventLog->getMethod() !== 'POST') {
                $results->increment('not_a_post_request');
                continue;
            }

            if ($parsedEventLog->getMasterUserId() === null) {
                $results->increment('missing_master_user_id');
                $results->addMeta('no_mu_id', ['model_id' => $parsedEventLog->getModelId()]);
                continue;
            }

            if (!$this->checkpoint($parsedEventLog)) {
                $results->increment('skipped');
                continue;
            }

            try {
                $this->client->request(
                    $parsedEventLog->getMethod(),
                    $parsedEventLog->getUrl(),
                    [
                        'body' => $parsedEventLog->getBody(),
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Cookie' => 'PHPSESSID=d75513pkj0g0uje6c1lc9ald2z',
                            'Wallmob-Api-Key' => $this->apiKey,
                            'Wallmob-Overwrite-Id' => $parsedEventLog->getMasterUserId(),
                        ],
                    ],
                );
            } catch (RequestException $requestException) {
                $results->increment('failed');

                $content = json_decode((string) $requestException->getResponse()?->getBody()->getContents());

                $doesAlreadyExist = isset($content->errors->id[0])
                    && str_contains($content->errors->id[0], 'has already been taken');

                if ($doesAlreadyExist) {
                    $results->increment('failed_already_existed');
                }

                $results->addError([
                    'failed_at' => $index,
                    'id' => $parsedEventLog->getModelId(),
                    'master_user_id' => $parsedEventLog->getMasterUserId(),
                    'already_exists' => $doesAlreadyExist,
                    'guzzle_response_data' => [
                        'error'      => $content->error ?? '',
                        'errors'     => $content->errors ?? '',
                        'error_code' => $content->errorCode ?? '',
                    ],
                ]);
            }
        }

        return $results;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
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

        echo 'Going to do ' . $parsedEventLog->getMethod() .
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

    private function progress(int $done, int $total): void
    {
        if ($done >= $total || $this->withCheckpoints) {
            echo "\rProgress: {$done} / {$total}" . PHP_EOL;
        } else {
            echo "\rProgress: {$done} / {$total}";
        }
    }
}
