<?php

declare(strict_types=1);

namespace Modules\Billing\States\RefundRequest;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class RefundRequestStatus extends State
{
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Approved::class)
            ->allowTransition(Pending::class, Rejected::class)
            ->allowTransition(Approved::class, Processed::class);
    }
}
