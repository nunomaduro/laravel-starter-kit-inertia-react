<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\PrismSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManagePrism extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Prism';

    protected static ?int $navigationSort = 50;

    protected static string $settings = PrismSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Server')
                    ->schema([
                        Toggle::make('prism_server_enabled')
                            ->label('Prism server enabled'),
                        TextInput::make('request_timeout')
                            ->label('Request timeout')
                            ->numeric(),
                    ]),
                Section::make('Defaults')
                    ->schema([
                        TextInput::make('default_provider')
                            ->label('Default provider'),
                        TextInput::make('default_model')
                            ->label('Default model'),
                    ]),
                Section::make('API Keys')
                    ->schema([
                        TextInput::make('openai_api_key')
                            ->label('OpenAI API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('anthropic_api_key')
                            ->label('Anthropic API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('groq_api_key')
                            ->label('Groq API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('xai_api_key')
                            ->label('xAI API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('gemini_api_key')
                            ->label('Gemini API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('deepseek_api_key')
                            ->label('DeepSeek API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('mistral_api_key')
                            ->label('Mistral API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('openrouter_api_key')
                            ->label('OpenRouter API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('elevenlabs_api_key')
                            ->label('ElevenLabs API key')
                            ->password()
                            ->revealable(),
                        TextInput::make('voyageai_api_key')
                            ->label('VoyageAI API key')
                            ->password()
                            ->revealable(),
                    ]),
            ]);
    }
}
