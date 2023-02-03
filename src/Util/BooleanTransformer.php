<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Util;

class BooleanTransformer
{
    private const TRUE_VALUES = [1, '1', 'true', true, 'yes'];
    private const FALSE_VALUES = [0, '0', 'false', false, 'no'];

    public static function boolOrNull(mixed $value): ?bool
    {
        if (null === $value) {
            return null;
        }

        if (\in_array($value, self::TRUE_VALUES, true)) {
            return true;
        }

        if (\in_array($value, self::FALSE_VALUES, true)) {
            return false;
        }

        throw new \InvalidArgumentException('Invalid bool value.');
    }

    public static function stringOrNull(?bool $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value ? 'true' : 'false';
    }
}
