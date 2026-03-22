<?php

declare(strict_types=1);

namespace Database\Seeders\Essential;

use Illuminate\Database\Seeder;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

final class MailTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome (user created)',
                'event' => \App\Events\User\UserCreated::class,
                'subject' => 'Welcome to {{ config("app.name") }}',
                'body' => '<h1>Welcome, {{ $user->name }}</h1><p>Thanks for signing up. We\'re glad to have you.</p>',
                'recipients' => ['user'],
                'attachments' => [],
            ],
            [
                'name' => 'Organization invitation',
                'event' => \App\Events\OrganizationInvitationSent::class,
                'subject' => 'You have been invited to join {{ $organization->name }}',
                'body' => '<p>{{ $invitedBy->name ?? "A team member" }} has invited you to join <strong>{{ $organization->name }}</strong> as a <strong>{{ $role }}</strong>.</p><p><a href="{{ route("invitations.show", ["token" => $invitation->token]) }}">Accept invitation</a></p><p>This invitation expires on {{ $invitation->expires_at->format("F j, Y") }}.</p>',
                'recipients' => ['invitee'],
                'attachments' => [],
            ],
            [
                'name' => 'Invitation accepted (notify inviter)',
                'event' => \App\Events\OrganizationInvitationAccepted::class,
                'subject' => '{{ $user->name }} accepted the invitation to join {{ $organization->name }}',
                'body' => '<p>{{ $user->name }} has accepted your invitation and joined <strong>{{ $organization->name }}</strong>.</p>',
                'recipients' => ['inviter'],
                'attachments' => [],
            ],
            [
                'name' => 'Trial ending reminder',
                'event' => \Modules\Billing\Events\TrialEndingReminder::class,
                'subject' => 'Your trial ends in {{ $daysRemaining }} day(s)',
                'body' => '<h1>Hello!</h1><p>Your free trial of {{ $planName }} ends in {{ $daysRemaining }} days.</p><p>Add a payment method to continue enjoying all features after your trial ends.</p><p><a href="{{ route("billing.index") }}">Billing Dashboard</a></p><p>Questions? Our support team is happy to help.</p>',
                'recipients' => ['owner'],
                'attachments' => [],
            ],
            [
                'name' => 'Dunning (failed payment reminder)',
                'event' => \Modules\Billing\Events\DunningFailedPaymentReminder::class,
                'subject' => 'Payment Update Required',
                'body' => '<p>We were unable to process a recent payment for your {{ $organization->name }} account.</p><p>This is reminder #{{ $attemptNumber }} (day {{ $daysSinceFailure }} since the failure).</p><p><a href="{{ route("dashboard") }}">Update Payment Method</a></p><p>Please update your payment method to avoid service interruption.</p>',
                'recipients' => ['owner'],
                'attachments' => [],
            ],
            [
                'name' => 'Invoice paid',
                'event' => \Modules\Billing\Events\InvoicePaid::class,
                'subject' => 'Invoice {{ $invoice->number }} paid',
                'body' => '<p>Your invoice {{ $invoice->number }} has been paid.</p><p>Total: {{ $invoice->currency }} {{ number_format($invoice->total / 100, 2) }}</p>',
                'recipients' => ['owner'],
                'attachments' => [],
            ],
            [
                'name' => 'New terms version published',
                'event' => \App\Events\NewTermsVersionPublished::class,
                'subject' => 'New terms require your acceptance',
                'body' => '<p>We have updated our terms.</p><p>{{ $termsVersion->title }}</p><p><a href="{{ route("terms.accept") }}">Review and accept</a></p><p>You will need to accept the new version to continue using the application.</p>',
                'recipients' => ['user'],
                'attachments' => [],
            ],
        ];

        foreach ($templates as $data) {
            MailTemplate::query()->updateOrCreate(
                ['name' => $data['name'], 'event' => $data['event']],
                [
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'recipients' => $data['recipients'],
                    'attachments' => $data['attachments'],
                    'delay' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
