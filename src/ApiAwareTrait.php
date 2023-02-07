<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

trait ApiAwareTrait
{
    protected Api $api;

    public function setApi($api): void
    {
        $this->api = $api;
    }
}
