<?php

declare(strict_types=1);

namespace Modules\Billing\Listeners;

use App\Events\OrganizationMemberAdded;
use App\Events\OrganizationMemberRemoved;
use Modules\Billing\Actions\SyncSubscriptionSeatsAction;

final readonly class SyncSubscriptionSeatsOnMemberChange
{
    public function __construct(
        private SyncSubscriptionSeatsAction $syncSeats
    ) {}

    public function handle(OrganizationMemberAdded|OrganizationMemberRemoved $event): void
    {
        $this->syncSeats->handle($event->organization);
    }
}
