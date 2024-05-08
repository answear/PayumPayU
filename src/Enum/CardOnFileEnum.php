<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum CardOnFileEnum: string
{
    case First = 'FIRST';
    case StandardMerchant = 'STANDARD_MERCHANT';
}
