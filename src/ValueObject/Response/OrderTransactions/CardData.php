<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response\OrderTransactions;

class CardData
{
    public function __construct(
        public readonly string $cardNumberMasked,
        public readonly string $cardScheme,
        public readonly string $cardProfile,
        public readonly string $cardClassification,
        public readonly string $cardResponseCode,
        public readonly string $cardResponseCodeDesc,
        public readonly string $cardEciCode,
        public readonly string $card3DsStatus,
        public readonly string $card3DsFrictionlessIndicator,
        public readonly string $card3DsStatusDescription,
        public readonly string $cardBinCountry,
        public readonly string $firstTransactionId,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            $response['cardNumberMasked'] ?? '',
            $response['cardScheme'] ?? '',
            $response['cardProfile'] ?? '',
            $response['cardClassification'] ?? '',
            $response['cardResponseCode'] ?? '',
            $response['cardResponseCodeDesc'] ?? '',
            $response['cardEciCode'] ?? '',
            $response['card3DsStatus'] ?? '',
            $response['card3DsFrictionlessIndicator'] ?? '',
            $response['card3DsStatusDescription'] ?? '',
            $response['cardBinCountry'] ?? '',
            $response['firstTransactionId'] ?? '',
        );
    }
}
