<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\Model\Payment;
use Answear\Payum\PayU\Enum\ModelFields;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Service\PayULogger;
use Answear\Payum\PayU\Service\SignatureValidator;
use Answear\Payum\PayU\Util\PaymentHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use Webmozart\Assert\Assert;

class NotifyAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    protected const SIGNATURE_HEADER = 'openpayu-signature';
    protected const ORDER_KEY = 'order';
    protected const REFUND_KEY = 'refund';

    protected array $notifyContent;

    public function __construct(
        private OrderRequestService $orderRequestService,
        private SignatureValidator $signatureValidator,
        private PayULogger $logger
    ) {
    }

    /**
     * @param Notify $request
     *
     * @throws HttpResponse
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());
        $payment = PaymentHelper::ensurePayment($request->getFirstModel());
        $token = $request->getToken();
        Assert::notNull($token, 'Token must be set on notify action.');

        $this->onExecute($payment, $model);
        $this->onPostExecute($request, $payment, $model, $token);

        throw new HttpResponse('OK', 200);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify
            && $request->getModel() instanceof \ArrayAccess
            && $request->getFirstModel() instanceof PaymentInterface;
    }

    protected function onExecute(PaymentInterface $payment, Model $model): void
    {
        $content = $this->getNotifyContent($model, $payment);
        $this->logger->info(
            'Notify action',
            [
                'orderId' => $model->orderId(),
                'model' => $model->getArrayCopy(),
                'content' => $content,
            ]
        );
        if ($this->isRefundNotify($content)) {
            $this->refundNotify($model, $payment, $content[self::REFUND_KEY]);
        } else {
            $this->orderNotify($model, $content[self::ORDER_KEY] ?? [], $payment);
        }
    }

    protected function onPostExecute(Notify $request, PaymentInterface $payment, Model $model, TokenInterface $token): void
    {
        $this->updatePayment($request, $payment, $model, $token);

        $this->logger->info(
            'Notify action successfully processed',
            [
                'orderId' => $model->orderId(),
                'model' => $model->getArrayCopy(),
            ]
        );
    }

    protected function updatePayment(Notify $request, PaymentInterface $payment, Model $model, TokenInterface $token): void
    {
        $payment->setDetails($model);
        $request->setModel($model);

        $status = new GetHumanStatus($token);
        $status->setModel($payment);
        $this->gateway->execute($status);
        /** Payment will be auto-updated on @see \Payum\Core\Extension\StorageExtension::onPostExecute */
    }

    protected function isRefundNotify(array $content): bool
    {
        return isset($content[self::REFUND_KEY][ModelFields::REFUND_ID]);
    }

    protected function getNotifyContent(Model $model, ?PaymentInterface $firstModel): array
    {
        if (isset($this->notifyContent)) {
            return $this->notifyContent;
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        $this->assertRequestValid($httpRequest, $model, $firstModel);

        $this->notifyContent = json_decode($httpRequest->content, true, 512, JSON_THROW_ON_ERROR);

        return $this->notifyContent;
    }

    private function refundNotify(Model $model, PaymentInterface $firstModel, array $refundData): void
    {
        $orderId = PaymentHelper::getOrderId($model, $firstModel);
        $this->updatePaymentStatus($model, $orderId, $firstModel);
        $model->updateRefundData($refundData);
    }

    private function orderNotify(Model $model, array $orderData, ?PaymentInterface $firstModel): void
    {
        /** @see https://developers.payu.com/pl/restapi.html#update_notification_for_order_status */
        $orderId = $orderData[ModelFields::ORDER_ID] ?? null;
        if (null === $orderId) {
            throw new \LogicException('No orderId on notify.');
        }
        $model->setOrderId($orderId);
        if ($firstModel instanceof Payment) {
            $firstModel->setOrderId($orderId);
        }

        $this->updatePaymentStatus($model, $orderId, $firstModel);
    }

    private function updatePaymentStatus(Model $model, string $orderId, ?PaymentInterface $firstModel): void
    {
        $response = $this->orderRequestService->retrieve($orderId, PaymentHelper::getConfigKey($model, $firstModel));
        if (ResponseStatusCode::Success === $response->status->statusCode) {
            $model->setStatus($response->orders[0]->status);
            foreach ($response->properties as $property) {
                $model->setProperty($property);
            }
        }
    }

    private function assertRequestValid(GetHttpRequest $httpRequest, Model $model, ?PaymentInterface $firstModel): void
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

        if (!$this->signatureValidator->isValid($signatureHeader, $httpRequest->content, PaymentHelper::getConfigKey($model, $firstModel))) {
            throw new \InvalidArgumentException('Signature is not valid', 400);
        }
    }
}
