<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Order;

enum PayMethodType: string
{
    case Pbl = 'PBL';
    case CardToken = 'CARD_TOKEN';
    case PaymentWall = 'PAYMENT_WALL';
}
