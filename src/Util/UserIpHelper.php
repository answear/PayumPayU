<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

class UserIpHelper
{
    public static function getIp(): ?string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_TRUE_CLIENT_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? null;
    }
}
