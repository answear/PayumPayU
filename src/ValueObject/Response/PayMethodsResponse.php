<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Response;

class PayMethodsResponse
{
    /**
     * @param array<PayByLink> $payByLinks
     */
    public function __construct(
        public readonly array $cardTokens,
        public readonly array $pexTokens,
        public readonly array $payByLinks,
        public readonly ResponseStatus $status,
    ) {
    }

    public static function fromResponse(array $response): self
    {
        $response['cardTokens'] = $response['cardTokens'] ?? [];
        $response['pexTokens'] = $response['pexTokens'] ?? [];
        $response['payByLinks'] = $response['payByLinks'] ?? [];

        return new self(
            $response['cardTokens'] ?: [],
            $response['pexTokens'] ?: [],
            array_map(
                static fn(array $payByLink) => PayByLink::fromResponse($payByLink),
                $response['payByLinks'] ?: [],
            ),
            ResponseStatus::fromResponse($response['status'])
        );
    }

    public function toArray(): array
    {
        return [
            'cardTokens' => $this->cardTokens,
            'pexTokens' => $this->pexTokens,
            'payByLinks' => array_map(
                static fn(PayByLink $payByLink) => $payByLink->toArray(),
                $this->payByLinks
            ),
            'status' => $this->status->toArray(),
        ];
    }
}
