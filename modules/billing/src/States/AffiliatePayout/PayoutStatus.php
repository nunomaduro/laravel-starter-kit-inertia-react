<?php

declare(strict_types=1);

namespace Modules\Billing\States\AffiliatePayout;

use Modules\Billing\States\AffiliatePayout\Transitions\MarkPayoutCompletedTransition;
use Modules\Billing\States\AffiliatePayout\Transitions\MarkPayoutFailedTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class PayoutStatus extends State
{
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition(Processing::class, Completed::class, MarkPayoutCompletedTransition::class)
            ->allowTransition(Processing::class, Failed::class, MarkPayoutFailedTransition::class);
    }
}
