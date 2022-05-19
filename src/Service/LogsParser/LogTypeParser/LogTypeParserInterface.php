<?php

declare(strict_types=1);

namespace App\Service\LogsParser\LogTypeParser;

use App\Service\LogsParser\ParsedLog;

interface LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog;
}
