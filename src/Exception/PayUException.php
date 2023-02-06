<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\PaymentInterface;

class PayUException extends \Exception
{
    public ?array $response = null;
    public ?PaymentInterface $payment = null;
    public ?ArrayObject $model = null;

    public static function withResponse(
        string $message,
        OrderCreatedResponse $orderCreatedResponse,
        ArrayObject $model,
        ?PaymentInterface $payment
    ): self {
        $exception = new self($message);
        $exception->response = $orderCreatedResponse->toArray();
        $exception->payment = $payment;
        $exception->model = $model;

        return $exception;
    }
}
