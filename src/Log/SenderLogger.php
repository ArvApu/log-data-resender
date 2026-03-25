<?php

declare(strict_types=1);

namespace App\Log;

use App\Entity\Log;
use App\Entity\SenderLog;
use App\Repository\SenderLogRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class SenderLogger implements LoggerInterface
{
    use LoggerTrait;

    public function __construct(
        private readonly SenderLogRepository $senderLogRepository,
    ) {
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $log = new Log();
        $log->setLevel($level);
        $log->setMessage((string) $message);
        $log->setCreatedAt(new \DateTimeImmutable());

        $senderLog = new SenderLog();
        $senderLog->setLog($log);
        $senderLog->setFailedAt($context['failed_at']);
        $senderLog->setSession($context['session']);
        $senderLog->setMasterUserId($context['master_user_id']);
        $senderLog->setModelId($context['model_id']);
        $senderLog->setResponseData($context['response_data'] ?? []);

        $this->senderLogRepository->save($senderLog, true);
    }
}
