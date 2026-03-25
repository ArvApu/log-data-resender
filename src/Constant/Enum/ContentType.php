<?php

declare(strict_types=1);

namespace App\Constant\Enum;

enum ContentType: string
{
    case CSV = 'text/csv';
    case HTML = 'text/html';
    case MARKDOWN = 'text/markdown';
    case PLAIN = 'text/plain';
    case XML = 'text/xml';

    case XML_APPLICATION = 'application/xml';
    case BINARY = 'application/octet-stream';
    case FORM_URL_ENCODED = 'application/x-www-form-urlencoded';
    case JSON = 'application/json';
    case NDJSON = 'application/ndjson';
    case PDF = 'application/pdf';
    case ZIP = 'application/zip';

    case PK_PASS = 'application/vnd.apple.pkpass';
    case PK_PASSES = 'application/vnd.apple.pkpasses';

    case JPG = 'image/jpeg';
    case PNG = 'image/png';
    case SVG = 'image/svg+xml';
    case WEBP = 'image/webp';
    case AVIF = 'image/avif';

    case MULTIPART = 'multipart/form-data';
}