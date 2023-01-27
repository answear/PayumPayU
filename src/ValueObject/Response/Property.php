<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

class Property
{
    public function __construct(
        public readonly string $name,
        public readonly string $value,
    ) {
    }
}
