<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

readonly class Buyer
{
    public function __construct(
        public string $email,
        public string $firstName,
        public string $lastName,
        public ?string $phone = null,
        public ?string $customerId = null,
        public ?string $extCustomerId = null,
        public ?string $nin = null,
        public ?string $language = null,
        public ?Delivery $delivery = null,
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
