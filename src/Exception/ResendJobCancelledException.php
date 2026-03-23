<?php

declare(strict_types=1);

namespace App\Exception;

final class ResendJobCancelledException extends \RuntimeException
{
    public function __construct(string $message = 'Resend job was cancelled.')
    {
        parent::__construct($message);
    }
}
