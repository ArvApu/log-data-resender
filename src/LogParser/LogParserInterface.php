<?php

declare(strict_types=1);

namespace App\LogParser;

interface LogParserInterface
{
    /**
     * @return ParsedLog[]
     */
    public function parse(iterable $events): array;
}
