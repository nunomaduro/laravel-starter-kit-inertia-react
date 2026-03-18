<?php

declare(strict_types=1);

namespace App\States\AffiliateCommission\Transitions;

use App\Models\Billing\AffiliateCommission;
use Spatie\ModelStates\DefaultTransition;

final class MarkCommissionPaidTransition extends DefaultTransition
{
    public function handle(): AffiliateCommission
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->paid_at = now();
        $this->model->save();
        $this->model->affiliate->decrement('pending_earnings', $this->model->amount);
        $this->model->affiliate->increment('paid_earnings', $this->model->amount);

        return $this->model;
    }
}
