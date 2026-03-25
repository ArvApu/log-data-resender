<?php

namespace App\Service\LogModifier;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

// TODO: investigate visitor interface pattern for this,
//  to avoid coupling with the pipeline and allow more flexible log modification
#[AutoconfigureTag(self::class)]
interface LogModifierInterface
{
    public function getId(): string;

    public function modify(array $log): array;
}
