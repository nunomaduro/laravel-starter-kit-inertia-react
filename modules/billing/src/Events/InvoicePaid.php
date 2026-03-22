<?php

declare(strict_types=1);

namespace Modules\Billing\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use MartinPetricko\LaravelDatabaseMail\Events\Concerns\CanTriggerDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Events\Contracts\TriggersDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Recipients\Recipient;
use Modules\Billing\Models\Invoice;

final class InvoicePaid implements TriggersDatabaseMail
{
    use CanTriggerDatabaseMail;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public static function getDescription(): string
    {
        return 'Fires when an invoice is paid (e.g. Stripe webhook).';
    }

    public static function getName(): string
    {
        return 'Invoice paid';
    }

    /**
     * @return array<string, Recipient<InvoicePaid>>
     */
    public static function getRecipients(): array
    {
        return [
            'owner' => new Recipient('Organization owner', function (InvoicePaid $event): array {
                $owner = $event->invoice->organization->owner;

                return $owner !== null ? [$owner] : [];
            }),
        ];
    }
}
