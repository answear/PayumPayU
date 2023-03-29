<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Authorization\AuthType;

class TokenRequest implements AuthType
{
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => '*/*',
        ];
    }
}
