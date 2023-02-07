<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\PayU\ApiAwareTrait;
use Answear\Payum\PayU\Enum\ModelFields;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Util\PaymentHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Payment;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    private LoggerInterface $logger;

    private const SIGNATURE_HEADER = 'openpayu-signature';
    private const ORDER_KEY = 'order';
    private const REFUND_KEY = 'refund';
    private const PAYMENT_ID_PROPERTY = 'PAYMENT_ID';

    public function setApi($api): void
    {
        $this->api = $api;
        $this->logger = $this->api->getLogger();
    }

    /**
     * @param Notify $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());
        $firstModel = PaymentHelper::getPaymentOrNull($request->getFirstModel());
        $token = $request->getToken();
        Assert::notNull($firstModel, 'Payment must be set on notify action.');
        Assert::notNull($token, 'Token must be set on notify action.');

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $this->assertRequestValid($httpRequest, $model, $firstModel);

        $content = json_decode($httpRequest->content, true, 512, JSON_THROW_ON_ERROR);
        $this->logger->info(
            'Notify action',
            [
                'orderId' => $model->orderId(),
                'model' => $model->getArrayCopy(),
                'content' => $content,
            ]
        );
        if (isset($content[self::REFUND_KEY])) {
            $this->refundNotify($request, $model, $firstModel, $content[self::REFUND_KEY]);
        } else {
            $this->orderNotify($model, $content[self::ORDER_KEY] ?? [], $firstModel);
        }

        $this->updateRequestStatus($model, $firstModel, $token);

        $this->logger->info(
            'Notify action successful',
            [
                'orderId' => $model->orderId(),
                'model' => $model->getArrayCopy(),
            ]
        );

        throw new HttpResponse('OK', 200);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }

    private function refundNotify(Notify $request, Model $model, Payment $firstModel, array $refundData): void
    {
        $orderId = PaymentHelper::getOrderId($model, $firstModel);
        $this->updatePaymentStatus($model, $orderId, $firstModel);
        $model->updateRefundData($refundData);

        $request->setModel($model);
    }

    private function orderNotify(Model $model, array $orderData, ?Payment $firstModel): void
    {
        /** @see https://developers.payu.com/pl/restapi.html#update_notification_for_order_status */
        $orderId = $orderData[ModelFields::ORDER_ID] ?? null;
        if (null === $orderId) {
            throw new \LogicException('No orderId on notify.');
        }
        $model->setOrderId($orderId);

        $this->updatePaymentStatus($model, $orderId, $firstModel);
    }

    private function updatePaymentStatus(Model $model, string $orderId, ?Payment $firstModel): void
    {
        $response = $this->api->retrieveOrder($orderId, PaymentHelper::getConfigKey($model, $firstModel));
        if (ResponseStatusCode::Success === $response->status->statusCode) {
            $model->setStatus($response->orders[0]->status);
            foreach ($response->properties as $property) {
                if (self::PAYMENT_ID_PROPERTY === $property->name) {
                    $model->setProperty($property);
                }
            }
        }
    }

    private function updateRequestStatus(Model $model, Payment $firstModel, TokenInterface $token): void
    {
        $status = new GetHumanStatus($token);
        $status->setModel($firstModel);
        $status->setModel($model);
        $this->gateway->execute($status);
    }

    private function assertRequestValid(GetHttpRequest $httpRequest, Model $model, ?Payment $firstModel): void
    {
        if (!property_exists($httpRequest, 'headers')) {
            $exception = new PayUException('Request is not valid');
            $exception->model = $model;

            throw $exception;
        }

        $headers = $httpRequest->headers;
        $signatureHeader = $headers[self::SIGNATURE_HEADER] ?? null;
        if (null === $signatureHeader) {
            throw new \InvalidArgumentException('Signature is not set', 400);
        }
        if (\is_array($signatureHeader)) {
            $signatureHeader = reset($signatureHeader);
        }

        if (!$this->api->signatureIsValid($signatureHeader, $httpRequest->content, PaymentHelper::getConfigKey($model, $firstModel))) {
            throw new \InvalidArgumentException('Signature is not valid', 400);
        }
    }
}
