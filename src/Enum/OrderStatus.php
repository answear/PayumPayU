<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum OrderStatus: string
{
    case Pending = 'PENDING';
    case WaitingForConfirmation = 'WAITING_FOR_CONFIRMATION';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELED';
}
