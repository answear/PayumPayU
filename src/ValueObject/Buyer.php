<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

class Buyer
{
    public function __construct(
        public readonly string $email,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly ?string $phone = null,
        public readonly ?string $customerId = null,
        public readonly ?string $extCustomerId = null,
        public readonly ?string $nin = null,
        public readonly ?string $language = null,
        public readonly ?Delivery $delivery = null,
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'phone' => $this->phone,
            'customerId' => $this->customerId,
            'extCustomerId' => $this->extCustomerId,
            'nin' => $this->nin,
            'language' => $this->language,
            'delivery' => $this->delivery?->toArray(),
        ];
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['email'],
            $response['firstName'],
            $response['lastName'],
            $response['phone'] ?? null,
            $response['customerId'] ?? null,
            $response['extCustomerId'] ?? null,
            $response['nin'] ?? null,
            $response['language'] ?? null,
            isset($response['delivery']) ? Delivery::fromResponse($response['delivery']) : null
        );
    }
}
