<?php

declare(strict_types=1);

namespace App\Service\LogResender\Progress;

use App\Constant\Enum\ResultCategory;
use App\Entity\ResendJob;
use App\Service\LogResender\Sender\ResultsAccumulator;
use Doctrine\ORM\EntityManagerInterface;

class ResendJobProgressReporter implements ProgressReporterInterface
{
    private int $lastProcessed = 0;
    private float $lastFlushAt = 0.0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResendJob $job,
        private readonly int $batchSize = 50,
        private readonly float $minIntervalSeconds = 3.0,
    ) {
    }

    public function report(ResultsAccumulator $results): void
    {
        $processed = $results->getCount(ResultCategory::COMPLETED);
        $now = microtime(true);

        if (!$this->hasEnoughNewProcessedLogs($processed) || !$this->hasMinimumIntervalElapsed($now)) {
            return;
        }

        $this->lastProcessed = $processed;
        $this->lastFlushAt = $now;

        $this->job->setProcessedCount($processed);
        $this->job->setCounts($results->getCounts());
        $this->job->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    public function finish(ResultsAccumulator $results): void
    {
        $this->job->setProcessedCount($results->getCount(ResultCategory::COMPLETED));
        $this->job->setCounts($results->getCounts());
        $this->job->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }

    private function hasEnoughNewProcessedLogs(int $processed): bool
    {
        return $processed - $this->lastProcessed >= $this->batchSize;
    }

    private function hasMinimumIntervalElapsed(float $now): bool
    {
        return $now - $this->lastFlushAt >= $this->minIntervalSeconds;
    }
}
