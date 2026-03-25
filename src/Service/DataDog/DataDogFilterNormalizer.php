<?php

declare(strict_types=1);

namespace App\Service\DataDog;

use App\Data\ValueObject\DataDogPayload;

final readonly class DataDogFilterNormalizer
{
    public function __construct(
        private DataDogRangeResolver $rangeResolver,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function normalize(
        string $rawFilter,
        ?string $datePreset,
        ?string $dateFrom,
        ?string $dateTo,
    ): string {
        $input = trim($rawFilter);

        if ($input === '') {
            throw new \InvalidArgumentException('Filter is required for DataDog source.');
        }

        $payload = $this->parsePayload($input) ?? DataDogPayload::fromQuery($input);

        $range = $this->rangeResolver->resolve($datePreset, $dateFrom, $dateTo);

        if ($range === null) {
            return $payload->toJson();
        }

        return $payload
            ->ensureRange($range)
            ->toJson();
    }

    private function parsePayload(string $input): ?DataDogPayload
    {
        return $this->parseJson($input)
            ?? $this->parseCurlPayload($input)
            ?? $this->parseUrlPayload($input);
    }

    private function parseJson(string $input): ?DataDogPayload
    {
        $decoded = json_decode($input, true);

        if (!is_array($decoded)) {
            return null;
        }

        return DataDogPayload::fromArray($decoded);
    }

    private function parseCurlPayload(string $input): ?DataDogPayload
    {
        if (!str_starts_with($input, 'curl ')) {
            return null;
        }

        // Extracts JSON payload from curl commands.
        // Matches:
        //   -d '...'
        //   --data '...'
        //   --data-raw "..."
        //   --data-binary "..."
        // Captures the quoted payload (group 1), handling escaped quotes inside the string.
        $pattern = '/(?:-d|--data(?:-raw|-binary)?)\s+(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")/m';

        if (!preg_match($pattern, $input, $matches)) {
            return null;
        }

        $raw = substr($matches[1], 1, -1);
        $raw = stripcslashes($raw);

        return $this->parseJson($raw);
    }

    private function parseUrlPayload(string $input): ?DataDogPayload
    {
        if (!preg_match('/^https?:\/\//i', $input)) {
            return null;
        }

        $params = $this->parseUrlParams($input);
        if ($params === []) {
            return null;
        }

        $query = $this->extractQueryParam($params);
        $range = $this->extractRangeParams($params);

        if ($query === null && $range === null) {
            return null;
        }

        return new DataDogPayload(
            query: $query,
            from: $range['from'] ?? null,
            to: $range['to'] ?? null,
        );
    }

    private function parseUrlParams(string $url): array
    {
        $parts = parse_url($url);
        $params = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $params);
        }

        if (!empty($parts['fragment'])) {
            $fragment = $parts['fragment'];

            if (str_contains($fragment, '?')) {
                $fragment = substr($fragment, strpos($fragment, '?') + 1);
            }

            if (str_contains($fragment, '=')) {
                $fragmentParams = [];
                parse_str($fragment, $fragmentParams);
                $params = array_merge($fragmentParams, $params);
            }
        }

        return $params;
    }

    private function extractQueryParam(array $params): ?string
    {
        foreach (['query', 'q', 'logs', 'search'] as $candidate) {
            if (!isset($params[$candidate]) || !is_string($params[$candidate])) {
                continue;
            }

            $value = trim($params[$candidate]);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractRangeParams(array $params): ?array
    {
        $from = $this->rangeResolver->normalizeTimeValue($this->findParam($params, ['from_ts', 'from', 'start']));
        $to = $this->rangeResolver->normalizeTimeValue($this->findParam($params, ['to_ts', 'to', 'end']));

        if ($from === null || $to === null) {
            return null;
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    private function findParam(array $params, array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (isset($params[$candidate]) && is_scalar($params[$candidate])) {
                return (string) $params[$candidate];
            }
        }

        return null;
    }
}
