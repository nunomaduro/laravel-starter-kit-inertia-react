<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Settings\InfrastructureSettings;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class ManageInfrastructure extends SettingsPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Settings · System';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServer;

    protected static ?string $navigationLabel = 'Infrastructure';

    protected static ?int $navigationSort = 15;

    protected static string $settings = InfrastructureSettings::class;

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Drivers')
                    ->description('Cache, session, and queue drivers. Changing these requires updating .env and restarting the server.')
                    ->schema([
                        Select::make('cache_driver')
                            ->label('Cache driver')
                            ->options([
                                'database' => 'Database (default, no extra setup)',
                                'redis' => 'Redis (recommended for production)',
                                'array' => 'Array (in-memory, testing only)',
                                'file' => 'File',
                            ])
                            ->required(),
                        Select::make('session_driver')
                            ->label('Session driver')
                            ->options([
                                'database' => 'Database',
                                'redis' => 'Redis',
                                'file' => 'File',
                                'cookie' => 'Cookie',
                            ])
                            ->required(),
                        Select::make('queue_connection')
                            ->label('Queue connection')
                            ->options([
                                'database' => 'Database',
                                'redis' => 'Redis (Horizon)',
                                'sync' => 'Sync (runs inline, no worker needed)',
                            ])
                            ->required(),
                    ]),
                Section::make('Redis')
                    ->description('Required when any driver above is set to Redis.')
                    ->schema([
                        TextInput::make('redis_host')
                            ->label('Host')
                            ->placeholder('127.0.0.1')
                            ->required(),
                        TextInput::make('redis_port')
                            ->label('Port')
                            ->numeric()
                            ->placeholder('6379')
                            ->required(),
                        TextInput::make('redis_password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->placeholder('Leave blank if none'),
                    ]),
            ]);
    }

    /**
     * After saving to the settings DB, also sync the values to .env so they
     * take effect on the next request cycle (the runtime config is updated
     * immediately by SettingsOverlayServiceProvider, but .env ensures the
     * values survive a server restart).
     */
    protected function afterSave(): void
    {
        /** @var InfrastructureSettings $settings */
        $settings = resolve(InfrastructureSettings::class);

        $envPath = base_path('.env');
        $env = file_exists($envPath) ? (string) file_get_contents($envPath) : '';

        $env = $this->setEnvVar($env, 'CACHE_STORE', $settings->cache_driver);
        $env = $this->setEnvVar($env, 'SESSION_DRIVER', $settings->session_driver);
        $env = $this->setEnvVar($env, 'QUEUE_CONNECTION', $settings->queue_connection);
        $env = $this->setEnvVar($env, 'REDIS_HOST', $settings->redis_host);
        $env = $this->setEnvVar($env, 'REDIS_PORT', (string) $settings->redis_port);

        if ($settings->redis_password !== null && $settings->redis_password !== '') {
            $env = $this->setEnvVar($env, 'REDIS_PASSWORD', $settings->redis_password);
        }

        file_put_contents($envPath, $env);

        Notification::make()
            ->title('Infrastructure settings saved')
            ->body('The .env file has been updated. Restart your queue workers (php artisan queue:restart) for the changes to take full effect.')
            ->warning()
            ->persistent()
            ->send();
    }

    private function setEnvVar(string $env, string $key, string $value): string
    {
        $escaped = preg_match('/\s/', $value) ? '"'.$value.'"' : $value;
        $line = sprintf('%s=%s', $key, $escaped);

        if (preg_match(sprintf('/^%s=.*/m', $key), $env)) {
            return (string) preg_replace(sprintf('/^%s=.*/m', $key), $line, $env);
        }

        return mb_rtrim($env).(PHP_EOL.$line.PHP_EOL);
    }
}
