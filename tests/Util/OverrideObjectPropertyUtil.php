<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Tests\Util;

class OverrideObjectPropertyUtil
{
    public static function getValue(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    public static function override(object $object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass(get_class($object));

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public static function overrideMulti(object $object, array $properties): void
    {
        $reflection = new \ReflectionClass(get_class($object));

        foreach ($properties as $propertyName => $value) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }
    }
}
