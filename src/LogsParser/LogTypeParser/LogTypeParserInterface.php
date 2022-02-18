<?php

declare(strict_types=1);

namespace App\LogsParser\LogTypeParser;

use App\LogsParser\ParsedLog;

interface LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog;
}
