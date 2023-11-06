<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum PayMethodType: string
{
    case Pbl = 'PBL';
    case CardToken = 'CARD_TOKEN';
    case Installments = 'INSTALLMENTS';
    case PaymentWall = 'PAYMENT_WALL';
    case BlikAuthorizationCode = 'BLIK_AUTHORIZATION_CODE';
}
