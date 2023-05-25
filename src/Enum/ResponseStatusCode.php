<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum ResponseStatusCode: string
{
    case Success = 'SUCCESS';
    case ErrorValueMissing = 'ERROR_VALUE_MISSING';
    case ErrorValueInvalid = 'ERROR_VALUE_INVALID';
    case OpenpayuBusinessError = 'OPENPAYU_BUSINESS_ERROR';
    case OpenpayuErrorValueInvalid = 'OPENPAYU_ERROR_VALUE_INVALID';
    case OpenpayuErrorInternal = 'OPENPAYU_ERROR_INTERNAL';
    case Unauthorized = 'UNAUTHORIZED';
    case DataNotFound = 'DATA_NOT_FOUND';
    case Timeout = 'TIMEOUT';
    case BusinessError = 'BUSINESS_ERROR';
    case ErrorInternal = 'ERROR_INTERNAL';
    case GeneralError = 'GENERAL_ERROR';
    case Warning = 'WARNING';
    case ServiceNotAvailable = 'SERVICE_NOT_AVAILABLE';
}
