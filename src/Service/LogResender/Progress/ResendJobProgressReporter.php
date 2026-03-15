<?php

declare(strict_types=1);

namespace App\Service\LogResender\Progress;

use App\Service\LogResender\Sender\ResultsAccumulator;

class ResendJobProgressReporter implements ProgressReporterInterface
{

    public function report(ResultsAccumulator $results): void
    {
        // TODO: Implement report() method.
    }

    public function finish(ResultsAccumulator $results): void
    {
        // TODO: Implement finish() method.
    }
}
