<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

class JsonHelper
{
    public static function getArrayFromObject(object $object): array
    {
        return json_decode(
            json_encode($object, JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
