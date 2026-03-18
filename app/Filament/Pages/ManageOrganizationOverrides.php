<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Organization;
use App\Providers\SettingsOverlayServiceProvider;
use App\Services\OrganizationSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use UnitEnum;

final class ManageOrganizationOverrides extends Page implements HasTable
{
    use InteractsWithTable;

    public ?int $selectedOrganizationId = null;

    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $navigationLabel = 'Organization Overrides';

    protected string $view = 'filament.pages.manage-organization-overrides';

    protected static ?int $navigationSort = 120;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => DB::table('organization_settings')
                    ->join('organizations', 'organizations.id', '=', 'organization_settings.organization_id')
                    ->select([
                        'organization_settings.id',
                        'organizations.name as organization_name',
                        'organization_settings.group',
                        'organization_settings.name',
                        'organization_settings.is_encrypted',
                        'organization_settings.updated_at',
                    ])
                    ->toBase()
            )
            ->columns([
                TextColumn::make('organization_name')
                    ->label('Organization')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_encrypted')
                    ->boolean()
                    ->label('Encrypted'),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                DeleteAction::make()
                    ->action(function ($record): void {
                        DB::table('organization_settings')
                            ->where('id', $record->id)
                            ->delete();

                        Notification::make()
                            ->title('Override removed')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    protected function getHeaderActions(): array
    {
        $overridableKeys = SettingsOverlayServiceProvider::orgOverridableKeys();
        $options = [];

        foreach ($overridableKeys as $settingsKey => $configKey) {
            $options[$settingsKey] = sprintf('%s → %s', $settingsKey, $configKey);
        }

        return [
            Action::make('addOverride')
                ->label('Add Override')
                ->form([
                    Select::make('organization_id')
                        ->label('Organization')
                        ->options(Organization::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    Select::make('settings_key')
                        ->label('Setting')
                        ->options($options)
                        ->required()
                        ->searchable(),
                    TextInput::make('value')
                        ->label('Value (JSON-encoded)')
                        ->required()
                        ->helperText('Enter the value as JSON: "string", true, false, 123, ["a","b"]'),
                    Toggle::make('is_encrypted')
                        ->label('Encrypt value'),
                ])
                ->action(function (array $data): void {
                    $service = resolve(OrganizationSettingsService::class);
                    $org = Organization::query()->findOrFail($data['organization_id']);
                    [$group, $name] = explode('.', (string) $data['settings_key'], 2);

                    $value = json_decode((string) $data['value'], true, 512, JSON_THROW_ON_ERROR);

                    $service->setOverride($org, $group, $name, $value, $data['is_encrypted'] ?? false);

                    Notification::make()
                        ->title('Override saved')
                        ->success()
                        ->send();
                }),
        ];
    }
}
