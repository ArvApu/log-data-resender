<?php

declare(strict_types=1);

namespace App\Constant\Enum;

enum ResendJobStatus: string
{
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
