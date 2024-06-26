<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Action;

use Answear\Payum\Model\Payment;
use Answear\Payum\PayU\Enum\PayMethodType;
use Answear\Payum\PayU\Enum\RecurringEnum;
use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Model\Model;
use Answear\Payum\PayU\Request\OrderRequestService;
use Answear\Payum\PayU\Request\PayMethodsRequestService;
use Answear\Payum\PayU\Util\PaymentHelper;
use Answear\Payum\PayU\ValueObject\Product;
use Answear\Payum\PayU\ValueObject\Request\Order\PayMethod;
use Answear\Payum\PayU\ValueObject\Request\OrderRequest;
use Answear\Payum\PayU\ValueObject\Response\OrderCreated\StatusCode;
use Answear\Payum\PayU\ValueObject\Response\OrderCreatedResponse;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;

class CaptureAction implements ActionInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct(
        private OrderRequestService $orderRequestService,
        private PayMethodsRequestService $payMethodsRequestService
    ) {
    }

    /**
     * @param Capture $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $firstModel = PaymentHelper::ensurePayment($request->getFirstModel());
        $token = $request->getToken();

        $this->convertAction($firstModel, $token);
        $model = Model::ensureArrayObject($firstModel->getDetails());
        if (!empty($model->orderId())) {
            throw new \LogicException('Capture payment with order id present is forbidden.');
        }

        $configKey = PaymentHelper::getConfigKey($model, $firstModel);
        $orderRequest = $this->prepareOrderRequest($request, $token, $model);
        if (RecurringEnum::Standard === $model->recurring()) {
            $this->setRecurringStandardPayment($orderRequest, $model, $configKey);
        }

        $orderCreatedResponse = $this->orderRequestService->create($orderRequest, $configKey);
        $model->setPayUResponse($orderCreatedResponse);
        if (StatusCode::Success === $orderCreatedResponse->status->statusCode) {
            $this->updatePayment($model, $orderCreatedResponse, $firstModel, $token);
            $request->setModel($model);

            throw new HttpRedirect($orderCreatedResponse->redirectUri ?? $token->getAfterUrl());
        }

        if (StatusCode::WarningContinue3ds === $orderCreatedResponse->status->statusCode) {
            $this->updatePayment($model, $orderCreatedResponse, $firstModel, $token);
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

    private function updatePayment(
        Model $model,
        OrderCreatedResponse $orderCreatedResponse,
        PaymentInterface $payment,
        TokenInterface $token
    ): void {
        $model->setOrderId($orderCreatedResponse->orderId);
        if ($payment instanceof Payment) {
            $payment->setOrderId($orderCreatedResponse->orderId);
        }

        /**
         * Documentation says nothing about this kind of responses with payMethod on order creating
         * Keep it but with more knowledge need to refactor
         */
        if (isset($orderCreatedResponse->payMethods['payMethod'])) {
            $model->setCreditCardMaskedNumber($orderCreatedResponse->payMethods['payMethod']['card']['number'] ?? null);
            if ($payment->getCreditCard()) {
                $payment->getCreditCard()->setMaskedNumber($orderCreatedResponse->payMethods['payMethod']['card']['number'] ?? null);
            }
        }

        $payment->setDetails($model);

        $status = new GetHumanStatus($token);
        $status->setModel($payment);
        $this->gateway->execute($status);
        /** Payment will be auto-updated on @see \Payum\Core\Extension\StorageExtension::onPostExecute */
    }

    private function prepareOrderRequest(Capture $request, TokenInterface $token, Model $model): OrderRequest
    {
        $payMethod = $model->payMethod();
        if ($request instanceof \Answear\Payum\Action\Request\Capture && null !== $request->payMethod) {
            $payMethod = $request->payMethod;
        }

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
            $payMethod,
            $model->additionalDescription(),
            $model->visibleDescription(),
            $model->statementDescription(),
            $model->cardOnFile(),
            threeDsAuthentication: $model->threeDsAuthentication(),
        );
    }

    private function setRecurringStandardPayment(OrderRequest $orderRequest, Model $model, ?string $configKey): void
    {
        $payMethods = $this->payMethodsRequestService->retrieveForUser($model->clientEmail(), $model->clientId(), $configKey);
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

    private function convertAction(PaymentInterface $payment, TokenInterface $token): void
    {
        $details = $payment->getDetails();
        $status = new GetHumanStatus($payment);
        $status->setModel($details);
        $this->gateway->execute($status);
        if (empty($details) || $status->isNew()) {
            $this->gateway->execute($convert = new Convert($payment, 'array', $token));

            $payment->setDetails($convert->getResult());
        }
    }
}
