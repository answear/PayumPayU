<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\PayU\Enum\OrderStatus;
use Answear\Payum\PayU\Model\Model;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = Model::ensureArrayObject($request->getModel());

        switch (true) {
            case OrderStatus::New === $model->status():
                $request->markNew();

                return;
            case OrderStatus::Pending === $model->status():
            case OrderStatus::WaitingForConfirmation === $model->status():
                $request->markPending();

                return;
            case OrderStatus::Completed === $model->status():
                $request->markCaptured();

                return;
            case OrderStatus::Canceled === $model->status():
                $request->markCanceled();

                return;
        }

        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
