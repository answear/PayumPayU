<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Request;

use Answear\Payum\PayU\Exception\PayUException;
use Answear\Payum\PayU\Util\ExceptionHelper;
use Answear\Payum\PayU\ValueObject\Response;

/**
 * @interal
 * Use \Answear\Payum\PayU\Api::class instead
 */
class Shop
{
    /**
     * @throws PayUException
     */
    public function getShopInfo(string $publicShopId): Response\ShopInfo
    {
        try {
            return Response\ShopInfo::fromPayUShop(\OpenPayU_Shop::get($publicShopId));
        } catch (\Throwable $exception) {
            throw ExceptionHelper::getPayUException($exception);
        }
    }
}
