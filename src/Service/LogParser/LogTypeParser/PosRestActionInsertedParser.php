<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Attribute\ServiceMetadata;
use App\Data\ValueObject\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'pos_rest_action_inserted'),
    ServiceMetadata(label: 'POS REST Action Inserted Log Parser', description: 'Parses logs for POS REST actions.'),
]
class PosRestActionInsertedParser implements LogTypeParserInterface
{
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
        $body = json_encode($data->parameters, JSON_THROW_ON_ERROR);

        return new ParsedLog($body, $method, $url, $id, $masterUserId, false);
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