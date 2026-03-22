<?php

declare(strict_types=1);

namespace Modules\Billing\States\Affiliate\Transitions;

use Modules\Billing\Models\Affiliate;
use Spatie\ModelStates\DefaultTransition;

final class ApproveAffiliateTransition extends DefaultTransition
{
    public function handle(): Affiliate
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->approved_at = now();
        $this->model->save();

        return $this->model;
    }
}
