<?php

declare(strict_types=1);

namespace App\States\AffiliatePayout;

use App\States\AffiliatePayout\Transitions\MarkPayoutCompletedTransition;
use App\States\AffiliatePayout\Transitions\MarkPayoutFailedTransition;
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
