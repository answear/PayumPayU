<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Order;

use Answear\Payum\PayU\Enum\PayMethodType;

class PayMethod
{
    public function __construct(
        public readonly PayMethodType $type,
        public readonly ?string $value = null,
        public readonly ?string $authorizationCode = null,
        public readonly ?array $specificData = null,
    ) {
        if (PayMethodType::PaymentWall !== $this->type && empty($value)) {
            throw new \InvalidArgumentException(sprintf('Value is required for type %s.', $this->type->value));
        }
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'value' => $this->value,
            'authorizationCode' => $this->authorizationCode,
            'specificData' => $this->specificData,
        ];
    }
}
