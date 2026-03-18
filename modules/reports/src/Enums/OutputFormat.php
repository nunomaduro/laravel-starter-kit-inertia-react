<?php

declare(strict_types=1);

namespace Modules\Reports\Enums;

enum OutputFormat: string
{
    case Pdf = 'pdf';
    case Html = 'html';
    case Csv = 'csv';
}
