<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum OrderStatus: string
{
    case New = 'NEW';
    case Pending = 'PENDING';
    case WaitingForConfirmation = 'WAITING_FOR_CONFIRMATION';
    case Completed = 'COMPLETED';
    case Canceled = 'CANCELED';

    public static function finalStatuses(): array
    {
        return [
            self::Completed,
            self::Canceled,
        ];
    }

    public function isFinal(): bool
    {
        return in_array($this, self::finalStatuses(), true);
    }
}
