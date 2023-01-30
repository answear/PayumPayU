<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum RefundStatus: string
{
    case Finalized = 'FINALIZED';
    case Canceled = 'CANCELED';
    case Pending = 'PENDING';
}
