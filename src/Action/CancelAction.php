<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\PayU\Exception\CannotCancelPaymentException;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Util\PaymentHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Cancel;
use Webmozart\Assert\Assert;

class CancelAction implements ActionInterface
{
    public function __construct(
        private OrderRequestService $orderRequestService,
    ) {
    }

    /**
     * @param Cancel $request
     *
     * @throws PayUException
     * @throws CannotCancelPaymentException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());
        $payment = PaymentHelper::ensurePayment($request->getFirstModel());
        $orderId = PaymentHelper::getOrderId($model, $payment);
        Assert::notEmpty($orderId, 'OrderId must be set on cancel action.');
        $configKey = PaymentHelper::getConfigKey($model, $payment);

        if (!$this->canCancelPayment($orderId, $configKey)) {
            throw new CannotCancelPaymentException('Order status is final, cannot cancel payment.');
        }

        $this->orderRequestService->cancel($orderId, $configKey);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Cancel
            && $request->getModel() instanceof \ArrayAccess
            && $request->getFirstModel() instanceof PaymentInterface;
    }

    /**
     * @throws PayUException
     */
    private function canCancelPayment(string $orderId, ?string $configKey): bool
    {
        $response = $this->orderRequestService->retrieve($orderId, $configKey);

        return !$response->orders[0]->status->isFinal();
    }
}
