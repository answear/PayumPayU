<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

class BooleanTransformer
{
    public static function toString(?bool $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value ? 'true' : 'false';
    }
}
