<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderCreated;

enum StatusCode: string
{
    case Success = 'SUCCESS';
    case WarningContinueRedirect = 'WARNING_CONTINUE_REDIRECT';
    case WarningContinue3ds = 'WARNING_CONTINUE_3DS';
    case WarningContinueCVV = 'WARNING_CONTINUE_CVV';
    case Error_syntax = 'ERROR_SYNTAX';
    case ErrorValueInvalid = 'ERROR_VALUE_INVALID';
    case ErrorValueMissing = 'ERROR_VALUE_MISSING';
    case ErrorOrderNotUnique = 'ERROR_ORDER_NOT_UNIQUE';
    case ErrorInternal = 'ERROR_INTERNAL';
    case BusinessError = 'BUSINESS_ERROR';
    case Unauthorized = 'UNAUTHORIZED';
    case UnauthorizedRequest = 'UNAUTHORIZED_REQUEST';
    case DataNotFound = 'DATA_NOT_FOUND';
    case Timeout = 'TIMEOUT';
    case GeneralError = 'GENERAL_ERROR';
    case Warning = 'WARNING';
    case ServiceNotAvailable = 'SERVICE_NOT_AVAILABLE';
}
