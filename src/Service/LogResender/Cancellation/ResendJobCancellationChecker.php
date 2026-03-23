<?php

declare(strict_types=1);

namespace App\Service\LogResender\Cancellation;

use App\Constant\Enum\ResendJobStatus;
use App\Entity\ResendJob;
use App\Exception\ResendJobCancelledException;
use Doctrine\ORM\EntityManagerInterface;

final class ResendJobCancellationChecker implements CancellationCheckerInterface
{
    private float $lastCheckAt = 0.0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResendJob $job,
        private readonly float $minIntervalSeconds = 1.0,
    ) {
    }

    public function check(): void
    {
        $now = microtime(true);

        if ($now - $this->lastCheckAt < $this->minIntervalSeconds) {
            return;
        }

        $this->lastCheckAt = $now;

        $this->entityManager->refresh($this->job);

        if ($this->job->getStatus() === ResendJobStatus::CANCELLED) {
            throw new ResendJobCancelledException();
        }
    }
}
