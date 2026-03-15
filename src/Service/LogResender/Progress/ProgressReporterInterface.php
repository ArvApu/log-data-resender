<?php

declare(strict_types=1);

namespace App\Service\LogResender\Progress;

use App\Service\LogResender\Sender\ResultsAccumulator;

interface ProgressReporterInterface
{
    public function report(ResultsAccumulator $results): void;

    public function finish(ResultsAccumulator $results): void;
}
