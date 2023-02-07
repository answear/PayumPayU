<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

use Answear\Payum\PayU\Util\JsonHelper;

class PayURequestException extends PayUException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($previous instanceof \OpenPayU_Exception_Request && $previous->getOriginalResponse()) {
            $this->response = JsonHelper::getArrayFromObject($this->getOriginalResponse($previous->getOriginalResponse()));
        }

        parent::__construct($message, $code, $previous);
    }

    private function getOriginalResponse($originalResponse): object
    {
        if ($originalResponse instanceof \OpenPayU_Result) {
            return $originalResponse->getResponse();
        }

        return $originalResponse;
    }
}
