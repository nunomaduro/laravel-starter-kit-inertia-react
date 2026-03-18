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
use UnitEnum;

final class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Email templates';

    protected static ?string $modelLabel = 'Email template';

    protected static ?string $pluralModelLabel = 'Email templates';

    protected static string|UnitEnum|null $navigationGroup = 'Content & Legal';

    protected static ?int $navigationSort = 10;

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
