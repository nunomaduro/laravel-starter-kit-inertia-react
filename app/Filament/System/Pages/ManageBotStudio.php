<?php

declare(strict_types=1);

namespace App\Filament\System\Pages;

use App\Settings\BotStudioSettings;
use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageBotStudio extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · AI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Bot Studio';

    protected static ?int $navigationSort = 65;

    protected static string $settings = BotStudioSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Agent Limits')
                    ->description('Control how many agents organizations can create per plan tier.')
                    ->components([
                        TextInput::make('max_agents_basic')
                            ->label('Max agents (Basic plan)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('Maximum number of agents for Basic plan organizations.'),
                        TextInput::make('max_agents_pro')
                            ->label('Max agents (Pro plan)')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Set to 0 for unlimited.'),
                    ]),

                Section::make('Knowledge Base Limits')
                    ->description('File size and storage limits for agent knowledge bases.')
                    ->components([
                        TextInput::make('max_knowledge_file_size_mb')
                            ->label('Max file size (MB)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('Maximum size of a single knowledge file upload.'),
                        TextInput::make('max_knowledge_total_mb')
                            ->label('Max total storage (MB)')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('Maximum total knowledge storage per organization.'),
                    ]),

                Section::make('Model Configuration')
                    ->description('Default and allowed AI models for bot agents.')
                    ->components([
                        TextInput::make('default_model')
                            ->label('Default model')
                            ->required()
                            ->helperText('The model assigned to new agents by default.'),
                        TagsInput::make('allowed_models')
                            ->label('Allowed models')
                            ->required()
                            ->helperText('Models available for selection when creating or editing agents.'),
                    ]),
            ]);
    }
}
