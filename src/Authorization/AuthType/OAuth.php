<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Authorization\AuthType;

readonly class OAuth implements AuthType
{
    public function __construct(private string $accessToken)
    {
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }
}
