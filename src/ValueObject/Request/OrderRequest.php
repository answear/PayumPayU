<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request;

use Answear\Payum\PayU\Enum\AuthType;
use Answear\Payum\PayU\Enum\CardOnFileEnum;
use Answear\Payum\PayU\Enum\RecurringEnum;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\Order\ThreeDsAuthentication;
use Webmozart\Assert\Assert;

class OrderRequest
{
    public const METHOD = 'POST';
    public const AUTH_TYPE = AuthType::Basic;

    /**
     * @param ?int $validityTime - 86400 by default
     * @param array<Product> $products
     */
    public function __construct(
        public readonly string $description,
        public readonly string $currencyCode,
        public readonly int $totalAmount,
        public readonly string $customerIp,
        public readonly ?string $notifyUrl = null,
        public readonly array $products = [],
        public readonly ?int $validityTime = null,
        public readonly ?string $extOrderId = null,
        public readonly ?string $continueUrl = null,
        public readonly ?Buyer $buyer = null,
        public ?PayMethod $payMethod = null,
        public readonly ?string $additionalDescription = null,
        public readonly ?string $visibleDescription = null,
        public readonly ?string $statementDescription = null,
        public ?CardOnFileEnum $cardOnFile = null,
        public ?string $recurring = null,
        public ?ThreeDsAuthentication $threeDsAuthentication = null,
    ) {
        Assert::notEmpty($this->products);
        Assert::allIsInstanceOf($this->products, Product::class);
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(string $merchantPosId): array
    {
        return [
            'extOrderId' => $this->extOrderId,
            'notifyUrl' => $this->notifyUrl,
            'customerIp' => $this->customerIp,
            'merchantPosId' => $merchantPosId,
            'validityTime' => $this->validityTime,
            'description' => $this->description,
            'additionalDescription' => $this->additionalDescription,
            'visibleDescription' => $this->visibleDescription,
            'statementDescription' => $this->statementDescription,
            'currencyCode' => $this->currencyCode,
            'totalAmount' => $this->totalAmount,
            'continueUrl' => $this->continueUrl,
            'buyer' => $this->buyer?->toArray(),
            'products' => array_map(static fn(Product $product) => $product->toArray(), $this->products),
            'payMethods' => null === $this->payMethod ? null : ['payMethod' => $this->payMethod->toArray()],
            'threeDsAuthentication' => $this->threeDsAuthentication?->toArray(),
            'cardOnFile' => $this->cardOnFile?->value,
            'recurring' => $this->recurring,
        ];
    }

    public function setRequiring(?RecurringEnum $recurring, PayMethod $payMethod): void
    {
        $this->recurring = $recurring?->value;
        $this->payMethod = $payMethod;
    }
}
