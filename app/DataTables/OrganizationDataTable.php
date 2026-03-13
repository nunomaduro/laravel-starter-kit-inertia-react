<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\Organization;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasExport;
use Override;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class OrganizationDataTable extends AbstractDataTable
{
    use HasExport;

    #[Override]
    protected static ?int $defaultPerPage = 25;

    #[Override]
    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public ?string $owner_name,
        public ?string $created_at,
    ) {}

    public static function fromModel(Organization $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            slug: $model->slug,
            owner_name: $model->relationLoaded('owner') ? $model->owner?->name : null,
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
            ColumnBuilder::make('slug', 'Slug')
                ->text()
                ->sortable()
                ->filterable()
                ->build(),
            ColumnBuilder::make('owner_name', 'Owner')
                ->text()
                ->relation('owner')
                ->internalName('owner.name')
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

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::partial('name'),
            AllowedFilter::partial('slug'),
            AllowedFilter::callback('trashed', function (Builder $query, mixed $value): void {
                if ($value === 'with') {
                    $query->withTrashed();
                } elseif ($value === 'only') {
                    $query->onlyTrashed();
                }
            }),
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
            'id' => $n.' organization'.($n !== 1 ? 's' : '').' on this page',
            'name' => null,
            'slug' => null,
            'owner_name' => null,
            'created_at' => null,
        ];
    }

    /**
     * @return array{pollingInterval: int, deferLoading: bool, softDeletesEnabled: bool, detailDisplay: string, detailRowEnabled: bool}
     */
    public static function tableOptions(): array
    {
        return [
            'pollingInterval' => 0,
            'deferLoading' => false,
            'softDeletesEnabled' => true,
            'detailDisplay' => 'drawer',
            'detailRowEnabled' => false,
        ];
    }

    public static function tableDeferLoading(): bool
    {
        return self::tableOptions()['deferLoading'];
    }

    public static function tableDetailRowEnabled(): bool
    {
        return self::tableOptions()['detailRowEnabled'];
    }

    public static function tableSoftDeletesEnabled(): bool
    {
        return self::tableOptions()['softDeletesEnabled'];
    }

    /**
     * @return array{tableData: array|\Inertia\Deferred, searchableColumns: list<string>}
     */
    public static function inertiaProps(Request $request): array
    {
        $opts = self::tableOptions();
        $defer = $opts['deferLoading'] && ! app()->environment('testing');
        $make = function () use ($request, $opts): array {
            $data = self::makeTable($request)->toArray();
            $data['config'] = array_merge($data['config'] ?? [], $opts);

            return $data;
        };

        return [
            'tableData' => $defer ? Inertia::defer($make) : $make(),
            'searchableColumns' => self::tableSearchableColumns(),
        ];
    }

    public static function tableBaseQuery(): Builder
    {
        $user = request()->user();
        if ($user === null) {
            return Organization::query()->whereRaw('1 = 0');
        }

        $orgIds = $user->organizations()->pluck('organizations.id');

        return Organization::query()->whereIn('id', $orgIds);
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
        return 'organizations';
    }

    public static function tableExportFilename(): Closure
    {
        return fn (): string => 'organizations-'.now()->format('Y-m-d-His');
    }

    public static function tableExportEnabled(): bool
    {
        return true;
    }

    public static function tablePersistState(): bool
    {
        return true;
    }
}
