<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

use Answear\Payum\PayU\Exception\PayUAuthorizationException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Exception\PayUNetworkException;
use Answear\Payum\PayU\Exception\PayURequestException;
use Answear\Payum\PayU\Exception\PayUServerErrorException;

class ExceptionHelper
{
    public static function getPayUException(\Throwable $exception): PayUException
    {
        return match ($exception->getCode()) {
            400 => new PayURequestException($exception->getMessage(), $exception->getCode(), $exception),
            401, 403 => new PayUAuthorizationException($exception->getMessage(), $exception->getCode(), $exception),
            408, 500, 503 => new PayUServerErrorException($exception->getMessage(), $exception->getCode(), $exception),
            default => new PayUNetworkException($exception->getMessage(), $exception->getCode(), $exception),
        };
    }
}
