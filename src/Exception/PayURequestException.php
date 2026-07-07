<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

use GuzzleHttp\Exception\ClientException;

class PayURequestException extends PayUException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->setOriginalResponse($previous);

        parent::__construct($message, $code, $previous);
    }

    private function setOriginalResponse(?\Throwable $previous): void
    {
        if ($previous instanceof ClientException) {
            $originalResponse = $previous->getResponse()->getBody()->getContents();
            $this->response = json_decode($originalResponse, true);
        }
    }
}
