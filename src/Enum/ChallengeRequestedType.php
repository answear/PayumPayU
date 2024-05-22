<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum ChallengeRequestedType: string
{
    case Yes = 'YES';
    case No = 'NO';
    case Mandate = 'MANDATE';
}
