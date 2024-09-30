<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

use Answear\Payum\PayU\Util\BooleanTransformer;
use Webmozart\Assert\Assert;

readonly class Product
{
    public function __construct(
        public string $name,
        public int $unitPrice,
        public int $quantity,
        public ?bool $virtual = null,
        public ?\DateTimeImmutable $listingDate = null,
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
            'virtual' => BooleanTransformer::stringOrNull($this->virtual),
            'listingDate' => $this->listingDate?->format(\DateTimeInterface::ATOM),
        ];
    }

    public static function fromResponse(array $response): self
    {
        Assert::numeric($response['unitPrice']);
        Assert::numeric($response['quantity']);

        return new self(
            $response['name'],
            (int) $response['unitPrice'],
            (int) $response['quantity'],
            BooleanTransformer::boolOrNull($response['virtual'] ?? null),
            isset($response['listingDate']) ? new \DateTimeImmutable($response['listingDate']) : null
        );
    }
}
