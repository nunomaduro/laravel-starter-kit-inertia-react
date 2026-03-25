<?php

declare(strict_types=1);

namespace App\Filament\System\Pages;

use App\Models\MailTriggerSchedule;
use App\Services\TenantContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Pennant\Feature;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;
use UnitEnum;

final class ManageMailTriggers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Settings · Mail';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Mail Triggers';

    protected static ?int $navigationSort = 40;

    protected string $view = 'filament.system.pages.manage-mail-triggers';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => MailTriggerSchedule::query()
                    ->where('organization_id', TenantContext::id())
            )
            ->columns([
                TextColumn::make('event_class')
                    ->label('Event')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->placeholder('None'),
                TextColumn::make('delay_minutes')
                    ->label('Delay')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? "{$state} min" : 'Immediate')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('feature_flag')
                    ->label('Feature Flag')
                    ->placeholder('None'),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->triggerForm()),
                DeleteAction::make(),
            ])
            ->defaultSort('event_class');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addTrigger')
                ->label('Add Trigger')
                ->form($this->triggerForm())
                ->action(function (array $data): void {
                    MailTriggerSchedule::query()->create([
                        ...$data,
                        'organization_id' => TenantContext::id(),
                        'created_by' => auth()->id(),
                    ]);

                    Notification::make()
                        ->title('Mail trigger created')
                        ->success()
                        ->send();
                }),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function triggerForm(): array
    {
        $eventOptions = collect(config('database-mail.events', []))
            ->mapWithKeys(fn (string $class): array => [$class => class_basename($class)])
            ->all();

        $templateOptions = MailTemplate::query()
            ->pluck('name', 'id')
            ->all();

        $featureOptions = $this->getAvailableFeatureFlags();

        return [
            Select::make('event_class')
                ->label('Event')
                ->options($eventOptions)
                ->required()
                ->searchable(),
            Select::make('template_id')
                ->label('Mail Template')
                ->options($templateOptions)
                ->searchable()
                ->nullable(),
            TextInput::make('delay_minutes')
                ->label('Delay (minutes)')
                ->numeric()
                ->minValue(0)
                ->nullable()
                ->helperText('Leave empty for immediate delivery'),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
            Select::make('feature_flag')
                ->label('Feature Flag')
                ->options($featureOptions)
                ->searchable()
                ->nullable()
                ->helperText('Only trigger when this feature flag is active'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableFeatureFlags(): array
    {
        $features = Feature::defined();

        return collect($features)
            ->mapWithKeys(fn (string $feature): array => [$feature => $feature])
            ->all();
    }
}
