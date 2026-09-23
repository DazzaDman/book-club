<?php

namespace App\Enums;

enum Cadence: string
{
    case Weekly = 'weekly';
    case Fortnightly = 'fortnightly';
    case Monthly = 'monthly';
    case BiMonthly = 'bi_monthly';
}
