<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Attribute\ServiceMetadata;
use App\Data\ValueObject\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[
    AsTaggedItem(index: 'json'),
    ServiceMetadata(label: 'JSON File Log Parser', description: 'Parses logs from JSON files.'),
]
class JsonFileLogTypeParser implements LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog
    {
        $data = $event;

        // TODO: this data cannot be extracted from logs so we must find other way to get them, now hard-coding
        $masterUserId = 'id_of_master_user';
        $resource = 'orders';
        $url = 'https://pos-etail.wallmob.com';
        $method = 'PATCH';

        $body = json_encode($data, JSON_THROW_ON_ERROR);

        return new ParsedLog($body, $method, "$url/{$resource}/{$data['id']}", $data['id'], $masterUserId);
    }
}
