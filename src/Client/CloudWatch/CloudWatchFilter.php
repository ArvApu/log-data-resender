<?php

declare(strict_types=1);

namespace App\Client\CloudWatch;

class CloudWatchFilter implements \JsonSerializable
{
    public function __construct(
        private string $filterPattern,
        private string $logGroupName,
        private string $logStreamName,
        private \DateTimeImmutable $startTime,
        private \DateTimeImmutable $endTime,
    ) {
    }

    /**
     * @throws \Exception
     */
    public static function fromJsonString(string $json): self
    {
        $json = json_decode($json);

        if ($json === null || self::hasInvalidStructure($json)) {
            throw new \Exception('Invalid json string provided');
        }

        return new self(
            $json->filterPattern,
            $json->logGroupName,
            $json->logStreamName,
            new \DateTimeImmutable('@' . round(($json->startTime) / 1000)),
            new \DateTimeImmutable('@' . round(($json->endTime) / 1000)),
        );
    }

    private static function hasInvalidStructure(\stdClass $json): bool
    {
        return !isset(
            $json->filterPattern,
            $json->logGroupName,
            $json->logStreamName,
            $json->startTime,
            $json->endTime,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'filterPattern' => $this->filterPattern,
            'logGroupName' => $this->logGroupName,
            'logStreamNamePrefix' => $this->logStreamName,
            'startTime' => $this->startTime->getTimestamp() * 1000,
            'endTime' => $this->endTime->getTimestamp() * 1000,
        ];
    }
}