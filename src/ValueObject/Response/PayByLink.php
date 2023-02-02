<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Answear\Payum\PayU\Enum\PayByLinkStatus;

class PayByLink
{
    private const AMOUNT_MAX = 99999999;

    public function __construct(
        public readonly string $value,
        public readonly string $name,
        public readonly string $brandImageUrl,
        public readonly PayByLinkStatus $status,
        public readonly ?int $minAmount = null,
        public readonly ?int $maxAmount = null,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['value'],
            $response['name'],
            $response['brandImageUrl'],
            PayByLinkStatus::from($response['status']),
            (!isset($response['minAmount']) || 0 === $response['minAmount']) ? null : $response['minAmount'],
            (!isset($response['maxAmount']) || self::AMOUNT_MAX === $response['maxAmount']) ? null : $response['maxAmount'],
        );
    }
}
