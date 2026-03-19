<?php

declare(strict_types=1);

namespace App\Filament\System\Resources\Organizations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->maxLength(255)
                    ->visibleOn('edit'),
                Select::make('owner_id')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
