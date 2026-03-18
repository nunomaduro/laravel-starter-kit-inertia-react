<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasExport;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class CategoryDataTable extends AbstractDataTable
{
    use HasExport;

    protected static ?int $defaultPerPage = 25;

    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $type,
        public ?int $parent_id,
        public ?string $parent_name,
        public ?string $created_at,
    ) {}

    public static function fromModel(Category $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            type: $model->type,
            parent_id: $model->parent_id,
            parent_name: $model->relationLoaded('parent') ? $model->parent?->name : null,
            created_at: $model->created_at?->format('Y-m-d H:i'),
        );
    }

    public static function tableColumns(): array
    {
        return [
            ColumnBuilder::make('id', 'ID')
                ->number()
                ->sortable()
                ->prefix('#')
                ->build(),
            ColumnBuilder::make('name', 'Name')
                ->text()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('type', 'Type')
                ->text()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('parent_name', 'Parent')
                ->text()
                ->relation('parent')
                ->internalName('parent.name')
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
        return ['name', 'slug'];
    }

    /**
     * Child column => parent column for cascading filter options.
     *
     * @return array<string, string>
     */
    public static function tableCascadingFilters(): array
    {
        return ['name' => 'type'];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function resolveCascadingFilterOptions(string $columnId, mixed $parentValue): array
    {
        if ($columnId !== 'name') {
            return [];
        }

        $query = self::tableBaseQuery()->select('id', 'name')->orderBy('name');

        if ($parentValue !== null && $parentValue !== '') {
            $query->where('type', $parentValue);
        }

        return $query->get()
            ->map(fn (Category $c): array => ['label' => $c->name, 'value' => $c->name])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function tableAsyncFilterColumns(): array
    {
        return ['type'];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public static function resolveAsyncFilterOptions(string $columnId, ?string $search = null): array
    {
        if ($columnId !== 'type') {
            return [];
        }

        $query = self::tableBaseQuery()
            ->select('type')
            ->distinct()
            ->orderBy('type');

        if ($search !== null && $search !== '') {
            $query->where('type', 'like', '%'.addcslashes($search, '%_\\').'%');
        }

        return $query->pluck('type', 'type')
            ->map(fn (string $label, string $value): array => ['label' => $label, 'value' => $value])
            ->values()
            ->all();
    }

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('type'),
            AllowedFilter::callback('parent_id', fn (Builder $q, $v) => $q->where('parent_id', $v)),
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
            'id' => $n.' categor'.($n !== 1 ? 'ies' : 'y').' on this page',
            'name' => null,
            'type' => null,
            'parent_name' => null,
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
        return Category::query();
    }

    public static function tableDefaultSort(): string
    {
        return 'name';
    }

    public static function tableAuthorize(string $action, Request $request): bool
    {
        return $request->user() !== null;
    }

    public static function tableExportName(): string
    {
        return 'categories';
    }
}
