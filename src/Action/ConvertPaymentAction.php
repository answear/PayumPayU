<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\PayU\Enum\ModelFields;
use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Enum\RecurringEnum;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Model\Payment;
use Answear\Payum\PayU\Util\UserIpHelper;
use Answear\Payum\PayU\ValueObject\Buyer;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * 259200s - 72h - 3 days
     */
    private const VALIDITY_TIME = 259200;

    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = Model::ensureArrayObject($payment->getDetails());
        $details->update(
            [
                ModelFields::TOTAL_AMOUNT => $payment->getTotalAmount(),
                ModelFields::CURRENCY => $payment->getCurrencyCode(),
                ModelFields::EXT_ORDER_ID => $payment->getNumber(),
                ModelFields::DESCRIPTION => $payment->getDescription(),
                ModelFields::CLIENT_EMAIL => $payment->getClientEmail(),
                ModelFields::CLIENT_ID => $payment->getClientId(),
                ModelFields::CUSTOMER_IP => UserIpHelper::getIp(),
                ModelFields::CREDIT_CARD_MASKED_NUMBER => $payment->getCreditCard() ? $payment->getCreditCard()->getMaskedNumber() : null,
                ModelFields::VALIDITY_TIME => self::VALIDITY_TIME,
            ]
        );
        if ($payment instanceof Payment) {
            $details->setConfigKey($payment->getConfigKey());

            $paidFor = $payment->getPaidFor();
            $buyer = new Buyer(
                $paidFor->getEmail(),
                $paidFor->getFirstName(),
                $paidFor->getSurname(),
                $paidFor->getPhone(),
                null,
                $payment->getClientId(),
                null,
                $payment->getLanguage(),
                null,
            );

            $details->setBuyer($buyer);
        }

        if (RecurringEnum::First === $details->recurring() && !empty($payment->getCreditCard()?->getToken())) {
            $details->update(
                [
                    ModelFields::PAY_METHODS => [
                        ModelFields::PAY_METHOD => [
                            'value' => $payment->getCreditCard()->getToken(),
                            'type' => PayMethodType::CardToken->value,
                        ],
                    ],
                ]
            );
        }

        if (null === $details->buyer()) {
            $buyer = new Buyer(
                $payment->getClientEmail(),
                $details[ModelFields::BUYER_FIRSTNAME] ?? '',
                $details[ModelFields::BUYER_LASTNAME] ?? '',
                extCustomerId: $payment->getClientId()
            );
            $details->setBuyer($buyer);
        }

        $details->setStatus(OrderStatus::New);

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' === $request->getTo();
    }
}
