<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Util\PaymentHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Cancel;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

class CancelAction implements ActionInterface
{
    private const FINAL_STATUSES = [
        OrderStatus::Completed,
        OrderStatus::Canceled,
    ];

    public function __construct(
        private OrderRequestService $orderRequestService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param Cancel $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());

        $payment = PaymentHelper::ensurePayment($request->getFirstModel());
        Assert::notNull($payment, 'Payment must be set on cancel action.');
        $orderId = PaymentHelper::getOrderId($model, $payment);
        Assert::notEmpty($orderId, 'OrderId must be set on cancel action.');

        try {
            if (!$this->canCancelPayment($model, $payment)) {
                return;
            }

            $this->orderRequestService->cancel($model->orderId(), PaymentHelper::getConfigKey($model, $payment));
        } catch (\Throwable $throwable) {
            $this->logger->critical('Cannot cancel payment.', ['exception' => $throwable]);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Cancel
            && $request->getModel() instanceof \ArrayAccess
            && $request->getFirstModel() instanceof PaymentInterface;
    }

    private function canCancelPayment(Model $model, PaymentInterface $payment): bool
    {
        $response = $this->orderRequestService->retrieve($model->orderId(), PaymentHelper::getConfigKey($model, $payment));
        $status = $response->orders[0]->status;

        return !in_array($status, self::FINAL_STATUSES, true);
    }
}
