<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\ValueObject\Request\Order;

use Answear\Payum\PayU\Enum\ChallengeRequestedType;

class ThreeDsAuthentication
{
    public function __construct(
        public readonly ChallengeRequestedType $challengeRequested,
    ) {
    }

    /**
     * @return array<string, string|int|array|null>
     */
    public function toArray(): array
    {
        return [
            'challangeRequested' => $this->challengeRequested->value,
        ];
    }
}
