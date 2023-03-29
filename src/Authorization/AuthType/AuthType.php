<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Authorization\AuthType;

interface AuthType
{
    public function getHeaders(): array;
}
