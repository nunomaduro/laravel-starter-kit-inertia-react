<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

use App\Models\Organization;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MartinPetricko\LaravelDatabaseMail\Events\Concerns\CanTriggerDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Events\Contracts\TriggersDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Recipients\Recipient;

final class DunningFailedPaymentReminder implements TriggersDatabaseMail
{
    use CanTriggerDatabaseMail;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Organization $organization,
        public int $attemptNumber,
        public int $daysSinceFailure,
    ) {}

    public static function getDescription(): string
    {
        return 'Fires when a dunning (failed payment reminder) email is sent to the organization owner.';
    }

    public static function getName(): string
    {
        return 'Dunning failed payment reminder';
    }

    /**
     * @return array<string, Recipient<DunningFailedPaymentReminder>>
     */
    public static function getRecipients(): array
    {
        return [
            'owner' => new Recipient('Organization owner', function (DunningFailedPaymentReminder $event): array {
                $owner = $event->organization->owner;

                return $owner !== null ? [$owner] : [];
            }),
        ];
    }
}
