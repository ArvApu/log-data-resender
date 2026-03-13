<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Service\LogParser\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface LogTypeParserInterface
{
    public function parse(array $event): ?ParsedLog;
}
