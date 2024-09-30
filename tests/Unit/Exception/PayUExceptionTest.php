<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Unit\Exception;

use Answear\Payum\PayU\Exception\PayUNetworkException;
use Answear\Payum\PayU\Exception\PayURequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class PayUExceptionTest extends TestCase
{
    #[Test]
    public function emptyJsonNetworkException(): void
    {
        $exception = new PayUNetworkException('Exception', 0, $this->getPreviousException());

        self::assertNull($exception->response);
    }

    #[Test]
    public function emptyJsonRequestException(): void
    {
        $exception = new PayURequestException('Exception', 0, $this->getPreviousException(''));

        self::assertNull($exception->response);
    }

    #[Test]
    public function jsonDecodedNetworkException(): void
    {
        $exception = new PayURequestException('Exception', 0, $this->getPreviousException('{"status":false}'));

        self::assertSame(['status' => false], $exception->response);
    }

    #[Test]
    public function jsonDecodedRequestException(): void
    {
        $exception = new PayURequestException('Exception', 0, $this->getPreviousException('{"status":true}'));

        self::assertSame(['status' => true], $exception->response);
    }

    private function getPreviousException(?string $body = null): ClientException
    {
        return new ClientException(
            'Client exception',
            $this->createMock(RequestInterface::class),
            new Response(400, [], $body)
        );
    }
}
