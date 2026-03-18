<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\AiSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageAi extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · Integrations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'AI';

    protected static ?int $navigationSort = 60;

    protected static string $settings = AiSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('default_provider')
                    ->label('Default provider'),
                TextInput::make('default_for_images')
                    ->label('Default for images'),
                TextInput::make('default_for_audio')
                    ->label('Default for audio'),
                TextInput::make('default_for_transcription')
                    ->label('Default for transcription'),
                TextInput::make('default_for_embeddings')
                    ->label('Default for embeddings'),
                TextInput::make('default_for_reranking')
                    ->label('Default for reranking'),
                TextInput::make('chat_model')
                    ->label('Chat model'),
                TextInput::make('cohere_api_key')
                    ->label('Cohere API key')
                    ->password()
                    ->revealable(),
                TextInput::make('jina_api_key')
                    ->label('Jina API key')
                    ->password()
                    ->revealable(),
                TextInput::make('thesys_api_key')
                    ->label('Thesys API key (C1)')
                    ->helperText('Used for DataTable Visualize and other Thesys features. Optional — leave empty to rely on .env only.')
                    ->password()
                    ->revealable(),
                TextInput::make('thesys_model')
                    ->label('Thesys model')
                    ->placeholder('c1-nightly')
                    ->helperText('Default c1-nightly when empty; set to match your Thesys project.'),
            ]);
    }
}
