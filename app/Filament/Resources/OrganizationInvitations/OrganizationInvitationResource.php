<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizationInvitations;

use App\Filament\Concerns\ScopesToCurrentTenant;
use App\Filament\Resources\OrganizationInvitations\Pages\CreateOrganizationInvitation;
use App\Filament\Resources\OrganizationInvitations\Pages\EditOrganizationInvitation;
use App\Filament\Resources\OrganizationInvitations\Pages\ListOrganizationInvitations;
use App\Filament\Resources\OrganizationInvitations\Schemas\OrganizationInvitationForm;
use App\Filament\Resources\OrganizationInvitations\Tables\OrganizationInvitationsTable;
use App\Models\OrganizationInvitation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class OrganizationInvitationResource extends Resource
{
    use ScopesToCurrentTenant;

    protected static ?string $model = OrganizationInvitation::class;

    protected static string|UnitEnum|null $navigationGroup = 'Organizations';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?string $recordTitleAttribute = 'email';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'admin';
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['email'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::getModel()::query()->where('status', OrganizationInvitation::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizationInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrganizationInvitationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizationInvitations::route('/'),
            'create' => CreateOrganizationInvitation::route('/create'),
            'edit' => EditOrganizationInvitation::route('/{record}/edit'),
        ];
    }
}
