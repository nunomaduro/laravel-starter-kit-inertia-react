<?php

declare(strict_types=1);

namespace Modules\Billing\States\AffiliateCommission\Transitions;

use Modules\Billing\Models\AffiliateCommission;
use Spatie\ModelStates\DefaultTransition;

final class CancelCommissionTransition extends DefaultTransition
{
    public function handle(): AffiliateCommission
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->save();
        $this->model->affiliate->decrement('pending_earnings', $this->model->amount);

        return $this->model;
    }
}
