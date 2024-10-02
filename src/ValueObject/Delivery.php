<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

readonly class Delivery
{
    public function __construct(
        public string $street,
        public string $postalCode,
        public string $city,
        public ?string $countryCode = null,
        public ?string $name = null,
        public ?string $recipientName = null,
        public ?string $recipientEmail = null,
        public ?string $recipientPhone = null,
        public ?string $postalBox = null,
        public ?string $state = null,
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
