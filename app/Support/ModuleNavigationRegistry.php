<?php

declare(strict_types=1);

namespace App\Support;

final class ModuleNavigationRegistry
{
    /** @var array<string, array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>> */
    private static array $groups = [];

    /**
     * @param  array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>  $navItems
     */
    public static function registerGroup(string $moduleKey, array $navItems): void
    {
        self::$groups[$moduleKey] = $navItems;
    }

    /**
     * @return array<string, array<int, array{label: string, route: string, icon: string, group: string, permission?: string}>>
     */
    public static function allGroups(): array
    {
        return self::$groups;
    }

    /**
     * Get nav items grouped by their 'group' key across all modules.
     *
     * @return array<string, array<int, array{label: string, route: string, icon: string, module: string, permission?: string}>>
     */
    public static function groupedBySection(): array
    {
        $sections = [];

        foreach (self::$groups as $moduleKey => $items) {
            foreach ($items as $item) {
                $group = $item['group'] ?? $moduleKey;
                $item['module'] = $moduleKey;
                $sections[$group][] = $item;
            }
        }

        return $sections;
    }

    public static function flush(): void
    {
        self::$groups = [];
    }
}
