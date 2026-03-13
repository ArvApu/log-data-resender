<?php

declare(strict_types=1);

namespace App\Service\LogParser\LogTypeParser;

use App\Service\LogParser\ParsedLog;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface LogTypeParserInterface
{
    public static function getId(): string;

    public function parse(array $event): ?ParsedLog;
}
