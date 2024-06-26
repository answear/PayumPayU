<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Model;

use Answear\Payum\PayU\Enum\CardOnFileEnum;
use Answear\Payum\PayU\Enum\ChallengeRequestedType;
use Answear\Payum\PayU\Enum\ModelFields;
use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Enum\RecurringEnum;
use Answear\Payum\PayU\Util\BooleanTransformer;
use Answear\Payum\PayU\ValueObject\Buyer;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\Order\ThreeDsAuthentication;
use Answear\Payum\PayU\ValueObject\Response;
use Payum\Core\Bridge\Spl\ArrayObject;

class Model extends ArrayObject
{
    public static function ensureArrayObject($input): self
    {
        if ($input instanceof self) {
            return $input;
        }

        if ($input instanceof ArrayObject) {
            return new self($input->getArrayCopy());
        }

        return new self($input);
    }

    public function customerIp(): ?string
    {
        return $this[ModelFields::CUSTOMER_IP] ?? null;
    }

    public function description(): string
    {
        return $this[ModelFields::DESCRIPTION];
    }

    public function currencyCode(): string
    {
        return $this[ModelFields::CURRENCY];
    }

    public function totalAmount(): int
    {
        return (int) $this[ModelFields::TOTAL_AMOUNT];
    }

    public function orderId(): ?string
    {
        return $this[ModelFields::ORDER_ID] ?? null;
    }

    public function setOrderId(string $orderId): void
    {
        $this[ModelFields::ORDER_ID] = $orderId;
    }

    public function extOrderId(): ?string
    {
        return $this[ModelFields::EXT_ORDER_ID] ?? null;
    }

    public function setExtOrderId(string $extOrderId): void
    {
        $this[ModelFields::EXT_ORDER_ID] = $extOrderId;
    }

    public function buyer(): ?Buyer
    {
        $buyer = $this[ModelFields::BUYER] ?? null;
        if (empty($buyer)) {
            return null;
        }

        return Buyer::fromResponse($buyer);
    }

    public function setBuyer(Buyer $buyer): void
    {
        $this[ModelFields::BUYER] = $buyer->toArray();
    }

    public function setBuyerPhone(?string $phone): void
    {
        $this[ModelFields::BUYER] = array_merge(
            $this[ModelFields::BUYER] ?? [],
            [ModelFields::BUYER_PHONE => $phone]
        );
    }

    public function payMethod(): ?PayMethod
    {
        if (!isset($this[ModelFields::PAY_METHODS])) {
            return null;
        }

        $type = $this[ModelFields::PAY_METHODS][ModelFields::PAY_METHOD][ModelFields::PAY_METHOD_TYPE] ?? '';

        return new PayMethod(
            $type instanceof PayMethodType ? $type : PayMethodType::tryFrom($type),
            $this[ModelFields::PAY_METHODS][ModelFields::PAY_METHOD][ModelFields::PAY_METHOD_VALUE],
            $this[ModelFields::PAY_METHODS][ModelFields::PAY_METHOD][ModelFields::PAY_METHOD_AUTHORIZATION_CODE] ?? null,
            $this[ModelFields::PAY_METHODS][ModelFields::PAY_METHOD][ModelFields::PAY_METHOD_SPECIFIC_DATA] ?? null,
        );
    }

    public function additionalDescription(): ?string
    {
        return $this[ModelFields::ADDITIONAL_DESCRIPTION] ?? null;
    }

    public function visibleDescription(): ?string
    {
        return $this[ModelFields::VISIBLE_DESCRIPTION] ?? null;
    }

    public function statementDescription(): ?string
    {
        return $this[ModelFields::STATEMENT_DESCRIPTION] ?? null;
    }

    public function validityTime(): ?int
    {
        return isset($this[ModelFields::VALIDITY_TIME]) ? (int) $this[ModelFields::VALIDITY_TIME] : null;
    }

    /**
     * @return array<Product>
     */
    public function getProducts(): array
    {
        if (!isset($this['products'])) {
            return [];
        }

        return array_map(
            static fn(array $product) => new Product(
                $product[ModelFields::PRODUCT_NAME],
                (int) $product[ModelFields::PRODUCT_UNIT_PRICE],
                (int) $product[ModelFields::PRODUCT_QUANTITY],
                BooleanTransformer::boolOrNull($product[ModelFields::PRODUCT_VIRTUAL] ?? null),
                isset($product[ModelFields::PRODUCT_LISTING_DATE])
                    ? new \DateTimeImmutable($product[ModelFields::PRODUCT_LISTING_DATE])
                    : null
            ),
            $this['products']
        );
    }

    public function recurring(): ?RecurringEnum
    {
        return RecurringEnum::tryFrom($this[ModelFields::RECURRING] ?? '');
    }

    public function clientId(): ?string
    {
        return $this[ModelFields::CLIENT_ID] ?? null;
    }

    public function clientEmail(): ?string
    {
        return $this[ModelFields::CLIENT_EMAIL] ?? null;
    }

    public function creditCardMaskedNumber(): ?string
    {
        return $this[ModelFields::CREDIT_CARD_MASKED_NUMBER] ?? null;
    }

    public function setCreditCardMaskedNumber(?string $creditCardMaskedNumber): void
    {
        $this[ModelFields::CREDIT_CARD_MASKED_NUMBER] = $creditCardMaskedNumber;
    }

    public function cardOnFile(): ?CardOnFileEnum
    {
        return CardOnFileEnum::tryFrom($this[ModelFields::CARD_ON_FILE] ?? '');
    }

    public function setCardOnFile(?CardOnFileEnum $cardOnFileEnum): void
    {
        $this[ModelFields::CARD_ON_FILE] = $cardOnFileEnum?->value;
    }

    public function threeDsAuthentication(): ?ThreeDsAuthentication
    {
        if (!isset($this[ModelFields::THREE_DS_AUTHENTICATION])) {
            return null;
        }

        return new ThreeDsAuthentication(
            ChallengeRequestedType::from($this[ModelFields::THREE_DS_AUTHENTICATION][ModelFields::CHALLENGE_REQUESTED])
        );
    }

    public function setPayUResponse(Response\OrderCreatedResponse $orderCreatedResponse): void
    {
        $this[ModelFields::PAYU_RESPONSE] = $orderCreatedResponse->toArray();
    }

    public function redirectUri(): ?string
    {
        return $this[ModelFields::PAYU_RESPONSE][ModelFields::REDIRECT_URI] ?? null;
    }

    public function configKey(): ?string
    {
        return $this[ModelFields::CONFIG_KEY] ?? null;
    }

    public function setConfigKey(?string $configKey): void
    {
        $this[ModelFields::CONFIG_KEY] = $configKey;
    }

    public function status(): ?OrderStatus
    {
        if (empty($this[ModelFields::STATUS])) {
            return null;
        }

        return OrderStatus::from($this[ModelFields::STATUS]);
    }

    public function setStatus(OrderStatus|string $status): void
    {
        $status = $status instanceof OrderStatus ? $status->value : $status;

        $this[ModelFields::STATUS] = $status;
    }

    public function updateRefundData(array $singleRefundData): void
    {
        $refundId = $singleRefundData[ModelFields::REFUND_ID];
        $currentRefund = $this[ModelFields::REFUND][$refundId] ?? [];

        $this[ModelFields::REFUND] = array_replace(
            $this[ModelFields::REFUND] ?? [],
            [$refundId => array_merge($currentRefund, $singleRefundData)]
        );
    }

    public function setProperty(Response\Property $property): void
    {
        $this[ModelFields::PROPERTIES] = array_replace($this[ModelFields::PROPERTIES] ?? [], [$property->name => $property->value]);
    }
}
