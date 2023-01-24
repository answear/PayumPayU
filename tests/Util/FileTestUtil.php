<?php

declare(strict_types=1);

namespace Answear\OverseasBundle\Tests\Util;

class FileTestUtil
{
    public static function getFileContents(string $filePath): string
    {
        $contents = file_get_contents($filePath);
        if (false === $contents) {
            throw new \InvalidArgumentException($filePath . ' does not contain any data.');
        }

        return $contents;
    }

    public static function decodeJsonFromFile(string $filePath): array
    {
        $json = self::getFileContents($filePath);

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
