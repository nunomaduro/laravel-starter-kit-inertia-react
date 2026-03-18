<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Enums\AnnouncementLevel;
use App\Enums\AnnouncementScope;
use App\Models\Announcement;
use App\Models\Organization;
use App\Services\TenantContext;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasExport;
use Machour\DataTable\Concerns\HasReorder;
use Machour\DataTable\Concerns\HasToggle;
use Machour\DataTable\QuickView;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class AnnouncementDataTable extends AbstractDataTable
{
    use HasExport;
    use HasReorder;
    use HasToggle;

    protected static ?int $defaultPerPage = 25;

    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $title,
        public string $level,
        public string $scope,
        public bool $is_active,
        public ?string $starts_at,
        public ?string $ends_at,
        public ?string $organization_name,
        public ?string $creator_name,
        public ?string $created_at,
    ) {}

    public static function fromModel(Announcement $model): self
    {
        return new self(
            id: $model->id,
            title: $model->title,
            level: $model->level->value,
            scope: $model->scope->value,
            is_active: $model->is_active,
            starts_at: $model->starts_at?->format('Y-m-d H:i'),
            ends_at: $model->ends_at?->format('Y-m-d H:i'),
            organization_name: $model->relationLoaded('organization') ? $model->organization?->name : null,
            creator_name: $model->relationLoaded('creator') ? $model->creator?->name : null,
            created_at: $model->created_at?->format('Y-m-d H:i'),
        );
    }

    public static function tableColumns(): array
    {
        return [
            ColumnBuilder::make('_index', '#')
                ->rowIndex()
                ->visible(false)
                ->build(),
            ColumnBuilder::make('id', 'ID')
                ->number()
                ->sortable()
                ->prefix('#')
                ->build(),
            ColumnBuilder::make('title', 'Title')
                ->text()
                ->sortable()
                ->filterable()
                ->lineClamp(2)
                ->build(),
            ColumnBuilder::make('level', 'Level')
                ->badge([
                    ['label' => 'Info', 'value' => 'info', 'variant' => 'secondary'],
                    ['label' => 'Warning', 'value' => 'warning', 'variant' => 'warning'],
                    ['label' => 'Maintenance', 'value' => 'maintenance', 'variant' => 'danger'],
                ])
                ->filterable()
                ->build(),
            ColumnBuilder::make('scope', 'Scope')
                ->badge([
                    ['label' => 'Global', 'value' => 'global', 'variant' => 'secondary'],
                    ['label' => 'Organization', 'value' => 'organization', 'variant' => 'outline'],
                ])
                ->filterable()
                ->build(),
            ColumnBuilder::make('is_active', 'Active')
                ->boolean()
                ->toggleable()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('starts_at', 'Starts at')
                ->date()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('ends_at', 'Ends at')
                ->date()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('organization_name', 'Organization')
                ->text()
                ->relation('organization')
                ->internalName('organization.name')
                ->sortable()
                ->build(),
            ColumnBuilder::make('creator_name', 'Creator')
                ->text()
                ->relation('creator')
                ->internalName('creator.name')
                ->sortable()
                ->build(),
            ColumnBuilder::make('created_at', 'Created at')
                ->date()
                ->sortable()
                ->filterable()
                ->build(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tableSearchableColumns(): array
    {
        return ['title'];
    }

    /**
     * @return array<string, class-string<BackedEnum>>
     */
    public static function tableEnumFilters(): array
    {
        return [
            'level' => AnnouncementLevel::class,
            'scope' => AnnouncementScope::class,
        ];
    }

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('title'),
            AllowedFilter::exact('level'),
            AllowedFilter::exact('scope'),
            AllowedFilter::exact('is_active'),
            AllowedFilter::callback('starts_at', fn (Builder $q, $v) => $q->whereDate('starts_at', $v)),
            AllowedFilter::callback('ends_at', fn (Builder $q, $v) => $q->whereDate('ends_at', $v)),
            AllowedFilter::callback('organization_id', fn (Builder $q, $v) => $q->where('organization_id', $v)),
        ];
    }

    public static function tableQuickViews(): array
    {
        return [
            new QuickView(
                id: 'all',
                label: 'All',
                params: [],
                icon: 'list',
                columns: ['id', 'title', 'level', 'scope', 'is_active', 'created_at'],
            ),
            new QuickView(
                id: 'active',
                label: 'Active',
                params: ['filter[is_active]' => '1'],
                icon: 'check-circle',
                columns: ['id', 'title', 'level', 'scope', 'starts_at', 'ends_at', 'created_at'],
            ),
            new QuickView(
                id: 'info',
                label: 'Info level',
                params: ['filter[level]' => 'info'],
                icon: 'info',
                columns: ['id', 'title', 'scope', 'is_active', 'created_at'],
            ),
            new QuickView(
                id: 'warning',
                label: 'Warning level',
                params: ['filter[level]' => 'warning'],
                icon: 'alert-triangle',
                columns: ['id', 'title', 'scope', 'is_active', 'created_at'],
            ),
            new QuickView(
                id: 'global',
                label: 'Global',
                params: ['filter[scope]' => 'global'],
                icon: 'globe',
                columns: ['id', 'title', 'level', 'is_active', 'created_at'],
            ),
            new QuickView(
                id: 'org',
                label: 'Organization',
                params: ['filter[scope]' => 'organization'],
                icon: 'building-2',
                columns: ['id', 'title', 'level', 'organization_name', 'is_active', 'created_at'],
            ),
        ];
    }

    /**
     * @param  Collection<int, self>  $items
     * @return array<string, mixed>
     */
    public static function tableFooter(Collection $items): array
    {
        $n = $items->count();

        return [
            '_index' => null,
            'id' => $n.' announcement'.($n !== 1 ? 's' : '').' on this page',
            'title' => null,
            'level' => null,
            'scope' => null,
            'is_active' => null,
            'starts_at' => null,
            'ends_at' => null,
            'organization_name' => null,
            'creator_name' => null,
            'created_at' => null,
        ];
    }

    public static function inertiaProps(Request $request): array
    {
        return [
            'tableData' => self::makeTable($request)->toArray(),
            'searchableColumns' => self::tableSearchableColumns(),
        ];
    }

    public static function tableBaseQuery(): Builder
    {
        $user = auth()->user();
        $query = Announcement::query();

        if ($user?->can('announcements.manage_global')) {
            return $query;
        }

        $tenantId = TenantContext::id();
        if ($tenantId === null) {
            return $query->whereNull('organization_id');
        }

        return $query->where(function (Builder $q) use ($tenantId): void {
            $q->whereNull('organization_id')
                ->orWhere('organization_id', $tenantId);
        });
    }

    public static function tableDefaultSort(): string
    {
        return 'position';
    }

    public static function tableExportName(): string
    {
        return 'announcements';
    }

    public static function tableToggleModel(): string
    {
        return Announcement::class;
    }

    public static function tableReorderModel(): string
    {
        return Announcement::class;
    }

    public static function tableReorderName(): string
    {
        return 'announcements';
    }

    public static function tableReorderColumn(): string
    {
        return 'position';
    }

    public static function tableToggleName(): string
    {
        return 'announcements';
    }

    public static function resolveToggleUrl(): string
    {
        $prefix = config('data-table.route_prefix', 'data-table');

        return url(sprintf('/%s/toggle/', $prefix).self::tableToggleName());
    }

    public static function tableAuthorize(string $action, Request $request): bool
    {
        $user = $request->user();
        if ($user === null) {
            return false;
        }

        if ($user->can('announcements.manage_global')) {
            return true;
        }

        $orgId = TenantContext::id();
        if ($orgId === null) {
            return false;
        }

        $org = Organization::query()->find($orgId);

        return $org !== null && $user->canInOrganization('announcements.manage', $org);
    }
}
