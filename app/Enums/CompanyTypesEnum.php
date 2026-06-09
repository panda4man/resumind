<?php

namespace App\Enums;

enum CompanyTypesEnum: string
{
    case SaaS = 'saas';
    case Agency = 'agency';
    case Startup = 'startup';
    case NonProfit = 'non-profit';
    case Government = 'government';
}
