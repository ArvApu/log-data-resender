<?php

declare(strict_types=1);

namespace App\LogParser\Parser;

use App\LogParser\LogParserInterface;

class DataDogLogParser implements LogParserInterface
{
    public function parse(array $events): array
    {
        return [];
    }
}