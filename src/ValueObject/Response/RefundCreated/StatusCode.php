<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\RefundCreated;

enum StatusCode: string
{
    case Success = 'SUCCESS';
    case ErrorValueMissing = 'ERROR_VALUE_MISSING';
    case OpenpayuBusinessError = 'OPENPAYU_BUSINESS_ERROR';
    case OpenpayuErrorValueInvalid = 'OPENPAYU_ERROR_VALUE_INVALID';
    case OpenpayuErrorInternal = 'OPENPAYU_ERROR_INTERNAL';
}
