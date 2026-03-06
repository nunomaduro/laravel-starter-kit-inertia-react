<?php

declare(strict_types=1);

namespace App\Filament\Resources\MailTemplates;

use App\Filament\Resources\MailTemplates\Pages\EditMailTemplate;
use App\Filament\Resources\MailTemplates\Pages\ListMailTemplates;
use App\Filament\Resources\MailTemplates\Schemas\MailTemplateForm;
use App\Filament\Resources\MailTemplates\Tables\MailTemplatesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;
use Override;
use UnitEnum;

final class MailTemplateResource extends Resource
{
    #[Override]
    protected static ?string $model = MailTemplate::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    #[Override]
    protected static ?string $navigationLabel = 'Email templates';

    #[Override]
    protected static ?string $modelLabel = 'Email template';

    #[Override]
    protected static ?string $pluralModelLabel = 'Email templates';

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Content & Legal';

    #[Override]
    protected static ?int $navigationSort = 10;

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'subject'];
    }

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system' && (auth()->user()?->hasRole('super-admin') ?? false);
    }

    public static function form(Schema $schema): Schema
    {
        return MailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MailTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMailTemplates::route('/'),
            'edit' => EditMailTemplate::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
