<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Enums\UserStatusEnum;
use App\Events\User\UserUpdated;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasAi;
use Machour\DataTable\Concerns\HasAuditLog;
use Machour\DataTable\Concerns\HasExport;
use Machour\DataTable\Concerns\HasImport;
use Machour\DataTable\Concerns\HasInlineEdit;
use Machour\DataTable\Concerns\HasReorder;
use Machour\DataTable\Concerns\HasSelectAll;
use Machour\DataTable\Concerns\HasToggle;
use Machour\DataTable\Filters\OperatorFilter;
use Machour\DataTable\QuickView;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Throwable;

#[TypeScript]
final class UserDataTable extends AbstractDataTable
{
    use HasAi;
    use HasAuditLog;
    use HasExport;
    use HasImport;
    use HasInlineEdit;
    use HasReorder;
    use HasSelectAll;
    use HasToggle;

    protected static ?int $defaultPerPage = 25;

    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $hash_id,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $avatar,
        public ?string $color,
        public ?array $tags,
        public string $status,
        public bool $onboarding_completed,
        public int $organizations_count,
        public ?string $first_organization_name,
        public ?string $profile_url,
        public int $account_age_days,
        public int $profile_score,
        public string $plan_tier,
        public float $lifetime_value,
        public ?string $theme_mode,
        public ?int $position,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(User $model): self
    {
        $status = match (true) {
            $model->trashed() => UserStatusEnum::Deleted,
            $model->email_verified_at !== null => UserStatusEnum::Active,
            default => UserStatusEnum::Pending,
        };

        return new self(
            id: $model->id,
            hash_id: $model->hashId,
            name: $model->name,
            email: $model->email,
            phone: $model->phone,
            avatar: $model->avatar,
            color: $model->color,
            tags: $model->tags ?? [],
            status: $status->value,
            onboarding_completed: $model->onboarding_completed,
            organizations_count: $model->organizations_count ?? $model->organizations()->count(),
            first_organization_name: $model->relationLoaded('organizations')
                ? ($model->organizations->first()?->name ?? null)
                : null,
            profile_url: route('users.show', ['user' => $model->hashId]),
            account_age_days: $model->created_at ? (int) $model->created_at->diffInDays(now()) : 0,
            profile_score: self::computeProfileScore($model, $status),
            plan_tier: self::computePlanTier(self::computeProfileScore($model, $status)),
            lifetime_value: round(self::computeProfileScore($model, $status) * 9.99, 2),
            theme_mode: $model->theme_mode,
            position: $model->position,
            created_at: $model->created_at?->format('Y-m-d H:i'),
            updated_at: $model->updated_at?->format('Y-m-d H:i'),
        );
    }

    public static function tableColumns(): array
    {
        return [
            // ── Identity ──────────────────────────────────────────────────────────
            ColumnBuilder::make('_index', '#')
                ->rowIndex()
                ->visible(false)
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('id', 'ID')
                ->number()
                ->sortable()
                ->prefix('#')
                ->description('Primary key')
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('hash_id', 'Hash ID')
                ->text()
                ->visible(false)
                ->description('Public-safe hash identifier')
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('avatar', 'Avatar')
                ->image()
                ->visible(false)
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('name', 'Name')
                ->text()
                ->sortable()
                ->filterable()
                ->editable()
                ->lineClamp(2)
                ->avatar('avatar')
                ->headerFilter()
                ->autoHeight()
                ->description('Display name with avatar')
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('email', 'Email')
                ->email()
                ->sortable()
                ->filterable()
                ->editable()
                ->headerFilter()
                ->tooltip('Contact email')
                ->group('Identity')
                ->build(),
            // phone: phone type column — showcases phone()
            ColumnBuilder::make('phone', 'Phone')
                ->phone()
                ->editable()
                ->visible(false)
                ->description('Phone number (showcases phone() type)')
                ->group('Identity')
                ->build(),
            // profile_url: link type — shows the user's profile page URL (hidden by default)
            ColumnBuilder::make('profile_url', 'Profile link')
                ->link()
                ->visible(false)
                ->description('Direct link to user profile')
                ->group('Identity')
                ->build(),
            // user_profile: stacked name+email with avatar (composite display, hidden by default)
            ColumnBuilder::make('user_profile', 'Profile (stacked)')
                ->text()
                ->stacked(['name', 'email'])
                ->avatar('avatar')
                ->visible(false)
                ->description('Name + email stacked with avatar')
                ->group('Identity')
                ->build(),

            // ── Status ───────────────────────────────────────────────────────────
            ColumnBuilder::make('status', 'Status')
                ->badge([
                    ['label' => 'Active', 'value' => 'active', 'variant' => 'success'],
                    ['label' => 'Pending', 'value' => 'pending', 'variant' => 'warning'],
                    ['label' => 'Deleted', 'value' => 'deleted', 'variant' => 'danger'],
                ])
                ->filterable()
                ->description('Active, pending, or deleted (soft-delete)')
                ->group('Status')
                ->build(),
            // color: color type column — showcases color() swatch display
            ColumnBuilder::make('color', 'Color label')
                ->color()
                ->editable()
                ->visible(false)
                ->description('User color label — hex swatch (showcases color() type)')
                ->group('Identity')
                ->build(),
            // tags: multiOption type — showcases multi-value display
            ColumnBuilder::make('tags', 'Tags')
                ->multiOption(['admin', 'beta-tester', 'power-user', 'early-adopter', 'trial', 'paid', 'enterprise', 'vip', 'support', 'dev'])
                ->editable()
                ->visible(false)
                ->description('User tags — multi-value display (showcases multiOption())')
                ->group('Identity')
                ->build(),
            // status_icon: icon type using valueGetter from 'status' (hidden by default)
            ColumnBuilder::make('status_icon', 'Status icon')
                ->iconColumn([
                    'active' => 'check-circle',
                    'pending' => 'clock',
                    'deleted' => 'trash-2',
                ])
                ->valueGetter('status')
                ->visible(false)
                ->description('Icon representation of status')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('onboarding_completed', 'Onboarding done')
                ->boolean()
                ->toggleable()
                ->sortable()
                ->filterable()
                ->description('Has completed onboarding flow')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('organizations_count', 'Orgs')
                ->number()
                ->summary('sum')
                ->suffix(fn (mixed $v): string => $v === 1 ? ' org' : ' orgs')
                ->range(0, 50)
                ->description('Number of organizations')
                ->group('Status')
                ->build(),
            // profile_score: percentage type — showcases percentage column
            ColumnBuilder::make('profile_score', 'Profile score')
                ->percentage()
                ->sortable()
                ->description('Profile completeness: name (34%) + email verified (33%) + onboarding (33%)')
                ->visible(false)
                ->group('Status')
                ->build(),
            // account_label: computed column — derives value from status + onboarding_completed at query time
            ColumnBuilder::make('account_label', 'Account label')
                ->text()
                ->computed(['status', 'onboarding_completed'], function (array $row): string {
                    $s = $row['status'] ?? 'unknown';
                    $ob = (bool) ($row['onboarding_completed'] ?? false);

                    return match ($s) {
                        'active' => $ob ? 'Active & Onboarded' : 'Active (pending onboarding)',
                        'pending' => 'Pending verification',
                        'deleted' => 'Deleted account',
                        default => 'Unknown',
                    };
                })
                ->colorMap([
                    'Active & Onboarded' => 'text-emerald-600',
                    'Active (pending onboarding)' => 'text-blue-600',
                    'Pending verification' => 'text-amber-600',
                    'Deleted account' => 'text-red-600',
                ])
                ->visible(false)
                ->description('Computed label from status + onboarding (showcases computed() + colorMap())')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('first_organization_name', 'Primary org')
                ->text()
                ->filterable()
                ->description('First/primary organization name')
                ->group('Status')
                ->build(),
            // plan_tier: text + valueFormatter — showcases JS function expression valueFormatter
            ColumnBuilder::make('plan_tier', 'Plan tier')
                ->text()
                ->valueFormatter('(value) => "⭐ " + value.charAt(0).toUpperCase() + value.slice(1)')
                ->visible(false)
                ->description('Computed plan (free/pro/enterprise) — showcases valueFormatter()')
                ->group('Status')
                ->build(),
            // lifetime_value: currency type — showcases currency() + locale()
            ColumnBuilder::make('lifetime_value', 'Lifetime value')
                ->currency('USD')
                ->locale('en-US')
                ->sortable()
                ->summary('sum')
                ->visible(false)
                ->description('Synthetic USD value — showcases currency() type with locale')
                ->group('Status')
                ->build(),

            // ── Preferences ──────────────────────────────────────────────────────
            // theme_mode: select type — showcases inline editable dropdown (select())
            ColumnBuilder::make('theme_mode', 'Theme')
                ->select([
                    ['label' => 'Light', 'value' => 'light'],
                    ['label' => 'Dark', 'value' => 'dark'],
                    ['label' => 'System', 'value' => 'system'],
                ])
                ->visible(false)
                ->description('User theme preference — showcases select() inline editable dropdown')
                ->group('Preferences')
                ->build(),
            // theme_label: option type — showcases display-only label mapping (option())
            ColumnBuilder::make('theme_label', 'Theme (display)')
                ->option([
                    ['label' => '☀ Light mode', 'value' => 'light'],
                    ['label' => '🌙 Dark mode', 'value' => 'dark'],
                    ['label' => '🖥 System', 'value' => 'system'],
                ])
                ->valueGetter('theme_mode')
                ->visible(false)
                ->description('Display-only label mapping — showcases option() type')
                ->group('Preferences')
                ->build(),

            // ── Dates ────────────────────────────────────────────────────────────
            // position: number column for drag-to-reorder (HasReorder)
            ColumnBuilder::make('position', 'Order')
                ->number()
                ->sortable()
                ->visible(false)
                ->description('Drag-to-reorder position (showcases HasReorder)')
                ->group('Dates')
                ->build(),
            // account_age_days: number + sparkline('bar') — showcases suffix(Closure) + sparkline
            ColumnBuilder::make('account_age_days', 'Account age')
                ->number()
                ->sortable()
                ->suffix(fn (mixed $v): string => $v === 1 ? ' day' : ' days')
                ->sparkline('bar')
                ->description('Days since creation (showcases suffix Closure + sparkline bar)')
                ->visible(false)
                ->group('Dates')
                ->build(),
            ColumnBuilder::make('created_at', 'Created at')
                ->date()
                ->sortable()
                ->filterable()
                ->locale('en')
                ->description('First seen')
                ->group('Dates')
                ->build(),
            ColumnBuilder::make('updated_at', 'Updated at')
                ->date()
                ->sortable()
                ->filterable()
                ->locale('en')
                ->responsivePriority(2)
                ->group('Dates')
                ->build(),
        ];
    }

    /**
     * Full-dataset summary for showcase (total count, sum of orgs).
     *
     * @return array<string, mixed>
     */
    public static function tableSummary(QueryBuilder $query): array
    {
        $builder = $query->getEloquentBuilder();

        $userIds = (clone $builder)->select('id')->pluck('id');

        $organizationsSum = $userIds->isEmpty()
            ? 0
            : (int) DB::table('organization_user')->whereIn('user_id', $userIds)->count();

        return [
            'id' => $builder->count(),
            'organizations_count' => $organizationsSum,
        ];
    }

    /**
     * KPI cards above the table (showcase).
     *
     * @return array<int, array{label: string, value: int|float|string, format?: string, change?: float|null, icon?: string|null, description?: string|null}>
     */
    public static function tableAnalytics(): array
    {
        $base = self::tableBaseQuery();
        $total = $base->count();
        $active = (clone $base)->whereNotNull('users.email_verified_at')->count();
        $pending = (clone $base)->whereNull('users.email_verified_at')->count();
        $onboarded = (clone $base)->where('users.onboarding_completed', true)->count();

        return [
            [
                'label' => 'Total users',
                'value' => $total,
                'format' => 'number',
                'icon' => '👥',
                'description' => 'All visible users',
            ],
            [
                'label' => 'Active',
                'value' => $active,
                'format' => 'number',
                'change' => $total > 0 ? round(($active / $total) * 100 - 50, 1) : null,
                'icon' => '✓',
                'description' => 'Email verified',
            ],
            [
                'label' => 'Pending',
                'value' => $pending,
                'format' => 'number',
                'icon' => '○',
                'description' => 'Not yet verified',
            ],
            [
                'label' => 'Onboarding done',
                'value' => $onboarded,
                'format' => 'number',
                'description' => 'Completed onboarding',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tableSearchableColumns(): array
    {
        return ['name', 'email'];
    }

    /**
     * Enum-based filter options for status (show dropdown from BackedEnum).
     *
     * @return array<string, class-string<BackedEnum>>
     */
    public static function tableEnumFilters(): array
    {
        return ['status' => UserStatusEnum::class];
    }

    /**
     * Columns whose filter options are loaded asynchronously (e.g. large lists).
     *
     * @return array<string>
     */
    public static function tableAsyncFilterColumns(): array
    {
        return ['first_organization_name'];
    }

    /**
     * Resolve async filter options for a column (lazy-loaded for large datasets).
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function resolveAsyncFilterOptions(string $columnId, ?string $search = null): array
    {
        if ($columnId !== 'first_organization_name') {
            return [];
        }

        $query = Organization::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->limit(50);

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', '%'.addcslashes($search, '%_\\').'%');
        }

        return $query->pluck('name', 'name')
            ->map(fn (string $label, string $value): array => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    /**
     * Cascading filter map: child column → parent column.
     * When 'first_organization_name' is selected, 'name' options narrow to users in that org.
     *
     * @return array<string, string>
     */
    public static function tableCascadingFilters(): array
    {
        return ['name' => 'first_organization_name'];
    }

    /**
     * Resolve child filter options given the parent value.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function resolveCascadingFilterOptions(string $columnId, mixed $parentValue): array
    {
        if ($columnId !== 'name') {
            return [];
        }

        return self::tableBaseQuery()
            ->select('users.name')
            ->distinct()
            ->whereHas('organizations', function (Builder $q) use ($parentValue): void {
                $q->where('organizations.name', $parentValue);
            })
            ->orderBy('users.name')
            ->limit(100)
            ->pluck('name', 'name')
            ->map(fn (string $label, string $value): array => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('created_at', new OperatorFilter('date')),
            AllowedFilter::custom('updated_at', new OperatorFilter('date')),
            AllowedFilter::custom('onboarding_completed', new OperatorFilter('boolean')),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            AllowedFilter::callback('first_organization_name', function (Builder $query, mixed $value): void {
                if ($value !== null && $value !== '') {
                    $query->whereHas('organizations', function (Builder $q) use ($value): void {
                        $q->where('organizations.name', $value);
                    });
                }
            }),
            AllowedFilter::callback('trashed', function (Builder $query, mixed $value): void {
                if ($value === 'with') {
                    $query->withTrashed();
                } elseif ($value === 'only') {
                    $query->onlyTrashed();
                }
            }),
        ];
    }

    /** Never show trashed rows by default — must opt-in via filter. */
    public static function tableWithTrashedDefault(): bool
    {
        return false;
    }

    public static function tableQuickViews(): array
    {
        return [
            new QuickView(
                id: 'all',
                label: 'All',
                params: [],
                icon: 'users',
                columns: ['id', 'name', 'email', 'onboarding_completed', 'created_at'],
            ),
            new QuickView(
                id: 'recent',
                label: 'Created this year',
                params: ['filter[created_at]' => 'after:'.now()->startOfYear()->format('Y-m-d')],
                icon: 'calendar',
                columns: ['id', 'name', 'email', 'created_at'],
            ),
            new QuickView(
                id: 'last-month',
                label: 'Created last month',
                params: ['filter[created_at]' => 'between:'.now()->subMonth()->startOfMonth()->format('Y-m-d').','.now()->subMonth()->endOfMonth()->format('Y-m-d')],
                icon: 'calendar-days',
            ),
            new QuickView(
                id: 'onboarding-done',
                label: 'Onboarding complete',
                params: ['filter[onboarding_completed]' => 'eq:1'],
                icon: 'check-circle',
            ),
            new QuickView(
                id: 'with-trashed',
                label: 'With trashed',
                params: ['filter[trashed]' => 'with'],
                icon: 'archive',
                columns: ['id', 'name', 'email', 'status', 'created_at'],
            ),
            new QuickView(
                id: 'only-trashed',
                label: 'Only trashed',
                params: ['filter[trashed]' => 'only'],
                icon: 'trash-2',
                columns: ['id', 'name', 'email', 'status', 'created_at'],
            ),
        ];
    }

    /**
     * Single footer row: page count, created-at range (this page), and org-count sum.
     * Summary row is disabled so this is the only footer line.
     *
     * @param  Collection<int, self>  $items
     * @return array<string, mixed>
     */
    public static function tableFooter(Collection $items): array
    {
        $n = $items->count();
        $createdRange = $items->isEmpty()
            ? null
            : $items->min('created_at').' – '.$items->max('created_at');

        return [
            '_index' => null,
            'id' => $n.' user'.($n !== 1 ? 's' : '').' on this page',
            'hash_id' => null,
            'avatar' => null,
            'name' => null,
            'email' => null,
            'phone' => null,
            'color' => null,
            'tags' => null,
            'profile_url' => null,
            'user_profile' => null,
            'status' => null,
            'status_icon' => null,
            'onboarding_completed' => null,
            'organizations_count' => $items->sum('organizations_count'),
            'profile_score' => $items->isEmpty() ? null : (int) $items->avg('profile_score'),
            'account_label' => null,
            'first_organization_name' => null,
            'plan_tier' => null,
            'lifetime_value' => $items->isEmpty() ? null : round((float) $items->sum('lifetime_value'), 2),
            'theme_mode' => null,
            'theme_label' => null,
            'position' => null,
            'account_age_days' => $items->isEmpty() ? null : (int) $items->avg('account_age_days'),
            'created_at' => $createdRange,
            'updated_at' => null,
        ];
    }

    /**
     * @return array<int, array{column: string, operator: string, value: mixed, row?: array, cell?: array}>
     */
    public static function tableRules(): array
    {
        return [
            [
                'column' => 'onboarding_completed',
                'operator' => 'eq',
                'value' => true,
                'row' => ['class' => 'bg-emerald-50 dark:bg-emerald-950/30'],
            ],
            [
                'column' => 'onboarding_completed',
                'operator' => 'eq',
                'value' => false,
                'row' => ['class' => 'bg-amber-50 dark:bg-amber-950/20'],
            ],
        ];
    }

    public static function tableDetailRowEnabled(): bool
    {
        return true;
    }

    public static function tableDetailDisplay(): string
    {
        return 'drawer';
    }

    public static function tablePollingInterval(): int
    {
        return 60;
    }

    public static function tableSoftDeletesEnabled(): bool
    {
        return true;
    }

    public static function inertiaProps(Request $request): array
    {
        $data = self::makeTable($request)->toArray();
        $data['analytics'] = self::tableAnalytics();

        // Sparkline data: real per-user activity counts for the last 7 days
        // Format: Record<columnId, number[][]> — outer array = rows, inner array = 7-day values
        $rows = $data['data'] ?? [];
        $rowIds = array_column($rows, 'id');
        $activityCounts = [];
        if (! empty($rowIds)) {
            try {
                $days = collect(range(6, 0))->map(fn (int $d) => now()->subDays($d)->format('Y-m-d'));
                $raw = DB::table('activity_log')
                    ->selectRaw('causer_id, DATE(created_at) as day, COUNT(*) as cnt')
                    ->whereIn('causer_id', $rowIds)
                    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy('causer_id', 'day')
                    ->get()
                    ->groupBy('causer_id');
                foreach ($rowIds as $uid) {
                    $byDay = $raw->get($uid, collect())->keyBy('day');
                    $activityCounts[$uid] = $days->map(fn (string $d): int => (int) ($byDay->get($d)?->cnt ?? 0))->values()->all();
                }
            } catch (Throwable) {
                // activity_log unavailable — fall through to empty sparklines
            }
        }

        // Use row-ID keying (Record<rowId, number[]>) — stable across sort/pagination changes
        $sparklines = ['account_age_days' => []];
        foreach ($rows as $row) {
            $uid = $row['id'];
            $sparklines['account_age_days'][$uid] = $activityCounts[$uid] ?? array_fill(0, 7, 0);
        }

        $data['sparklineData'] = $sparklines;

        return [
            'tableData' => $data,
            'searchableColumns' => self::tableSearchableColumns(),
            // Channel names consumed by the React DataTable's realtimeChannel + presenceChannel props
            'realtimeChannel' => 'users',
            'presenceChannel' => 'presence-users',
        ];
    }

    /**
     * @return array{user: array{id: int, name: string, email: string, avatar: string|null, status: string, onboarding_completed: bool, email_verified_at: string|null, organizations: list<array{id: int, name: string}>, created_at: string|null}}
     */
    public static function showProps(User $user): array
    {
        $user->loadMissing('organizations:id,name');

        $status = match (true) {
            $user->trashed() => 'deleted',
            $user->email_verified_at !== null => 'active',
            default => 'pending',
        };

        return [
            'user' => [
                'id' => $user->id,
                'hash_id' => $user->hashId,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'status' => $status,
                'onboarding_completed' => $user->onboarding_completed,
                'email_verified_at' => $user->email_verified_at?->format('Y-m-d H:i'),
                'organizations' => $user->organizations->map(fn ($org): array => ['id' => $org->id, 'name' => $org->name])->values()->all(),
                'created_at' => $user->created_at?->toIso8601String(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function tableDetailRow(mixed $model): ?array
    {
        if (! $model instanceof User) {
            return null;
        }

        return [
            'email_verified_at' => $model->email_verified_at?->format('Y-m-d H:i'),
            'updated_at' => $model->updated_at?->format('Y-m-d H:i'),
            'organizations_count' => $model->organizations_count ?? $model->organizations()->count(),
        ];
    }

    public static function tableBaseQuery(): Builder
    {
        $user = request()->user();
        $query = User::query();

        $canSeeAll = $user?->hasRole('super-admin') || $user?->can('bypass-permissions');
        if (! $canSeeAll) {
            $organization = TenantContext::get();
            if (! $organization instanceof Organization) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas('organizations', function (Builder $q) use ($organization): void {
                $q->where('organizations.id', $organization->id);
            });
        }

        return $query->withCount('organizations')->with('organizations:id,name');
    }

    public static function tableDefaultSort(): string
    {
        return '-id';
    }

    /** Group rows by this column (e.g. onboarding status). Full usage example. */
    public static function tableGroupByColumn(): string
    {
        return 'onboarding_completed';
    }

    /** Authorize table actions (including AI). Showcase: allow any authenticated user. */
    public static function tableAuthorize(string $action, Request $request): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }
        if (str_starts_with($action, 'ai_')) {
            return true;
        }

        return true;
    }

    /** Domain context for AI (NLQ, insights, suggestions). Showcase. */
    public static function tableAiSystemContext(): string
    {
        return <<<'TEXT'
This table lists application users. Each row represents one user account.

Columns available:
- id (number): Primary key, prefixed with #
- hash_id (text): Public-safe hash identifier
- name (text, editable, filterable): Display name with avatar
- email (email, editable, filterable): Contact email address
- profile_url (link): Direct URL to user profile page
- user_profile (stacked): Name + email stacked with avatar (display only)
- status (badge): Account status — "active" (email verified), "pending" (not verified), "deleted" (soft-deleted)
- status_icon (icon): Icon representation of status
- onboarding_completed (boolean, toggleable): Whether user finished onboarding
- organizations_count (number): How many organizations this user belongs to
- profile_score (percentage 0-100): Profile completeness — 34% for name, 33% for email verified, 33% for onboarding
- account_label (computed text): Derived label combining status + onboarding ("Active & Onboarded", etc.)
- first_organization_name (text, filterable): Primary/first organization name
- account_age_days (number): Days since account was created
- created_at (date, filterable): When the account was created
- updated_at (date, filterable): When the account was last modified

Filters support: operator-based (date ranges: after:, before:, between:; boolean: eq:), enum (status dropdown), async options for first_organization_name, cascading (selecting an org narrows name filter), and partial text match. Soft-deleted users can be shown via the "With trashed" or "Only trashed" quick views.
TEXT;
    }

    /**
     * Server-side conditional action visibility.
     * Keys match action.id (stable), with fallback to action.label.
     *
     * @return array<string, array{column: string, operator: string, value: mixed}>
     */
    public static function tableActionRules(): array
    {
        return [
            'restore' => ['column' => 'status', 'operator' => 'eq', 'value' => 'deleted'],
            'force-delete' => ['column' => 'status', 'operator' => 'eq', 'value' => 'deleted'],
        ];
    }

    /**
     * Pin the super-admin user to the top of every page.
     *
     * @return array<int, self>
     */
    public static function tablePinnedTopRows(): array
    {
        try {
            $superAdmin = User::query()
                ->withCount('organizations')
                ->with('organizations:id,name')
                ->whereHas('roles', function (Builder $q): void {
                    $q->where('name', 'super-admin');
                })
                ->first();

            if ($superAdmin === null) {
                return [];
            }

            return [self::fromModel($superAdmin)];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Pin a synthetic "platform totals" row at the bottom.
     *
     * @return array<int, self>
     */
    public static function tablePinnedBottomRows(): array
    {
        try {
            $total = User::query()->count();
            $totalOrgs = DB::table('organization_user')->count();

            $row = new self(
                id: 0,
                hash_id: '',
                name: '— Platform totals —',
                email: '',
                phone: null,
                avatar: null,
                color: null,
                tags: [],
                status: 'active',
                onboarding_completed: false,
                organizations_count: $totalOrgs,
                first_organization_name: null,
                profile_url: null,
                account_age_days: 0,
                profile_score: 0,
                plan_tier: 'free',
                lifetime_value: 0.0,
                theme_mode: null,
                position: null,
                created_at: null,
                updated_at: null,
            );

            // Annotate the name with total count for display
            $row->name = "— {$total} users platform-wide —";

            return [$row];
        } catch (Throwable) {
            return [];
        }
    }

    /** Default layout: 'table', 'grid', 'cards', or 'kanban'. */
    public static function tableDefaultLayout(): string
    {
        return 'table';
    }

    /** Enable pivot mode (crosstab analysis). Showcases tablePivotEnabled + tablePivotConfig. */
    public static function tablePivotEnabled(): bool
    {
        return true;
    }

    /**
     * @return array{rowFields: string[], columnFields: string[], valueField: string, aggregation: string}
     */
    public static function tablePivotConfig(): array
    {
        return [
            'rowFields' => ['status'],
            'columnFields' => ['onboarding_completed'],
            'valueField' => 'id',
            'aggregation' => 'count',
        ];
    }

    /**
     * Additional sorts merged with auto-detected sortable columns.
     * Only declare non-obvious custom field mappings here.
     * Standard sortable columns (id, name, email, etc.) are auto-detected.
     *
     * @return array<int, AllowedSort|string>
     */
    public static function tableAdditionalSorts(): array
    {
        return [
            // account_age_days is computed from created_at — map to the real DB column
            AllowedSort::field('account_age_days', 'created_at'),
            // lifetime_value is computed from profile_score — map to the real DB column
            AllowedSort::field('lifetime_value', 'profile_score'),
        ];
    }

    // ─── HasExport ───────────────────────────────────────────

    public static function tableExportName(): string
    {
        return 'users';
    }

    // ─── HasInlineEdit ────────────────────────────────────────

    public static function tableInlineEditModel(): string
    {
        return User::class;
    }

    /**
     * @return array<string, mixed>
     */
    public static function tableInlineEditRules(string $columnId): array
    {
        return match ($columnId) {
            'name' => ['value' => 'required|string|max:255'],
            'email' => ['value' => 'required|email|max:255'],
            'theme_mode' => ['value' => 'required|in:light,dark,system'],
            default => ['value' => 'required'],
        };
    }

    public static function handleInlineEdit(Request $request, int|string $id): JsonResponse
    {
        $columnId = $request->input('column');
        $value = $request->input('value');
        $editableColumns = self::tableEditableColumns();
        if (! in_array($columnId, $editableColumns, true)) {
            return response()->json(['error' => 'Column is not editable.'], 422);
        }

        $request->validate(self::tableInlineEditRules($columnId));
        $modelClass = self::tableInlineEditModel();
        $model = $modelClass::query()->findOrFail($id);
        $oldValue = $model->{$columnId};
        $model->update([$columnId => $value]);
        self::logInlineEdit($model, $columnId, $oldValue, $model->{$columnId});

        // Broadcast to all connected clients via realtimeChannel (showcases ShouldBroadcast)
        UserUpdated::dispatch($model);

        return response()->json(['success' => true, 'value' => $model->{$columnId}]);
    }

    // ─── HasToggle ─────────────────────────────────────────────

    public static function tableToggleModel(): string
    {
        return User::class;
    }

    public static function tableToggleName(): string
    {
        return 'users';
    }

    /**
     * Return base toggle URL (frontend appends /{id}). Override because route() requires id.
     */
    public static function resolveToggleUrl(): string
    {
        $prefix = config('data-table.route_prefix', 'data-table');

        return url(sprintf('/%s/toggle/', $prefix).self::tableToggleName());
    }

    public static function handleToggle(Model $model, string $columnId, bool $value): void
    {
        $oldValue = $model->{$columnId};
        $model->update([$columnId => $value]);
        $model->refresh();
        self::logToggle($model, $columnId, $oldValue, $model->{$columnId});

        if ($model instanceof User) {
            UserUpdated::dispatch($model);
        }
    }

    // ─── HasReorder ─────────────────────────────────────────────

    public static function tableReorderModel(): string
    {
        return User::class;
    }

    public static function tableReorderName(): string
    {
        return 'users';
    }

    public static function tableReorderColumn(): string
    {
        return 'position';
    }

    // ─── HasSelectAll ──────────────────────────────────────────

    public static function tableSelectAllName(): string
    {
        return 'users';
    }

    // ─── HasImport ─────────────────────────────────────────────

    public static function tableImportName(): string
    {
        return 'users';
    }

    /**
     * @return array{created: int, updated: int, errors: array}
     */
    public static function processImport(string $filePath, string $extension): array
    {
        $created = 0;
        $updated = 0;
        $errors = [];

        $organization = TenantContext::get();
        if (! $organization instanceof Organization) {
            $errors[] = 'No organization context for import.';

            return ['created' => 0, 'updated' => 0, 'errors' => $errors];
        }

        try {
            if ($extension === 'csv') {
                $handle = fopen($filePath, 'r');
                if ($handle === false) {
                    $errors[] = 'Could not open file.';

                    return ['created' => 0, 'updated' => 0, 'errors' => $errors];
                }

                $header = fgetcsv($handle, escape: '\\');
                if ($header === false) {
                    fclose($handle);

                    return ['created' => 0, 'updated' => 0, 'errors' => $errors];
                }

                while (($row = fgetcsv($handle, escape: '\\')) !== false) {
                    $assoc = array_combine($header, $row);
                    if ($assoc === false) {
                        continue;
                    }

                    $email = $assoc['email'] ?? null;
                    if (! $email) {
                        $errors[] = 'Row missing email.';

                        continue;
                    }

                    $user = User::query()->where('email', $email)->first();
                    if ($user) {
                        $user->update(array_filter([
                            'name' => $assoc['name'] ?? $user->name,
                        ]));
                        $updated++;
                    } else {
                        $user = User::query()->create([
                            'name' => $assoc['name'] ?? 'Imported',
                            'email' => $email,
                            'password' => bcrypt(str()->random(32)),
                        ]);
                        $user->organizations()->attach($organization->id);
                        $created++;
                    }
                }

                fclose($handle);
            }
        } catch (Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    // ─── HasAuditLog ────────────────────────────────────────────

    public static function tableAuditLogName(): string
    {
        return 'users';
    }

    private static function computePlanTier(int $score): string
    {
        return match (true) {
            $score >= 100 => 'enterprise',
            $score >= 67 => 'pro',
            default => 'free',
        };
    }

    private static function computeProfileScore(User $model, UserStatusEnum $status): int
    {
        $score = 0;
        if (! empty($model->name)) {
            $score += 34;
        }

        if ($status === UserStatusEnum::Active) {
            $score += 33;
        }

        if ($model->onboarding_completed) {
            $score += 33;
        }

        return $score;
    }
}
