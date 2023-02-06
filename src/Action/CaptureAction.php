<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\Model\Payment;
use Answear\Payum\PayU\ApiAwareTrait;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Enum\RecurringEnum;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Util\PaymentHelper;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\StatusCode;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;

class CaptureAction implements ActionInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = Model::ensureArrayObject($request->getModel());
        $firstModel = PaymentHelper::getPaymentOrNull($request->getFirstModel());
        $token = $request->getToken();

        if (!empty($model->orderId())) {
            throw new \LogicException('Capture payment with order id present is forbidden.');
        }

        $orderRequest = $this->prepareOrderRequest($token, $model);
        if (RecurringEnum::Standard === $model->recurring()) {
            $this->setRecurringStandardPayment($orderRequest, $model);
        }

        $orderCreatedResponse = $this->api->createOrder($orderRequest, PaymentHelper::getConfigKey($model, $firstModel));
        $model->setPayUResponse($orderCreatedResponse);
        if (StatusCode::Success === $orderCreatedResponse->status->statusCode) {
            $this->updateModel($model, $orderCreatedResponse, $firstModel);
            $request->setModel($model);

            throw new HttpRedirect($orderCreatedResponse->redirectUri ?? $token->getTargetUrl());
        }

        if (StatusCode::WarningContinue3ds === $orderCreatedResponse->status->statusCode) {
            $this->updateModel($model, $orderCreatedResponse, $firstModel);
            $request->setModel($model);

            throw new HttpRedirect($orderCreatedResponse->redirectUri);
        }

        throw PayUException::withResponse(
            'Create payment fails.',
            $orderCreatedResponse,
            $model,
            $firstModel
        );
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess
            && $request->getFirstModel() instanceof PaymentInterface;
    }

    private function prepareOrderRequest(TokenInterface $token, Model $model): OrderRequest
    {
        return new OrderRequest(
            $model->description(),
            $model->currencyCode(),
            $model->totalAmount(),
            $model->customerIp(),
            $this->tokenFactory->createNotifyToken($token->getGatewayName(), $token->getDetails())->getTargetUrl(),
            $model->getProducts() ?: [
                new Product(
                    $model->description(),
                    $model->totalAmount(),
                    1
                ),
            ],
            $model->validityTime(),
            $model->extOrderId(),
            $token->getAfterUrl(),
            $model->buyer(),
            $model->payMethod(),
            $model->additionalDescription(),
            $model->visibleDescription(),
            $model->statementDescription()
        );
    }

    private function setRecurringStandardPayment(OrderRequest $orderRequest, Model $model): void
    {
        $payMethods = $this->api->retrievePayMethodsForUser($model->clientId(), $model->clientEmail());
        if (empty($payMethods->cardTokens)) {
            throw new \InvalidArgumentException('Cannot make this recurring payment. Token for user does not exist.');
        }
        $cardToken = $this->findPreferredToken($payMethods->cardTokens, $model->creditCardMaskedNumber());
        if (null === $cardToken) {
            throw new \InvalidArgumentException('Cannot make this recurring payment. Token for user does not exist.');
        }

        $orderRequest->setRequiring($model->recurring(), new PayMethod(PayMethodType::CardToken, $cardToken['value']));
    }

    private function findPreferredToken(array $tokens, ?string $creditCardMaskedNumber = null): ?array
    {
        $tokens = array_filter(
            $tokens,
            static fn($token) => 'ACTIVE' === $token['status']
                && (null === $creditCardMaskedNumber || $token['cardNumberMasked'] === $creditCardMaskedNumber)
        );
        if (empty($tokens)) {
            return null;
        }

        foreach ($tokens as $token) {
            if ($token->preferred) {
                return $token;
            }
        }

        return reset($tokens);
    }

    private function updateModel(Model $model, OrderCreatedResponse $orderCreatedResponse, ?PaymentInterface $firstModel): void
    {
        $model->setOrderId($orderCreatedResponse->orderId);
        if ($firstModel instanceof Payment) {
            $firstModel->setOrderId($orderCreatedResponse->orderId);
        }

        /**
         * Documentation says nothing about this kind of responses with payMethod on order creating
         * Keep it but with more knowledge need to refactor
         */
        if (isset($orderCreatedResponse->payMethods['payMethod'])) {
            $model->setCreditCardMaskedNumber($orderCreatedResponse->payMethods['payMethod']['card']['number'] ?? null);
            if (null !== $firstModel && $firstModel->getCreditCard()) {
                $firstModel->getCreditCard()->setMaskedNumber($orderCreatedResponse->payMethods['payMethod']['card']['number'] ?? null);
            }
        }
    }
}
