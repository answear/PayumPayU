<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum PayByLinkStatus: string
{
    case Enabled = 'ENABLED';
    case Disabled = 'DISABLED';
    case TemporaryDisabled = 'TEMPORARY_DISABLED';
}
