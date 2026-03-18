<?php

declare(strict_types=1);

namespace App\Filament\Resources\Billing\Affiliates;

use App\Filament\Resources\Billing\Affiliates\Pages\ManageAffiliates;
use App\Filament\Resources\Billing\Affiliates\Tables\AffiliatesTable;
use App\Models\Billing\Affiliate;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

final class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'affiliate_code';

    protected static string|UnitEnum|null $navigationGroup = 'Billing';

    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    /** @return array<string> */
    public static function getGloballySearchableAttributes(): array
    {
        return ['affiliate_code'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('affiliate_code')
                            ->disabled(),
                        Select::make('status')
                            ->options([
                                Affiliate::STATUS_PENDING => 'Pending',
                                Affiliate::STATUS_ACTIVE => 'Active',
                                Affiliate::STATUS_SUSPENDED => 'Suspended',
                                Affiliate::STATUS_REJECTED => 'Rejected',
                            ])
                            ->required(),
                        TextInput::make('commission_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(20),
                        TextInput::make('payment_email')
                            ->email(),
                        Select::make('payment_method')
                            ->options([
                                'paypal' => 'PayPal',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->default('paypal'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return AffiliatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAffiliates::route('/'),
        ];
    }
}
