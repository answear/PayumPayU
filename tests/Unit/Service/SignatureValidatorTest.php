<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit\Service;

use Answear\Payum\PayU\Enum\Environment;
use Answear\Payum\PayU\Service\ConfigProvider;
use Answear\Payum\PayU\Service\SignatureValidator;
use Answear\Payum\PayU\Tests\Util\FileTestUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SignatureValidatorTest extends TestCase
{
    #[Test]
    #[DataProvider('provideSignatureData')]
    public function isValidTest(string $signatureHeader, string $data, $expectedIsValid): void
    {
        self::assertSame(
            $expectedIsValid,
            $this->getService()->isValid($signatureHeader, $data, null),
            'Signature response mismatch!'
        );
    }

    private function getService(): SignatureValidator
    {
        return new SignatureValidator(
            new ConfigProvider(
                Environment::Secure->value,
                [
                    'configKey' => [
                        'public_shop_id' => 'sas323',
                        'pos_id' => '12653',
                        'signature_key' => 'sign_key527',
                        'oauth_client_id' => '98274',
                        'oauth_secret' => 'secret@#$VFSDF',
                    ],
                ]
            )
        );
    }

    public static function provideSignatureData(): iterable
    {
        $notifyData = FileTestUtil::getFileContents(__DIR__ . '/orderNotifyData.json');

        yield [
            'signatureHeader' => 'sender=145227;algorithm=SHA-256;signature=5e1d044aaacd7e45f4d3a65ff2c3d294b83cada4177bb14c79fc62d9394f289a',
            'data' => preg_replace('/\s+/', '', $notifyData),
            'expectedIsValid' => true,
        ];
    }
}
