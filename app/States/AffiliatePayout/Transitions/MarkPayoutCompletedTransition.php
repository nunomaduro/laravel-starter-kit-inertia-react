<?php

declare(strict_types=1);

namespace App\States\AffiliatePayout\Transitions;

use App\Models\Billing\AffiliatePayout;
use App\Models\User;
use Spatie\ModelStates\DefaultTransition;
use Spatie\ModelStates\State;

final class MarkPayoutCompletedTransition extends DefaultTransition
{
    public function __construct(
        AffiliatePayout $model,
        string $field,
        State $newState,
        private readonly string $transactionId,
        private readonly User $processor
    ) {
        parent::__construct($model, $field, $newState);
    }

    public function handle(): AffiliatePayout
    {
        $this->model->{$this->field} = $this->newState;
        $this->model->transaction_id = $this->transactionId;
        $this->model->processed_by = $this->processor->id;
        $this->model->processed_at = now();
        $this->model->save();

        return $this->model;
    }
}
