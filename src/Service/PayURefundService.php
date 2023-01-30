<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Service;

class PayURefundService extends \OpenPayU
{
    public static function retrieveRefundList(string $orderId): ?\OpenPayU_Result
    {
        if (empty($orderId)) {
            throw new \OpenPayU_Exception('Invalid orderId value for refund');
        }
        try {
            $authType = \OpenPayU_Refund::getAuth();
        } catch (\OpenPayU_Exception $e) {
            throw new \OpenPayU_Exception($e->getMessage(), $e->getCode());
        }

        $pathUrl = \OpenPayU_Configuration::getServiceUrl() . 'orders/' . $orderId . '/refunds';

        return \OpenPayU_Refund::verifyResponse(\OpenPayU_Http::doGet($pathUrl, $authType));
    }

    public static function retrieveSingleRefund(string $orderId, string $refundId): ?\OpenPayU_Result
    {
        if (empty($orderId)) {
            throw new \OpenPayU_Exception('Invalid orderId value for refund');
        }
        if (empty($refundId)) {
            throw new \OpenPayU_Exception('Invalid refundId value for refund');
        }

        try {
            $authType = \OpenPayU_Refund::getAuth();
        } catch (\OpenPayU_Exception $e) {
            throw new \OpenPayU_Exception($e->getMessage(), $e->getCode());
        }

        $pathUrl = \OpenPayU_Configuration::getServiceUrl() . 'orders/' . $orderId . '/refunds/' . $refundId;

        return \OpenPayU_Refund::verifyResponse(\OpenPayU_Http::doGet($pathUrl, $authType));
    }
}
