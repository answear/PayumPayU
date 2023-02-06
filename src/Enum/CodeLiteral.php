<?php

declare(strict_types=1);

namespace Answear\Payum\PayU\Enum;

enum CodeLiteral: string
{
    case MissingRefundSection = 'MISSING_REFUND_SECTION';
    case TransNotEnded = 'TRANS_NOT_ENDED';
    case NoBalance = 'NO_BALANCE';
    case AmountToBig = 'AMOUNT_TO_BIG';
    case AmountToSmall = 'AMOUNT_TO_SMALL';
    case RefundDisabled = 'REFUND_DISABLED';
    case RefundToOften = 'REFUND_TO_OFTEN';
    case RefundOuterIdNotUnique = 'REFUND_OUTER_ID_NOT_UNIQUE';
    case Paid = 'PAID';
    case UnknownError = 'UNKNOWN_ERROR';
    case RefundIdempotencyMismatch = 'REFUND_IDEMPOTENCY_MISMATCH';
    case TransBillingEntriesNotCompleted = 'TRANS_BILLING_ENTRIES_NOT_COMPLETED';
    case TransTooOld = 'TRANS_TOO_OLD';
    case RemainingTransAmountTooSmall = 'REMAINING_TRANS_AMOUNT_TOO_SMALL';
    // UnknownCode - custom code if something new
    case UnknownCode = 'UNKNOWN_CODE';
}
