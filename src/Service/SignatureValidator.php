<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

class SignatureValidator
{
    public function __construct(private ConfigProvider $configProvider)
    {
    }

    public function isValid(string $signatureHeader, string $data, ?string $configKey): bool
    {
        $config = $this->configProvider->getConfig($configKey);

        $signature = \OpenPayU_Util::parseSignature($signatureHeader);

        return \OpenPayU_Util::verifySignature(
            $data,
            $signature['signature'],
            $config->signatureKey,
            $signature['algorithm']
        );
    }
}
