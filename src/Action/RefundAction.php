<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\Action\Request\Refund;
use Answear\Payum\PayU\ApiAwareTrait;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Util\PaymentHelper;
use Answear\Payum\PayU\ValueObject\Request;
use Answear\Payum\PayU\ValueObject\Response\RefundCreatedResponse;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\Payment;
use Webmozart\Assert\Assert;

class RefundAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    /**
     * @param Refund $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());
        $firstModel = PaymentHelper::getPaymentOrNull($request->getFirstModel());
        Assert::notNull($firstModel, 'Payment must be set on refund action.');
        $orderId = PaymentHelper::getOrderId($model, $firstModel);
        Assert::notEmpty($orderId, 'OrderId must be set on refund action.');

        $refundCreatedResponse = $this->api->createRefund(
            $orderId,
            $this->prepareRefundRequest($request),
            PaymentHelper::getConfigKey($model, $firstModel)
        );

        $this->updateRefundData($model, $refundCreatedResponse, $request);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Refund
            && $request->getModel() instanceof \ArrayObject
            && $request->getFirstModel() instanceof Payment;
    }

    private function updateRefundData(Model $model, RefundCreatedResponse $response, Refund $request): void
    {
        $model->updateRefundData($response->refund->toArray());
        $request->setModel($model);
        $request->refundCreatedResponse = $response;
    }

    private function prepareRefundRequest(Refund $request): Request\RefundRequest
    {
        return new Request\RefundRequest(
            new Request\Refund\Refund(
                $request->description,
                $request->amount,
                $request->extCustomerId,
                $request->extRefundId
            )
        );
    }
}
