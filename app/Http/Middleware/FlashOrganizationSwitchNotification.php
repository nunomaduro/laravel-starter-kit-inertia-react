<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class FlashOrganizationSwitchNotification
{
    public function handle(Request $request, Closure $next): Response
    {
        $message = session('filament_org_switch_message');
        if (is_string($message) && $message !== '') {
            Notification::make()
                ->success()
                ->title($message)
                ->send();
            session()->forget('filament_org_switch_message');
        }

        return $next($request);
    }
}
