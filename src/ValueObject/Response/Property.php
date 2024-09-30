<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

readonly class Property
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
