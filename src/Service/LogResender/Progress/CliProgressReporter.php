<?php

declare(strict_types=1);

namespace App\Service\LogResender\Progress;

use App\Constant\Enum\ResultCategory;
use App\Service\LogResender\Sender\ResultsAccumulator;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class CliProgressReporter implements ProgressReporterInterface
{
    public function __construct(
        private OutputInterface $output,
    ) {
    }

    public function report(ResultsAccumulator $results): void
    {
        $done = $results->getCount(ResultCategory::COMPLETED);
        $this->output->write("\rProgress: {$done}");
    }

    public function finish(ResultsAccumulator $results): void
    {
        $this->output->writeln('');
    }
}
