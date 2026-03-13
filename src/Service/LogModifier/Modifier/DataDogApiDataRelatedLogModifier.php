<?php

namespace App\Service\LogModifier\Modifier;

use App\Client\DataDog\DataDogClient;
use App\Service\LogModifier\LogModifierInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10)]
readonly class DataDogApiDataRelatedLogModifier implements LogModifierInterface
{
    private const int DELTA_MS = 1000;

    public function __construct(
        private DataDogClient $dataDogClient
    ) {
    }

    public function getId(): string
    {
        return 'datadog_api_data_related';
    }

    /**
     * @throws \Exception
     */
    public function modify(array $log): array
    {
        $timestamp = $this->extractTimestamp($log);

        if ($timestamp === null) {
            throw new \Exception('Log does not contain a valid timestamp.');
        }

        [$from, $to] = $this->getTimestampRange($timestamp);

        $userId = $log['attributes']['attributes']['user']['id'] ?? null;
        $requestId = $log['attributes']['attributes']['request']['request_id'] ?? null;
        $apiModel = $log['attributes']['attributes']['api']['model'] ?? null;

        if (!$userId || !$requestId || !$apiModel) {
            throw new \Exception('Log is missing required user/request/api fields.');
        }

        $filter = json_encode([
            'filter' => [
                'from' => $from,
                'to' => $to,
                'query' => "@user.id:$userId @request.request_id:$requestId @api.model:$apiModel \"Api data\"",
            ],
        ]);

        $relatedLog = $this->dataDogClient->getLog($filter);

        if ($relatedLog === null) {
            throw new \Exception('No related log found in DataDog for the given log');
        }

        return $relatedLog;
    }

    private function extractTimestamp(array $log): ?\DateTimeImmutable
    {
        $timestampString = $log['attributes']['timestamp'] ?? null;

        if (!$timestampString) {
            return null;
        }

        try {
            return new \DateTimeImmutable($timestampString);
        } catch (\Exception) {
            return null;
        }
    }

    private function getTimestampRange(\DateTimeImmutable $date): array
    {
        $timestampMs = (int) ($date->format('Uu') / 1000);

        return [
            $timestampMs - self::DELTA_MS,
            $timestampMs + self::DELTA_MS,
        ];
    }
}

