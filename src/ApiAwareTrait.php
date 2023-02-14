<?php

declare(strict_types=1);

namespace Answear\Payum\PayU;

use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait
{
    protected Api $api;

    public function setApi($api): void
    {
        if (!$api instanceof Api) {
            throw new UnsupportedApiException(sprintf('Not supported api given. It must be an instance of %s', Api::class));
        }

        $this->api = $api;
    }
}
