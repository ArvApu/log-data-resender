<?php

namespace App\Service\LogProvider\Source;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface LogsProviderSourceInterface
{
    public function getLogs(string $filter): iterable;
}