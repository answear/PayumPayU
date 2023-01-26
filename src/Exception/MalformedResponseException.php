<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Exception;

class MalformedResponseException extends \RuntimeException
{
    private const MESSAGE = 'Cannot handle response data.';

    public function __construct(public array $response, ?\Throwable $previous = null)
    {
        parent::__construct(self::MESSAGE, $previous?->getCode() ?? 0, $previous);
    }
}
