<?php

declare(strict_types=1);

namespace App\Messenger\Message;

readonly class ResendLogsMessage implements QueueMessageInterface
{
    public function __construct(
        private int $jobId,
    ) {
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }
}
