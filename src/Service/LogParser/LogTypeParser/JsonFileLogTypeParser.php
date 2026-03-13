<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Service\LogParser\ParsedLog;

class JsonFileLogTypeParser implements LogTypeParserInterface
{

    public static function getId(): string
    {
        return 'json';
    }

    public function parse(array $event): ?ParsedLog
    {
        $data = $event;

        // TODO: this data cannot be extracted from logs so we must find other way to get them, now hard-coding
        $masterUserId = 'id_of_master_user';
        $resource = 'orders';
        $url = 'https://pos-etail.wallmob.com';
        $method = 'PATCH';

        return new ParsedLog(json_encode($data), $method, "$url/{$resource}/{$data['id']}", $data['id'], $masterUserId);
    }
}