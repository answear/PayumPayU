<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum ResponseStatusCode: string
{
    case Success = 'SUCCESS';
    case ErrorValueMissing = 'ERROR_VALUE_MISSING';
    case BusinessError = 'BUSINESS_ERROR';
    case ErrorValueInvalid = 'ERROR_VALUE_INVALID';
    case ErrorInternal = 'ERROR_INTERNAL';
    case OpenpayuBusinessError = 'OPENPAYU_BUSINESS_ERROR';
    case OpenpayuErrorValueInvalid = 'OPENPAYU_ERROR_VALUE_INVALID';
    case OpenpayuErrorInternal = 'OPENPAYU_ERROR_INTERNAL';
}
