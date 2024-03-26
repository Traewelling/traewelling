<?php declare(strict_types=1);

namespace App\Enum\Report;

enum ReportableSubject: string
{
    case EVENT  = 'Event';
    case STATUS = 'Status';
    case USER   = 'User';
}
