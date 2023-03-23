<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Integration\Action;

use Answear\Payum\Model\PaidForInterface;
use Answear\Payum\PayU\Action\ConvertPaymentAction;
use Answear\Payum\PayU\Service\UserIpService;
use Answear\Payum\PayU\Tests\Payment;
use Payum\Core\Model\CreditCard;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;

class ConvertActionTest extends TestCase
{
    /**
     * @test
     */
    public function convertWithLowInfoTest(): void
    {
        $convertAction = new ConvertPaymentAction(new UserIpService());

        $paidFor = $this->createMock(PaidForInterface::class);
        $paidFor->method('getEmail')
            ->willReturn('email2@test.fake');
        $paidFor->method('getFirstName')
            ->willReturn('Firstname');
        $paidFor->method('getSurname')
            ->willReturn('Surname');
        $paidFor->method('getPhone')
            ->willReturn('123123123');

        $payment = new Payment();
        $payment->setLanguage('pl');
        $payment->setConfigKey('pos2');
        $payment->setPaidFor($paidFor);
        $convert = new Convert($payment, 'array');

        $convertAction->execute($convert);

        self::assertSame(
            [
                'totalAmount' => null,
                'currencyCode' => null,
                'extOrderId' => null,
                'description' => null,
                'clientEmail' => null,
                'clientId' => null,
                'customerIp' => null,
                'creditCardMaskedNumber' => null,
                'validityTime' => 259200,
                'configKey' => 'pos2',
                'buyer' => [
                    'email' => 'email2@test.fake',
                    'firstName' => 'Firstname',
                    'lastName' => 'Surname',
                    'phone' => '123123123',
                    'customerId' => null,
                    'extCustomerId' => null,
                    'nin' => null,
                    'language' => 'pl',
                    'delivery' => null,
                ],
                'status' => 'NEW',
            ],
            $convert->getResult()
        );
    }

    /**
     * @test
     */
    public function convertWithFullDataTest(): void
    {
        $convertAction = new ConvertPaymentAction(new UserIpService());

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $creditCard = new CreditCard();
        $creditCard->setMaskedNumber('masked-number');

        $paidFor = $this->createMock(PaidForInterface::class);
        $paidFor->method('getEmail')
            ->willReturn('email2@test.fake');
        $paidFor->method('getFirstName')
            ->willReturn('Firstname');
        $paidFor->method('getSurname')
            ->willReturn('Surname');
        $paidFor->method('getPhone')
            ->willReturn('123123123');

        $payment = new Payment();
        $payment->setTotalAmount(1001);
        $payment->setCurrencyCode('PLN');
        $payment->setNumber('extOrderId');
        $payment->setDescription('Some description');
        $payment->setClientEmail('email@test.fake');
        $payment->setClientId('2874');
        $payment->setCreditCard($creditCard);
        $payment->setLanguage('pl');
        $payment->setConfigKey('pos2');
        $payment->setPaidFor($paidFor);
        $convert = new Convert($payment, 'array');

        $convertAction->execute($convert);

        self::assertSame(
            [
                'totalAmount' => 1001,
                'currencyCode' => 'PLN',
                'extOrderId' => 'extOrderId',
                'description' => 'Some description',
                'clientEmail' => 'email@test.fake',
                'clientId' => '2874',
                'customerIp' => '127.0.0.1',
                'creditCardMaskedNumber' => $creditCard->getMaskedNumber(),
                'validityTime' => 259200,
                'configKey' => 'pos2',
                'buyer' => [
                    'email' => 'email2@test.fake',
                    'firstName' => 'Firstname',
                    'lastName' => 'Surname',
                    'phone' => '123123123',
                    'customerId' => null,
                    'extCustomerId' => '2874',
                    'nin' => null,
                    'language' => 'pl',
                    'delivery' => null,
                ],
                'status' => 'NEW',
            ],
            $convert->getResult()
        );
    }
}
