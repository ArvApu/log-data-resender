<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Service\LogParser\ParsedLog;

class PosRestActionInsertedParser implements LogTypeParserInterface
{
    public static function getId(): string
    {
        return 'pos_rest_action_inserted';
    }

    public function parse(array $event): ?ParsedLog
    {
        $masterUserId = $event['attributes']['attributes']['usr']['id'] ?? null;
        $data = json_decode($event['attributes']['attributes']['info'] ?? '');

        if ($data === null || $masterUserId === null) {
            return null;
        }

        $id = $data->restId ?? null;
        $resource = $data->resourceName ?? null;
        $method = $this->resolveMethod((int) ($data->type ?? 0));

        if ($id === null || $resource === null || $method === null) {
            return null;
        }

        // TODO: url cannot be extracted from logs so we must find other way to get them, now hard-coding
        $baseUrl = 'https://pos-etail.wallmob.com';

        $url = ($method === 'PUT' || $method === 'PATCH') ? "$baseUrl/{$resource}/$id" : "$baseUrl/{$resource}";

        return new ParsedLog(json_encode($data->parameters), $method, $url, $id, $masterUserId, false);
    }

    private function resolveMethod(int $typeId): ?string
    {
        return match ($typeId) {
            0 => 'GET',
            1 => 'POST',
            2 => 'PUT',
            3 => 'PATCH',
            4 => 'DELETE',
            default => null,
        };
    }
}