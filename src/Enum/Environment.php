<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum Environment: string
{
    case Sandbox = 'sandbox';
    case Secure = 'secure';

    public static function hasValue(?string $value): bool
    {
        return null !== self::tryFrom($value ?? '');
    }
}
