<?php

declare(strict_types=1);

namespace App\Service\LogResender\Cancellation;

interface CancellationCheckerInterface
{
    public function check(): void;
}
