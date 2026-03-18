<?php

declare(strict_types=1);

namespace App\States\AffiliateCommission;

use App\States\AffiliateCommission\Transitions\ApproveCommissionTransition;
use App\States\AffiliateCommission\Transitions\CancelCommissionTransition;
use App\States\AffiliateCommission\Transitions\MarkCommissionPaidTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class CommissionStatus extends State
{
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Approved::class, ApproveCommissionTransition::class)
            ->allowTransition(Pending::class, Cancelled::class)
            ->allowTransition(Approved::class, Paid::class, MarkCommissionPaidTransition::class)
            ->allowTransition(Approved::class, Cancelled::class, CancelCommissionTransition::class);
    }
}
