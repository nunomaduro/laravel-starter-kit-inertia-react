<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Override;

final class InstallNextStepsWidget extends Widget
{
    #[Override]
    protected static ?int $sort = -2;

    #[Override]
    protected array|string|int $columnSpan = 'full';

    #[Override]
    protected string $view = 'filament.widgets.install-next-steps';

    public static function canView(): bool
    {
        return session('show_next_steps', false) === true;
    }

    public function dismiss(): void
    {
        session()->forget('show_next_steps');
        $this->redirect(request()->url(), navigate: true);
    }
}
