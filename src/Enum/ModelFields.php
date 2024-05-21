<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

use Answear\Payum\PayU\ValueObject\Buyer;

enum ModelFields
{
    public const STATUS = 'status';
    public const TOTAL_AMOUNT = 'totalAmount';
    public const CURRENCY = 'currencyCode';
    public const DESCRIPTION = 'description';
    public const ORDER_ID = 'orderId';
    public const EXT_ORDER_ID = 'extOrderId';
    /** @see Buyer - all fields from VO */
    public const BUYER = 'buyer';
    public const BUYER_FIRSTNAME = 'firstName';
    public const BUYER_LASTNAME = 'lastName';
    public const BUYER_PHONE = 'phone';
    public const VALIDITY_TIME = 'validityTime';
    public const CUSTOMER_IP = 'customerIp';
    public const PAY_METHODS = 'payMethods';
    public const PAY_METHOD = 'payMethod';
    public const PAY_METHOD_TYPE = 'type';
    public const PAY_METHOD_VALUE = 'value';
    public const PAY_METHOD_AUTHORIZATION_CODE = 'authorizationCode';
    public const PAY_METHOD_SPECIFIC_DATA = 'specificData';
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
    public const REDIRECT_URI = 'redirectUri';
    public const CONFIG_KEY = 'configKey';
    public const REFUND = 'refund';
    public const REFUND_ID = 'refundId';
    public const EXT_REFUND_ID = 'extRefundId';
    public const PROPERTIES = 'properties';
    public const CARD_ON_FILE = 'cardOnFile';
    public const THREE_DS_AUTHENTICATION = 'threeDsAuthentication';
    public const CHALLENGE_REQUESTED = 'challangeRequested';
}
