<?php

declare(strict_types=1);

namespace App\Enums;

enum VisibilityEnum: string
{
    case Global = 'global';
    case Organization = 'organization';
    case Shared = 'shared';

    public function label(): string
    {
        return match ($this) {
            self::Global => __('Global'),
            self::Organization => __((string) config('tenancy.term', 'Organization')),
            self::Shared => __('Shared'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Global => __('Visible to all :terms (read-only)', ['terms' => mb_strtolower(__((string) config('tenancy.term_plural', 'organizations')))]),
            self::Organization => __('Only visible to members of this :term', ['term' => mb_strtolower(__((string) config('tenancy.term', 'organization')))]),
            self::Shared => __('Visible to specific :terms and users', ['terms' => mb_strtolower(__((string) config('tenancy.term_plural', 'organizations')))]),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Global => 'heroicon-o-globe-alt',
            self::Organization => 'heroicon-o-building-office',
            self::Shared => 'heroicon-o-share',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Global => 'info',
            self::Organization => 'primary',
            self::Shared => 'warning',
        };
    }
}
