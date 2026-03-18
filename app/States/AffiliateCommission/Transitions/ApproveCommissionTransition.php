<?php

declare(strict_types=1);

namespace App\States\AffiliateCommission\Transitions;

use App\Models\Billing\AffiliateCommission;
use Spatie\ModelStates\DefaultTransition;

final class ApproveCommissionTransition extends DefaultTransition
{
    public function handle(): AffiliateCommission
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->approved_at = now();
        $this->model->save();
        $this->model->affiliate->increment('pending_earnings', $this->model->amount);

        return $this->model;
    }
}
