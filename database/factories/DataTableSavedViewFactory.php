<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DataTableSavedView;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataTableSavedView>
 */
final class DataTableSavedViewFactory extends Factory
{
    protected $model = DataTableSavedView::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_name' => fake()->slug(2),
            'name' => fake()->words(3, true),
            'filters' => null,
            'sort' => null,
            'columns' => null,
            'column_order' => null,
            'is_default' => false,
            'organization_id' => null,
            'is_shared' => false,
            'is_system' => false,
            'created_by' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }

    public function forTable(string $tableName): static
    {
        return $this->state(fn (): array => [
            'table_name' => $tableName,
        ]);
    }

    public function shared(Organization $org, User $creator): static
    {
        return $this->state(fn (): array => [
            'user_id' => $creator->id,
            'organization_id' => $org->id,
            'is_shared' => true,
            'is_system' => false,
            'created_by' => $creator->id,
        ]);
    }

    public function system(Organization $org, User $creator): static
    {
        return $this->state(fn (): array => [
            'user_id' => $creator->id,
            'organization_id' => $org->id,
            'is_shared' => false,
            'is_system' => true,
            'created_by' => $creator->id,
        ]);
    }
}
