<?php

declare(strict_types=1);

namespace App\Constant\Enum;

enum ResultCategory: string
{
    case COMPLETED = 'completed';
    case NOT_POST = 'not_a_post_request';
    case MISSING_MASTER_USER_ID = 'missing_master_user_id';
    case FAILED = 'failed';
    case ALREADY_EXISTS = 'already_exists';
}