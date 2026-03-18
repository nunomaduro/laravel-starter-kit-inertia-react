<?php

declare(strict_types=1);

namespace Modules\Reports\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;

final class ReportDataSourceRegistry
{
    /** @var array<string, callable(Organization, User|null, array<string, mixed>): (array<string, mixed>|Collection<int, mixed>)> */
    private array $sources = [];

    public function register(string $key, callable $callable): void
    {
        $this->sources[$key] = $callable;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>|Collection<int, mixed>
     */
    public function resolve(string $key, Organization $organization, ?User $user, array $config = []): array|Collection
    {
        if (! isset($this->sources[$key])) {
            return [];
        }

        return call_user_func($this->sources[$key], $organization, $user, $config);
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function options(): array
    {
        return array_map(
            fn (string $key): array => ['key' => $key, 'label' => ucwords(str_replace('_', ' ', $key))],
            array_keys($this->sources),
        );
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->sources);
    }
}
