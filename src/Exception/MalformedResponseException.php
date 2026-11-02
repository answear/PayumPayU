<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

class MalformedResponseException extends \RuntimeException
{
    private const MESSAGE = 'Cannot handle response data.';

    public function __construct(public array $response, ?\Throwable $previous = null)
    {
        $code = $previous?->getCode();

        parent::__construct(self::MESSAGE, \is_int($code) ? $code : 0, $previous);
    }
}
