<?php

declare(strict_types=1);

namespace App\States\OrganizationInvitation\Transitions;

use App\Models\OrganizationInvitation;
use Spatie\ModelStates\DefaultTransition;

final class AcceptInvitationTransition extends DefaultTransition
{
    public function handle(): OrganizationInvitation
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->accepted_at = now();
        $this->model->save();

        return $this->model;
    }
}
