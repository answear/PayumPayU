<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Auth;

use Answear\Payum\PayU\Enum\OAuthGrantType;
use Psr\Http\Message\ResponseInterface;
use Webmozart\Assert\Assert;

readonly class OAuthResultClientCredentials
{
    public function __construct(
        public string $accessToken,
        public string $tokenType,
        public int $expiresIn,
        public OAuthGrantType $grantType,
        public \DateTimeImmutable $expireDate,
    ) {
    }

    public static function fromResponse(ResponseInterface $response): self
    {
        $content = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        foreach (['expires_in', 'access_token', 'token_type', 'grant_type'] as $requireKey) {
            Assert::keyExists($content, $requireKey, sprintf('Key %s is required for oauth credentials', $requireKey));
        }

        $expiresIn = (int) $content['expires_in'];

        return new self(
            $content['access_token'],
            $content['token_type'],
            $expiresIn,
            OAuthGrantType::from($content['grant_type']),
            self::calculateExpireDate(new \DateTimeImmutable(), $expiresIn)
        );
    }

    private static function calculateExpireDate(\DateTimeImmutable $date, int $expiresIn): \DateTimeImmutable
    {
        return $date->add(new \DateInterval('PT' . ($expiresIn - 60) . 'S'));
    }

    public function isExpired(): bool
    {
        return $this->expireDate <= new \DateTimeImmutable();
    }
}
