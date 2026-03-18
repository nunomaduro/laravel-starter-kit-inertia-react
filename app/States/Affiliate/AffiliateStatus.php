<?php

declare(strict_types=1);

namespace App\States\Affiliate;

use App\States\Affiliate\Transitions\ApproveAffiliateTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class AffiliateStatus extends State
{
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Active::class, ApproveAffiliateTransition::class)
            ->allowTransition(Pending::class, Suspended::class)
            ->allowTransition(Pending::class, Rejected::class)
            ->allowTransition(Active::class, Suspended::class)
            ->allowTransition(Suspended::class, Active::class);
    }
}
