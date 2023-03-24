<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Authorization\AuthType;

class Basic implements AuthType
{
    private string $authBasicToken;

    public function __construct(string $posId, string $signatureKey)
    {
        if (empty($posId)) {
            throw new \RuntimeException('PosId is empty');
        }

        if (empty($signatureKey)) {
            throw new \RuntimeException('SignatureKey is empty');
        }

        $this->authBasicToken = base64_encode($posId . ':' . $signatureKey);
    }

    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $this->authBasicToken,
        ];
    }
}
