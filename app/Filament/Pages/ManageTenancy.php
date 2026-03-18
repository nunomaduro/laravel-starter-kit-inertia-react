<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\TenancySettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageTenancy extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · App';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Tenancy';

    protected static ?int $navigationSort = 70;

    protected static string $settings = TenancySettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Core')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Multi-tenancy enabled'),
                        TextInput::make('domain')
                            ->label('Domain')
                            ->placeholder('e.g. app.example.com'),
                        Toggle::make('subdomain_resolution')
                            ->label('Subdomain resolution'),
                    ]),
                Section::make('Terminology')
                    ->schema([
                        TextInput::make('term')
                            ->label('Term')
                            ->required(),
                        TextInput::make('term_plural')
                            ->label('Term plural')
                            ->required(),
                    ]),
                Section::make('Organization Creation')
                    ->schema([
                        Toggle::make('allow_user_org_creation')
                            ->label('Allow user org creation'),
                        TextInput::make('default_org_name')
                            ->label('Default org name'),
                        Toggle::make('auto_create_personal_org_for_admins')
                            ->label('Auto-create personal workspace (for org admins)')
                            ->helperText('Users who register or are added as admins get a personal org.'),
                        Toggle::make('auto_create_personal_org_for_members')
                            ->label('Auto-create personal workspace (for org members)')
                            ->helperText('Users who join only as members (e.g. via invite) get a personal org.'),
                    ]),
                Section::make('Invitations')
                    ->schema([
                        TextInput::make('invitation_expires_in_days')
                            ->label('Invitation expires in days')
                            ->numeric(),
                        Toggle::make('invitation_allow_registration')
                            ->label('Invitation allow registration'),
                    ]),
                Section::make('Sharing')
                    ->schema([
                        Toggle::make('sharing_restrict_to_connected')
                            ->label('Sharing restrict to connected'),
                        Select::make('sharing_edit_ownership')
                            ->label('Sharing edit ownership')
                            ->options([
                                'original_owner' => 'Original Owner',
                                'copy_on_edit' => 'Copy on Edit',
                            ]),
                    ]),
                Section::make('Super Admin')
                    ->schema([
                        Toggle::make('super_admin_can_view_all')
                            ->label('Super admin can view all'),
                        Toggle::make('super_admin_default_share_new_to_all_orgs')
                            ->label('Default "Share to all organizations" on for shareable data')
                            ->helperText('When creating items in shareable (visibility) models, this option is checked by default for super-admins.'),
                    ]),
            ]);
    }
}
