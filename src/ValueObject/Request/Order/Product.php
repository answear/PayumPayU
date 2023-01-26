<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Order;

use Answear\Payum\PayU\Util\BooleanTransformer;

class Product
{
    public function __construct(
        public readonly string $name,
        public readonly int $unitPrice,
        public readonly int $quantity,
        public readonly ?bool $virtual = null,
        public readonly ?\DateTimeImmutable $listingDate = null,
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'unitPrice' => $this->unitPrice,
            'quantity' => $this->quantity,
            'virtual' => BooleanTransformer::toString($this->virtual),
            'listingDate' => $this->listingDate?->format(\DateTimeInterface::ATOM),
        ];
    }
}
