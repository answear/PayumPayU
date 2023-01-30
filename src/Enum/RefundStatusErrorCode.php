<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

/** @see https://developers.payu.com/pl/restapi.html#refunds_retrieve */
enum RefundStatusErrorCode: string
{
    case BankDeclined = 'BANK_DECLINED';
    case ProviderDeclined = 'PROVIDER_DECLINED';
    case ProviderLimitError = 'PROVIDER_LIMIT_ERROR';
    case ProviderSecurityError = 'PROVIDER_SECURITY_ERROR';
    case ProviderTechnicalError = 'PROVIDER_TECHNICAL_ERROR';
    case BankUnavailableError = 'BANK_UNAVAILABLE_ERROR';
    case RefundTooLate = 'REFUND_TOO_LATE';
    case TechnicalError = 'TECHNICAL_ERROR';
    case RefundTooFast = 'REFUND_TOO_FAST';
    case RefundImpossible = 'REFUND_IMPOSSIBLE';
}
