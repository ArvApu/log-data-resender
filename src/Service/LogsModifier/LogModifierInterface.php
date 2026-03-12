<?php

namespace App\Service\LogsModifier;

// TODO: investigate visitor interface pattern for this, to avoid coupling with the pipeline and allow more flexible log modification
interface LogModifierInterface
{
    public function getId(): string;

    public function modify(array $log): array;
}

