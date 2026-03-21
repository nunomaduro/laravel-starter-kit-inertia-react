<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Services\PermissionCategoryResolver;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;

final class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $resolver = resolve(PermissionCategoryResolver::class);
        $grouped = $resolver->getPermissionsGroupedByCategory();
        $categories = config('permission_categories.categories', []);

        $components = [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->alphaDash(),
            Select::make('guard_name')
                ->options([
                    'web' => 'Web',
                    'api' => 'API',
                ])
                ->default('web')
                ->required(),
        ];

        foreach ($grouped as $categoryKey => $options) {
            $label = $categoryKey === 'other'
                ? __('Other')
                : ($categories[$categoryKey]['description'] ?? $categoryKey);
            $fieldName = 'permissions_'.$categoryKey;

            $components[] = Section::make($label)
                ->schema([
                    CheckboxList::make($fieldName)
                        ->options($options)
                        ->searchable()
                        ->bulkToggleable()
                        ->gridDirection('column')
                        ->columns(2),
                ])
                ->collapsible()
                ->persistCollapsedStatus()
                ->collapsed();
        }

        return $schema->components($components);
    }

    /**
     * Merge all permissions_* form fields into a single list of permission IDs.
     *
     * @param  array<string, mixed>  $data
     * @return array<int>
     */
    public static function mergePermissionIds(array $data): array
    {
        $merged = [];
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'permissions_') && is_array($data[$key] ?? null)) {
                $merged = array_merge($merged, Arr::flatten($data[$key]));
            }
        }

        return array_values(array_unique(array_filter($merged, is_numeric(...))));
    }
}
