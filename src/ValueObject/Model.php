<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject;

use Answear\Payum\PayU\Util\BooleanTransformer;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Payum\Core\Bridge\Spl\ArrayObject;

class Model extends ArrayObject
{
    public const STATUS = 'status';
    public const TOTAL_AMOUNT = 'totalAmount';
    public const CURRENCY = 'currencyCode';
    public const DESCRIPTION = 'description';
    public const ORDER_ID = 'orderId';
    public const EXT_ORDER_ID = 'extOrderId';
    public const BUYER = 'buyer';
    public const BUYER_FIRSTNAME = 'firstName';
    public const BUYER_LASTNAME = 'lastName';
    public const BUYER_PHONE = 'phone';
    public const BUYER_CUSTOMER_ID = 'customerID';
    public const BUYER_EXT_CUSTOMER_ID = 'extCustomerID';
    public const BUYER_NIN = 'nin';
    public const BUYER_LANGUAGE = 'language';
    public const BUYER_EMAIL = 'email';
    public const VALIDITY_TIME = 'validityTime';
    public const CUSTOMER_IP = 'customerIp';
    public const PAY_METHODS = 'payMethods';
    public const PAY_METHOD = 'payMethod';
    public const PAY_METHOD_TYPE = 'type';
    public const PAY_METHOD_VALUE = 'value';
    public const DELIVERY = 'delivery';
    public const DELIVERY_STATE = 'state';
    public const DELIVERY_POSTAL_BOX = 'postalBox';
    public const DELIVERY_RECIPIENT_PHONE = 'recipientPhone';
    public const DELIVERY_RECIPIENT_EMAIL = 'recipientEmail';
    public const DELIVERY_RECIPIENT_NAME = 'recipientName';
    public const DELIVERY_NAME = 'name';
    public const DELIVERY_COUNTRY_CODE = 'countryCode';
    public const DELIVERY_CITY = 'city';
    public const DELIVERY_POSTAL_CODE = 'postalCode';
    public const DELIVERY_STREET = 'street';
    public const PRODUCT_LISTING_DATE = 'listingDate';
    public const PRODUCT_VIRTUAL = 'virtual';
    public const PRODUCT_QUANTITY = 'quantity';
    public const PRODUCT_UNIT_PRICE = 'unitPrice';
    public const PRODUCT_NAME = 'name';
    public const STATEMENT_DESCRIPTION = 'statementDescription';
    public const VISIBLE_DESCRIPTION = 'visibleDescription';
    public const ADDITIONAL_DESCRIPTION = 'additionalDescription';
    public const RECURRING = 'recurring';
    public const CLIENT_ID = 'clientId';
    public const CLIENT_EMAIL = 'clientEmail';
    public const CREDIT_CARD_MASKED_NUMBER = 'creditCardMaskedNumber';
    public const PAYU_RESPONSE = 'payuResponse';

    public static function ensureArrayObject($input): self
    {
        if ($input instanceof self) {
            return $input;
        }

        if ($input instanceof ArrayObject) {
            return new self($input->input);
        }

        return new self($input);
    }

    public function customerIp(): ?string
    {
        return $this[self::CUSTOMER_IP] ?? null;
    }

    public function description(): string
    {
        return $this[self::DESCRIPTION];
    }

    public function currencyCode(): string
    {
        return $this[self::CURRENCY];
    }

    public function totalAmount(): int
    {
        return (int) $this[self::TOTAL_AMOUNT];
    }

    public function orderId(): ?string
    {
        return $this[self::ORDER_ID] ?? null;
    }

    public function setOrderId(string $orderId): void
    {
        $this[self::ORDER_ID] = $orderId;
    }

    public function extOrderId(): ?string
    {
        return $this[self::EXT_ORDER_ID] ?? null;
    }

    public function buyer(): Buyer
    {
        $buyer = $this[self::BUYER];
        $delivery = $buyer[self::DELIVERY] ?? null;

        return new Buyer(
            $buyer[self::BUYER_EMAIL],
            $buyer[self::BUYER_FIRSTNAME],
            $buyer[self::BUYER_LASTNAME],
            $buyer[self::BUYER_PHONE],
            $buyer[self::BUYER_CUSTOMER_ID] ?? null,
            $buyer[self::BUYER_EXT_CUSTOMER_ID] ?? null,
            $buyer[self::BUYER_NIN] ?? null,
            $buyer[self::BUYER_LANGUAGE] ?? $this[self::BUYER_LANGUAGE] ?? null,
            null === $delivery ? null : new Delivery(
                $delivery[self::DELIVERY_STREET],
                $delivery[self::DELIVERY_POSTAL_CODE],
                $delivery[self::DELIVERY_CITY],
                $delivery[self::DELIVERY_COUNTRY_CODE] ?? null,
                $delivery[self::DELIVERY_NAME] ?? null,
                $delivery[self::DELIVERY_RECIPIENT_NAME] ?? null,
                $delivery[self::DELIVERY_RECIPIENT_EMAIL] ?? null,
                $delivery[self::DELIVERY_RECIPIENT_PHONE] ?? null,
                $delivery[self::DELIVERY_POSTAL_BOX] ?? null,
                $delivery[self::DELIVERY_STATE] ?? null,
            ),
        );
    }

    public function payMethod(): ?PayMethod
    {
        if (!isset($this[self::PAY_METHODS])) {
            return null;
        }

        return new PayMethod(
            $this[self::PAY_METHODS][self::PAY_METHOD][self::PAY_METHOD_TYPE],
            $this[self::PAY_METHODS][self::PAY_METHOD][self::PAY_METHOD_VALUE],
        );
    }

    public function additionalDescription(): ?string
    {
        return $this[self::ADDITIONAL_DESCRIPTION] ?? null;
    }

    public function visibleDescription(): ?string
    {
        return $this[self::VISIBLE_DESCRIPTION] ?? null;
    }

    public function statementDescription(): ?string
    {
        return $this[self::STATEMENT_DESCRIPTION] ?? null;
    }

    public function validityTime(): ?int
    {
        return isset($this[self::VALIDITY_TIME]) ? (int) $this[self::VALIDITY_TIME] : null;
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
                $product[self::PRODUCT_NAME],
                (int) $product[self::PRODUCT_UNIT_PRICE],
                (int) $product[self::PRODUCT_QUANTITY],
                BooleanTransformer::boolOrNull($product[self::PRODUCT_VIRTUAL] ?? null),
                isset($product[self::PRODUCT_LISTING_DATE]) ? new \DateTimeImmutable($product[self::PRODUCT_LISTING_DATE]) : null
            ),
            $this['products']
        );
    }

    public function recurring(): ?string
    {
        return $this[self::RECURRING] ?? null;
    }

    public function clientId(): ?string
    {
        return $this[self::CLIENT_ID] ?? null;
    }

    public function clientEmail(): ?string
    {
        return $this[self::CLIENT_EMAIL] ?? null;
    }

    public function creditCardMaskedNumber(): ?string
    {
        return $this[self::CREDIT_CARD_MASKED_NUMBER] ?? null;
    }

    public function setCreditCardMaskedNumber(?string $creditCardMaskedNumber): void
    {
        $this[self::CREDIT_CARD_MASKED_NUMBER] = $creditCardMaskedNumber;
    }

    public function setPayUResponse(Response\OrderCreatedResponse $orderCreatedResponse): void
    {
        $this[self::PAYU_RESPONSE] = $orderCreatedResponse->toArray();
    }
}
