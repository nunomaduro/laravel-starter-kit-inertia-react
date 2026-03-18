<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasExport;
use Machour\DataTable\Filters\OperatorFilter;
use Machour\DataTable\QuickView;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PostDataTable extends AbstractDataTable
{
    use HasExport;

    protected static ?int $defaultPerPage = 25;

    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $title,
        public bool $is_published,
        public ?string $published_at,
        public int $views,
        public ?string $author_name,
        public ?string $created_at,
    ) {}

    public static function fromModel(Post $model): self
    {
        return new self(
            id: $model->id,
            title: $model->title,
            is_published: $model->is_published,
            published_at: $model->published_at?->format('Y-m-d H:i'),
            views: $model->views,
            author_name: $model->relationLoaded('author') ? $model->author?->name : null,
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
            ColumnBuilder::make('title', 'Title')
                ->text()
                ->sortable()
                ->filterable()
                ->lineClamp(2)
                ->build(),
            ColumnBuilder::make('is_published', 'Status')
                ->badge([
                    ['label' => 'Draft', 'value' => '0', 'variant' => 'secondary'],
                    ['label' => 'Published', 'value' => '1', 'variant' => 'success'],
                ])
                ->filterable()
                ->build(),
            ColumnBuilder::make('published_at', 'Published at')
                ->date()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('views', 'Views')
                ->number()
                ->sortable()
                ->build(),
            ColumnBuilder::make('author_name', 'Author')
                ->text()
                ->relation('author')
                ->internalName('author.name')
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

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('title'),
            AllowedFilter::exact('is_published'),
            AllowedFilter::custom('published_at', new OperatorFilter('date')),
            AllowedFilter::callback('author_id', fn (Builder $q, $v) => $q->where('author_id', $v)),
            AllowedFilter::callback('trashed', function (Builder $query, mixed $value): void {
                if ($value === 'with') {
                    $query->withTrashed();
                } elseif ($value === 'only') {
                    $query->onlyTrashed();
                }
            }),
        ];
    }

    public static function tableQuickViews(): array
    {
        $thisMonthStart = now()->startOfMonth()->format('Y-m-d');
        $thisMonthEnd = now()->endOfMonth()->format('Y-m-d');

        return [
            new QuickView(
                id: 'all',
                label: 'All',
                params: [],
                icon: 'list',
                columns: ['id', 'title', 'is_published', 'published_at', 'author_name', 'created_at'],
            ),
            new QuickView(
                id: 'draft',
                label: 'Draft',
                params: ['filter[is_published]' => '0'],
                icon: 'edit',
                columns: ['id', 'title', 'author_name', 'created_at'],
            ),
            new QuickView(
                id: 'published',
                label: 'Published',
                params: ['filter[is_published]' => '1'],
                icon: 'check-circle',
                columns: ['id', 'title', 'published_at', 'views', 'author_name', 'created_at'],
            ),
            new QuickView(
                id: 'this-month',
                label: 'This month',
                params: ['filter[published_at]' => sprintf('between:%s,%s', $thisMonthStart, $thisMonthEnd)],
                icon: 'calendar',
                columns: ['id', 'title', 'is_published', 'published_at', 'views', 'created_at'],
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
            'id' => $n.' post'.($n !== 1 ? 's' : '').' on this page',
            'title' => null,
            'is_published' => null,
            'published_at' => null,
            'views' => $items->sum(fn (self $p): int => $p->views),
            'author_name' => null,
            'created_at' => null,
        ];
    }

    public static function tableSoftDeletesEnabled(): bool
    {
        return true;
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
        return Post::query();
    }

    public static function tableDefaultSort(): string
    {
        return '-created_at';
    }

    public static function tableAuthorize(string $action, Request $request): bool
    {
        return $request->user() !== null;
    }

    public static function tableExportName(): string
    {
        return 'posts';
    }
}
