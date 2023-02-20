<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\Action\Request\SyncPayment;
use Answear\Payum\Model\Payment;
use Answear\Payum\PayU\ApiAwareTrait;
use Answear\Payum\PayU\Enum\ResponseStatusCode;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Util\PaymentHelper;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;

class SyncPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    /**
     * @param SyncPayment $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $payment = PaymentHelper::ensurePayment($request->getModel());
        $model = Model::ensureArrayObject($payment->getDetails());
        if (empty($model->orderId())) {
            throw new \LogicException('Model does not have order id.');
        }

        $this->updatePaymentStatus($model, $payment);
        $this->updatePayment($payment, $model);
    }

    public function supports($request): bool
    {
        return
            $request instanceof SyncPayment &&
            $request->getModel() instanceof PaymentInterface;
    }

    protected function updatePayment(PaymentInterface $payment, Model $model): void
    {
        $payment->setDetails($model);
        if ($payment instanceof Payment) {
            $payment->setOrderId($model->orderId());
        }
    }

    protected function updatePaymentStatus(Model $model, PaymentInterface $payment): void
    {
        $response = $this->api->retrieveOrder($model->orderId(), PaymentHelper::getConfigKey($model, $payment));
        if (ResponseStatusCode::Success === $response->status->statusCode) {
            $model->setStatus($response->orders[0]->status);
            foreach ($response->properties as $property) {
                $model->setProperty($property);
            }
        }
    }
}
