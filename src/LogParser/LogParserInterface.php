<?php

declare(strict_types=1);

namespace App\LogParser;

interface LogParserInterface
{
    /**
     * @param array $events
     * @return ParsedLog[]
     */
    public function parse(array $events): array;
}
