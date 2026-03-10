<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('infrastructure.cache_driver', config('cache.default', 'database'));
        $this->migrator->add('infrastructure.session_driver', config('session.driver', 'database'));
        $this->migrator->add('infrastructure.queue_connection', config('queue.default', 'database'));
        $this->migrator->add('infrastructure.redis_host', config('database.redis.default.host', '127.0.0.1'));
        $this->migrator->add('infrastructure.redis_port', (int) config('database.redis.default.port', 6379));
        $this->migrator->addEncrypted('infrastructure.redis_password', config('database.redis.default.password'));
    }
};
