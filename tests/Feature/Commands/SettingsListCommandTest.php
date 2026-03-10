<?php

declare(strict_types=1);

use App\Settings\AppSettings;
use App\Settings\MailSettings;
use Illuminate\Support\Facades\Artisan;

it('exits successfully and shows settings', function (): void {
    $exitCode = Artisan::call('settings:list');

    expect($exitCode)->toBe(0);
});

it('filters by group', function (): void {
    $exitCode = Artisan::call('settings:list', ['--group' => 'mail']);

    expect($exitCode)->toBe(0);
});

it('shows a warning for an unknown group', function (): void {
    $exitCode = Artisan::call('settings:list', ['--group' => 'nonexistent-group-xyz']);

    expect($exitCode)->toBe(0);

    $output = Artisan::output();
    expect($output)->toContain('nonexistent-group-xyz');
});

it('masks encrypted values by default', function (): void {
    $mail = resolve(MailSettings::class);
    $mail->smtp_password = 'supersecret';
    $mail->save();

    Artisan::call('settings:list', ['--group' => 'mail']);
    $output = Artisan::output();

    expect($output)->not->toContain('supersecret')
        ->and($output)->toContain('[encrypted]');
});

it('reflects current settings values', function (): void {
    $app = resolve(AppSettings::class);
    $app->site_name = 'My Test App';
    $app->save();

    Artisan::call('settings:list', ['--group' => 'app']);
    $output = Artisan::output();

    expect($output)->toContain('My Test App');
});
