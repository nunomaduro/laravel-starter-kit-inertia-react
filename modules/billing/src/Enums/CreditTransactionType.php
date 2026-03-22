<?php

declare(strict_types=1);

namespace Modules\Billing\Enums;

enum CreditTransactionType: string
{
    case Subscription = 'subscription';
    case Purchase = 'purchase';
    case Bonus = 'bonus';
    case Referral = 'referral';
    case Usage = 'usage';
    case Refund = 'refund';
    case Expiry = 'expiry';
    case Rollover = 'rollover';
    case Adjustment = 'adjustment';
}
