<?php

declare(strict_types=1);

namespace App\Service\LogsParser\LogTypeParser;

use App\Service\LogsParser\ParsedLog;

interface LogTypeParserInterface
{
    public static function getId(): string;

    public function parse(array $event): ?ParsedLog;
}
