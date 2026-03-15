<?php

namespace App\Messenger\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
interface QueueMessageInterface
{

}