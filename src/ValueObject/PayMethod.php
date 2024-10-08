<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

use Answear\Payum\PayU\Enum\PayMethodType;

readonly class PayMethod
{
    public function __construct(
        public ?PayMethodType $type,
        public ?string $value = null,
    ) {
        if (null === $this->type && null === $this->value) {
            throw new \InvalidArgumentException('Type or value are required.');
        }
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            isset($response['type']) ? PayMethodType::from($response['type']) : null,
            $response['value'] ?? null
        );
    }
}
