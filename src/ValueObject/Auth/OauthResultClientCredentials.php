<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Auth;

use Answear\Payum\PayU\Enum\OauthGrantType;
use Psr\Http\Message\ResponseInterface;

class OauthResultClientCredentials
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly OauthGrantType $grantType,
        public readonly \DateTimeImmutable $expireDate
    ) {
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = $response->getBody();
        $expiresIn = (int) $body['expires_in'];

        return new self(
            $body['access_token'],
            $body['token_type'],
            $expiresIn,
            OauthGrantType::from($body['grant_type']),
            self::calculateExpireDate(new \DateTimeImmutable(), $expiresIn)
        );
    }

    private static function calculateExpireDate(\DateTimeImmutable $date, int $expiresIn): \DateTimeImmutable
    {
        return $date->add(new \DateInterval('PT' . ($expiresIn - 60) . 'S'));
    }

    public function hasExpire(): bool
    {
        return $this->expireDate <= new \DateTimeImmutable();
    }
}
