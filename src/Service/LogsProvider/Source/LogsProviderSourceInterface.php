<?php

namespace App\Service\LogsProvider\Source;

interface LogsProviderSourceInterface
{
    public static function getId(): string;

    public function getLogs(string $filter): iterable;
}