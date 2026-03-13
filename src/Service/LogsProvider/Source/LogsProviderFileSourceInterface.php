<?php

namespace App\Service\LogsProvider\Source;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(self::class)]
interface LogsProviderFileSourceInterface extends LogsProviderSourceInterface
{

}