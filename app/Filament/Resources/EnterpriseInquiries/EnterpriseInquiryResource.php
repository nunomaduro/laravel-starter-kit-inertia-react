<?php

declare(strict_types=1);

namespace App\Filament\Resources\EnterpriseInquiries;

use App\Filament\Resources\EnterpriseInquiries\Pages\EditEnterpriseInquiry;
use App\Filament\Resources\EnterpriseInquiries\Pages\ListEnterpriseInquiries;
use App\Filament\Resources\EnterpriseInquiries\Pages\ViewEnterpriseInquiry;
use App\Filament\Resources\EnterpriseInquiries\Schemas\EnterpriseInquiryForm;
use App\Filament\Resources\EnterpriseInquiries\Schemas\EnterpriseInquiryInfolist;
use App\Filament\Resources\EnterpriseInquiries\Tables\EnterpriseInquiriesTable;
use App\Models\EnterpriseInquiry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class EnterpriseInquiryResource extends Resource
{
    protected static ?string $model = EnterpriseInquiry::class;

    protected static string|UnitEnum|null $navigationGroup = 'Engagement';

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'email';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'admin';
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'company'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::getModel()::query()->where('status', 'new')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return EnterpriseInquiryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EnterpriseInquiryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnterpriseInquiriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnterpriseInquiries::route('/'),
            'view' => ViewEnterpriseInquiry::route('/{record}'),
            'edit' => EditEnterpriseInquiry::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
