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
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'AI';

    protected static string $settings = AiSettings::class;

    public static function getNavigationLabel(): string
    {
        return 'AI';
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
            ]);
    }
}
