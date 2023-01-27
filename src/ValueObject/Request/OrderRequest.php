<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request;

use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Webmozart\Assert\Assert;

class OrderRequest
{
    /**
     * @param ?int $validityTime - 86400 by default
     * @param array<Product> $products
     */
    public function __construct(
        public readonly string $merchantPosId,
        public readonly string $description,
        public readonly string $currencyCode,
        public readonly int $totalAmount,
        public readonly string $customerIp,
        public readonly string $notifyUrl,
        public readonly array $products = [],
        public readonly ?int $validityTime = null,
        public readonly ?string $extOrderId = null,
        public readonly ?string $continueUrl = null,
        public readonly ?Buyer $buyer = null,
        public readonly ?PayMethod $payMethod = null,
        public readonly ?string $additionalDescription = null,
        public readonly ?string $visibleDescription = null,
        public readonly ?string $statementDescription = null,
    ) {
        Assert::notEmpty($this->products);
        Assert::allIsInstanceOf($this->products, Product::class);
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'extOrderId' => $this->extOrderId,
            'notifyUrl' => $this->notifyUrl,
            'customerIp' => $this->customerIp,
            'merchantPosId' => $this->merchantPosId,
            'validityTime' => $this->validityTime,
            'description' => $this->description,
            'additionalDescription' => $this->additionalDescription,
            'visibleDescription' => $this->visibleDescription,
            'statementDescription' => $this->statementDescription,
            'currencyCode' => $this->currencyCode,
            'totalAmount' => $this->totalAmount,
            'continueUrl' => $this->continueUrl,
            'buyer' => $this->buyer->toArray(),
            'products' => array_map(static fn(Product $product) => $product->toArray(), $this->products),
            'payMethods' => ['payMethod' => $this->payMethod->toArray()],
        ];
    }
}
