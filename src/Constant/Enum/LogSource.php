<?php

namespace App\Constant\Enum;

enum LogSource: string
{
    case CLOUDWATCH = 'cloudwatch';
    case DATADOG = 'datadog';
    case DATADOG_FILE = 'datadog_file';
    case FILE = 'file';
}
