<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use App\Constant\Enum\ResendJobStatus;
use App\Messenger\Message\ResendLogsMessage;
use App\Repository\ResendJobRepository;
use App\Service\LogResender\LogResender;
use App\Service\LogResender\Progress\ResendJobProgressReporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ResendLogsMessageHandler
{
    public function __construct(
        private ResendJobRepository $resendJobRepository,
        private LogResender $logResender,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ResendLogsMessage $message): void
    {
        $job = $this->resendJobRepository->find($message->getJobId());

        if ($job === null || $job->getStatus() !== ResendJobStatus::QUEUED) {
            return;
        }

        $job->setStatus(ResendJobStatus::RUNNING)
            ->setStartedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        $results = $this->logResender->resend(
            $job->getSource(),
            $job->getFilterFilePath() ?? (string) $job->getFilter(),
            $job->getParser(),
            $job->getModifiers(),
            new ResendJobProgressReporter($this->entityManager, $job),
        );

        if ($results?->getException() !== null) {
            $job->setStatus(ResendJobStatus::FAILED)
                ->setErrorMessage($results->getException()->getMessage());
        } else {
            $job->setStatus(ResendJobStatus::COMPLETED);
        }

        $job->setFinishedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
