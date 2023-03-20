<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;

class PayURequestException extends PayUException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($previous instanceof ClientException && $previous->getResponse()) {
            $originalResponse = $this->getOriginalResponse($previous->getResponse());
            $this->response = null === $originalResponse ? null : json_decode($originalResponse, true, 512, JSON_THROW_ON_ERROR);
        }

        parent::__construct($message, $code, $previous);
    }

    private function getOriginalResponse($originalResponse): ?string
    {
        if ($originalResponse instanceof Response) {
            return $originalResponse->getBody()->getContents();
        }

        return null;
    }
}
