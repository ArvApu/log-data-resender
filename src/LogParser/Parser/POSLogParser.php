<?php

declare(strict_types=1);

namespace App\LogParser\Parser;

use App\LogParser\LogParser;
use App\LogParser\ParsedLog;

class POSLogParser extends LogParser
{
    public function parse(array $events): array
    {
        $parsed = [];

        foreach ($events as $event) {
            $info = $this->getInformation($event);

            if ($info === null) {
                continue;
            }

            parse_str($info['REQUEST_BODY'], $requestBody);

            $parsed[] = new ParsedLog(
                json_encode($requestBody),
                $info['REQUEST_METHOD'],
                urldecode($info['REQUEST_URL']),
                $requestBody['id'],
                $this->getMasterUserId($event, $info),
            );
        }

        return $parsed;
    }

    private function getInformation(array $event): ?array
    {
        // For wallmob version 3.6 and above
        $info = json_decode($event['event']['json']['info']['info'] ?? '', true);

        if ($info !== null) {
            return $info;
        }

        // For wallmob version 3.5 and below
        $logMessage = json_decode($event['logmsg'] ?? '', true);

        if ($logMessage === null || !isset($logMessage['rawlogmessage'])) {
            return null;
        }

        $requestBody = $this->extractDataFromRawLogMessage($logMessage['rawlogmessage'], 'REQUEST BODY" :', '}');
        $requestMethod = $this->extractDataFromRawLogMessage($logMessage['rawlogmessage'], 'REQUEST METHOD" :', ',');
        $requestURL = $this->extractDataFromRawLogMessage($logMessage['rawlogmessage'], 'URL:', '}');

        if (!isset($requestBody, $requestMethod, $requestURL)) {
            return null;
        }

        return [
            'REQUEST_BODY' => $requestBody,
            'REQUEST_METHOD' => $requestMethod,
            'REQUEST_URL' => $requestURL,
            'master_user_id' => $logMessage['wallmob']['master_user_id']
        ];
    }

    private function extractDataFromRawLogMessage(string $rawLogMessage, string $startToken, string $endToken): ?string
    {
        $start = strpos($rawLogMessage, $startToken) + strlen($startToken);
        $end = strpos($rawLogMessage, $endToken, $start);

        if (!$start || !$end) {
            return null;
        }

        $data = trim(substr($rawLogMessage, $start, $end - $start));

        return str_replace('"', '', $data);
    }

    private function getMasterUserId(array $event, array $info): ?string
    {
        return $event['event']['json']['user']['id'] ?? $info['master_user_id'] ?? null;
    }
}
