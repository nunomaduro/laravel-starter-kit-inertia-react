<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

use App\Models\Organization;
use DateTimeInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MartinPetricko\LaravelDatabaseMail\Events\Concerns\CanTriggerDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Events\Contracts\TriggersDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Recipients\Recipient;

final class TrialEndingReminder implements TriggersDatabaseMail
{
    use CanTriggerDatabaseMail;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Organization $organization,
        public string $planName,
        public int $daysRemaining,
        public ?DateTimeInterface $trialEndsAt = null,
    ) {}

    public static function getDescription(): string
    {
        return 'Fires when a trial ending reminder is sent (e.g. 7, 3, 1 days before trial ends).';
    }

    public static function getName(): string
    {
        return 'Trial ending reminder';
    }

    /**
     * @return array<string, Recipient<TrialEndingReminder>>
     */
    public static function getRecipients(): array
    {
        return [
            'owner' => new Recipient('Organization owner', function (TrialEndingReminder $event): array {
                $owner = $event->organization->owner;

                return $owner !== null ? [$owner] : [];
            }),
        ];
    }
}
