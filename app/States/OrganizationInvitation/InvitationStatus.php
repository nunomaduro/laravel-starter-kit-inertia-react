<?php

declare(strict_types=1);

namespace App\States\OrganizationInvitation;

use App\States\OrganizationInvitation\Transitions\AcceptInvitationTransition;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class InvitationStatus extends State
{
    final public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->allowTransition(Pending::class, Accepted::class, AcceptInvitationTransition::class)
            ->allowTransition(Pending::class, Cancelled::class)
            ->allowTransition(Pending::class, Expired::class);
    }
}
