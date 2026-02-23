<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\ActivityType;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use STS\FilamentImpersonate\Events\EnterImpersonation;
use STS\FilamentImpersonate\Events\LeaveImpersonation;

final class LogImpersonationEvents
{
    public function handleEnterImpersonation(EnterImpersonation $event): void
    {
        $this->log(
            $event->impersonator,
            $event->impersonated,
            ActivityType::ImpersonationStarted->value,
            [
                'impersonator_name' => $this->userName($event->impersonator),
                'impersonator_id' => $event->impersonator->getAuthIdentifier(),
                'impersonated_name' => $this->userName($event->impersonated),
                'impersonated_id' => $event->impersonated->getAuthIdentifier(),
            ]
        );
    }

    public function handleLeaveImpersonation(LeaveImpersonation $event): void
    {
        if (! $event->impersonated instanceof Authenticatable) {
            return;
        }

        $this->log(
            $event->impersonator,
            $event->impersonated,
            ActivityType::ImpersonationEnded->value,
            [
                'impersonator_name' => $this->userName($event->impersonator),
                'impersonator_id' => $event->impersonator->getAuthIdentifier(),
                'impersonated_name' => $this->userName($event->impersonated),
                'impersonated_id' => $event->impersonated->getAuthIdentifier(),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function log(Authenticatable $impersonator, Authenticatable $impersonated, string $description, array $properties): void
    {
        if (! $impersonator instanceof Model || ! $impersonated instanceof Model) {
            return;
        }

        activity()
            ->causedBy($impersonator)
            ->performedOn($impersonated)
            ->withProperties($properties)
            ->log($description);
    }

    private function userName(Authenticatable $user): string
    {
        if ($user instanceof Model) {
            $name = $user->getAttribute('name');
            if ($name !== null && $name !== '') {
                return (string) $name;
            }
            $email = $user->getAttribute('email');
            if ($email !== null && $email !== '') {
                return (string) $email;
            }
        }

        return (string) $user->getAuthIdentifier();
    }
}
