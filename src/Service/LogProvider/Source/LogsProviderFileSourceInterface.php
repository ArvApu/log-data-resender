<?php

namespace App\Service\LogProvider\Source;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface LogsProviderFileSourceInterface extends LogsProviderSourceInterface
{
}