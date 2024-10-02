<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

use Webmozart\Assert\Assert;

readonly class OrderRetrieveTransactionResponse
{
    /**
     * @param array<Order> $orders
     * @param array<Property> $properties
     */
    public function __construct(
        public array $orders,
        public ResponseStatus $status,
        public array $properties,
    ) {
        Assert::allIsInstanceOf($this->orders, Order::class);
        Assert::allIsInstanceOf($this->properties, Property::class);
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            array_map(
                static fn(array $orderData) => Order::fromResponse($orderData),
                $response['orders']
            ),
            ResponseStatus::fromResponse($response['status']),
            array_map(
                static fn(array $propertyArray) => new Property($propertyArray['name'], $propertyArray['value']),
                $response['properties'] ?? []
            ),
        );
    }
}
