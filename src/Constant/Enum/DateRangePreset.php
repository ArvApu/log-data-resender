<?php

declare(strict_types=1);

namespace App\Constant\Enum;

enum DateRangePreset: string
{
    case LAST_15M = 'last_15m';
    case LAST_1H = 'last_1h';
    case LAST_4H = 'last_4h';
    case LAST_24H = 'last_24h';
    case LAST_7D = 'last_7d';

    public function intervalSpec(): string
    {
        return match ($this) {
            self::LAST_15M => 'PT15M',
            self::LAST_1H => 'PT1H',
            self::LAST_4H => 'PT4H',
            self::LAST_24H => 'P1D',
            self::LAST_7D => 'P7D',
        };
    }
}
