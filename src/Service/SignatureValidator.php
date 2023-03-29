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

        $signature = $this->parseSignature($signatureHeader);

        return $this->verifySignature(
            $data,
            $signature['signature'],
            $config->signatureKey,
            $signature['algorithm']
        );
    }

    private function parseSignature(string $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        $signatureData = [];
        $list = explode(';', rtrim($data, ';'));
        foreach ($list as $value) {
            $explode = explode('=', $value);
            if (2 !== count($explode)) {
                return null;
            }
            $signatureData[$explode[0]] = $explode[1];
        }

        return $signatureData;
    }

    private function verifySignature(string $message, string $signature, string $signatureKey, string $algorithm = 'MD5'): bool
    {
        if (empty($signature)) {
            return false;
        }
        if ('MD5' === $algorithm) {
            $hash = md5($message . $signatureKey);
        } elseif (in_array($algorithm, ['SHA', 'SHA1', 'SHA-1'])) {
            $hash = sha1($message . $signatureKey);
        } else {
            $hash = hash('sha256', $message . $signatureKey);
        }

        return 0 === strcmp($signature, $hash);
    }
}
