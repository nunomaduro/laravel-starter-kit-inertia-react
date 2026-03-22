<?php

declare(strict_types=1);

namespace Modules\PageBuilder\Services;

use App\Models\Billing\Invoice;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;

final class PageDataSourceRegistry
{
    /** @var array<string, callable(Organization, User|null, array): array|Collection> */
    private array $sources = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    public function register(string $key, callable $callable): void
    {
        $this->sources[$key] = $callable;
    }

    /**
     * @return array<string, mixed>|Collection<int, mixed>
     */
    public function resolve(string $key, Organization $organization, ?User $user, array $config = []): array|Collection
    {
        if (! isset($this->sources[$key])) {
            return [];
        }

        return ($this->sources[$key])($organization, $user, $config);
    }

    /**
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys($this->sources);
    }

    private function registerDefaults(): void
    {
        $this->register('members', function (Organization $organization, ?User $user): array {
            if (! $user instanceof User || ! $user->canInOrganization('org.members.view', $organization)) {
                return [];
            }

            return $organization->members()
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'email'])
                ->map(fn ($m): array => ['id' => $m->id, 'name' => $m->name, 'email' => $m->email])
                ->all();
        });

        $this->register('invoices', function (Organization $organization, ?User $user): array {
            if (! $user instanceof User || ! $user->canInOrganization('org.billing.view', $organization)) {
                return [];
            }

            return Invoice::query()
                ->where('organization_id', $organization->id)->latest()
                ->limit(20)
                ->get(['id', 'number', 'status', 'total', 'currency', 'paid_at', 'due_date'])
                ->map(fn (Invoice $i): array => [
                    'id' => $i->id,
                    'number' => $i->number,
                    'status' => $i->status,
                    'total' => $i->total,
                    'currency' => $i->currency,
                    'paid_at' => $i->paid_at?->toIso8601String(),
                    'due_date' => $i->due_date?->toIso8601String(),
                ])
                ->all();
        });
    }
}
