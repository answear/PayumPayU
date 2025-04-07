<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

class UserIpService
{
    public function getIp(): ?string
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_TRUE_CLIENT_IP'];

        if (!empty($ip)) {
            return $ip;
        }

        $xForwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'];
        if (!empty($xForwardedFor)) {
            return trim(explode(',', $xForwardedFor)[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
}
