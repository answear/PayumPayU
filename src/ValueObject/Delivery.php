<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

class Delivery
{
    public function __construct(
        public readonly string $street,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly ?string $countryCode = null,
        public readonly ?string $name = null,
        public readonly ?string $recipientName = null,
        public readonly ?string $recipientEmail = null,
        public readonly ?string $recipientPhone = null,
        public readonly ?string $postalBox = null,
        public readonly ?string $state = null,
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
            'countryCode' => $this->countryCode,
            'name' => $this->name,
            'recipientName' => $this->recipientName,
            'recipientEmail' => $this->recipientEmail,
            'recipientPhone' => $this->recipientPhone,
            'postalBox' => $this->postalBox,
            'state' => $this->state,
        ];
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['street'],
            $response['postalCode'],
            $response['city'],
            $response['countryCode'] ?? null,
            $response['name'] ?? null,
            $response['recipientName'] ?? null,
            $response['recipientEmail'] ?? null,
            $response['recipientPhone'] ?? null,
            $response['postalBox'] ?? null,
            $response['state'] ?? null,
        );
    }
}
