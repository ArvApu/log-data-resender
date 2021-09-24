<?php

declare(strict_types=1);

namespace App;

use App\LogParsers\ParsedLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Sender
{
    private Client $guzzle;
    private string $apiKey;

    public function __construct(Client $guzzle, string $apiKey)
    {
        $this->guzzle = $guzzle;
        $this->apiKey = $apiKey;
    }

    /**
     * @param ParsedLog[] $parsedEventLogs
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendData(array $parsedEventLogs)
    {
        $failed = 0;
        $alreadyExists = 0;
        $completed = 0;
        $total = count($parsedEventLogs);
        $errors = [];
        $nonPostRequests = 0;
        $doNotHaveMasterUserId = 0;

        $this->putContentsToFile('./output/_failed.txt', 'Failed ids: ' . PHP_EOL);

        foreach (array_reverse($parsedEventLogs) as $index => $parsedEventLog) {

            $completed++;
            dump("Progress: $completed/$total");

            if ($parsedEventLog->getMethod() !== 'POST') {
                $nonPostRequests++;
                continue;
            }

            if ($parsedEventLog->getMasterUserId() === null) {
                $doNotHaveMasterUserId++;
                $this->putContentsToFile(
                    './output/_failed.txt',
                    $parsedEventLog->getBody()['id'] . ',' . PHP_EOL,
                    FILE_APPEND);
                continue;
            }

            try {
                $this->guzzle->request(
                    $parsedEventLog->getMethod(),
                    $parsedEventLog->getUrl(),
                    [
                        'json' => $parsedEventLog->getBody(),
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Cookie' => 'PHPSESSID=d75513pkj0g0uje6c1lc9ald2z',
                            'Wallmob-Api-Key' => $this->apiKey,
                            'Wallmob-Overwrite-Id' => $parsedEventLog->getMasterUserId()
                        ]
                    ]
                );

            } catch (RequestException $requestException) {
                $failed++;

                $content = json_decode($requestException->getResponse()->getBody()->getContents());

                $doesAlreadyExist = isset($content->errors->id[0]) && strpos($content->errors->id[0], 'has already been taken') !== false;

                if ($doesAlreadyExist) {
                    $alreadyExists++;
                }

                $errors[] = [
                    'failedAt' => $index,
                    'id' => $parsedEventLog->getBody()['id'] ?? null,
                    'muId' => $parsedEventLog->getMasterUserId(),
                    'alreadyExists' => $doesAlreadyExist,
                    'guzzleResponse' => [
                        'error' => $content->error ?? '',
                        'errors' => $content->errors ?? '',
                        'errorCode' => $content->errorCode ?? '',
                    ]
                ];
            }
        }

        $this->putContentsToFile('./output/_errors.json', json_encode($errors));

        dd([
            'failed' => $failed,
            'already_exists' => $alreadyExists,
            'non_post_requests' => $nonPostRequests,
            'do_not_have_master_user_id' => $doNotHaveMasterUserId,
        ]);
    }

    // TODO: duplicate - move to class
    private function putContentsToFile(string $filename, $content, int $flag = 0)
    {
        if (!file_put_contents($filename, $content, $flag)) {
            die("Oops! Error creating {$filename} file..." . PHP_EOL);
        }
    }

    private function checkpoint(ParsedLog $parsedEventLog): bool
    {
        echo 'Going to do ' . $parsedEventLog->getMethod() .
            ' request to '  . $parsedEventLog->getUrl() .
            ' url for '     . $parsedEventLog->getMasterUserId() . PHP_EOL;

        echo 'Continue? [yes/no/abort]' . PHP_EOL;

        $input = trim(fgets(STDIN));

        if ($input === 'abort') {
            die('Aborted' . PHP_EOL);
        }

        return $input === 'yes' || $input === 'y';
    }
}