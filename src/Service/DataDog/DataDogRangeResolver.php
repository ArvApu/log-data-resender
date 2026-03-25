<?php

declare(strict_types=1);

namespace App\Service\DataDog;

use App\Constant\Enum\DateRangePreset;

final readonly class DataDogRangeResolver
{
    private const int MILLISECONDS_LENGTH = 13;

    public function resolve(?string $datePreset, ?string $dateFrom, ?string $dateTo): ?array
    {
        $datePreset = trim((string) $datePreset);
        $dateFrom = trim((string) $dateFrom);
        $dateTo = trim((string) $dateTo);

        if ($dateFrom !== '' || $dateTo !== '') {
            return $this->resolveCustomRange($dateFrom, $dateTo);
        }

        if ($datePreset === '') {
            return null;
        }

        $preset = DateRangePreset::tryFrom($datePreset);
        if ($preset === null) {
            return null;
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $from = $now->sub(new \DateInterval($preset->intervalSpec()));

        return [
            'from' => $from->format(DATE_ATOM),
            'to' => $now->format(DATE_ATOM),
        ];
    }

    private function resolveCustomRange(string $dateFrom, string $dateTo): array
    {
        if ($dateFrom === '' || $dateTo === '') {
            throw new \InvalidArgumentException('Custom date range requires both start and end.');
        }

        $from = $this->normalizeTimeValue($dateFrom);
        $to = $this->normalizeTimeValue($dateTo);

        if ($from === null || $to === null) {
            throw new \InvalidArgumentException('Custom date range must use a valid date-time.');
        }

        return [
            'from' => $from,
            'to' => $to,
        ];
    }

    public function normalizeTimeValue(?string $value): ?string
    {
        $value = trim($value ?? '');

        if (empty($value)) {
            return null;
        }

        if (ctype_digit($value)) {
            $epoch = (int) $value;

            $length = strlen($value);

            if ($length === self::MILLISECONDS_LENGTH) {
                $epoch = (int) ($epoch / 1000);
            }

            return new \DateTimeImmutable("@{$epoch}")->setTimezone(new \DateTimeZone('UTC'))->format(DATE_ATOM);
        }

        try {
            return new \DateTimeImmutable($value)->setTimezone(new \DateTimeZone('UTC'))->format(DATE_ATOM);
        } catch (\Exception) {
            return null;
        }
    }
}
