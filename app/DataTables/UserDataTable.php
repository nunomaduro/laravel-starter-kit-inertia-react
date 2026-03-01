<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\User;
use App\Services\TenantContext;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\Column;
use Machour\DataTable\Concerns\HasExport;
use Machour\DataTable\QuickView;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserDataTable extends AbstractDataTable
{
    use HasExport;

    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $created_at,
    ) {}

    public static function fromModel(User $model): self
    {
        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            created_at: $model->created_at?->format('Y-m-d H:i'),
        );
    }

    public static function tableColumns(): array
    {
        return [
            new Column(id: 'id', label: 'ID', type: 'number', sortable: true, group: 'Identity'),
            new Column(id: 'name', label: 'Name', type: 'text', sortable: true, group: 'Identity'),
            new Column(id: 'email', label: 'Email', type: 'email', sortable: true, group: 'Identity'),
            new Column(id: 'created_at', label: 'Created at', type: 'date', sortable: true, filterable: true, group: 'Dates'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function tableSearchableColumns(): array
    {
        return ['name', 'email'];
    }

    public static function tableQuickViews(): array
    {
        return [
            new QuickView(
                id: 'all',
                label: 'All',
                params: [],
            ),
            new QuickView(
                id: 'recent',
                label: 'Created this year',
                params: ['filter[created_at]' => 'after:'.now()->startOfYear()->format('Y-m-d')],
            ),
            new QuickView(
                id: 'last-month',
                label: 'Created last month',
                params: ['filter[created_at]' => 'between:'.now()->subMonth()->startOfMonth()->format('Y-m-d').','.now()->subMonth()->endOfMonth()->format('Y-m-d')],
            ),
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, self>  $items
     * @return array<string, mixed>
     */
    public static function tableFooter(\Illuminate\Support\Collection $items): array
    {
        return [
            'id' => $items->count().' user'.($items->count() !== 1 ? 's' : '').' on this page',
        ];
    }

    public static function tableBaseQuery(): Builder
    {
        $user = request()->user();
        $query = User::query();

        if ($user?->can('bypass-permissions')) {
            return $query;
        }

        $organization = TenantContext::get();
        if (! $organization instanceof \App\Models\Organization) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('organizations', function (Builder $q) use ($organization): void {
            $q->where('organizations.id', $organization->id);
        });
    }

    public static function tableDefaultSort(): string
    {
        return '-id';
    }

    public static function tableExportEnabled(): bool
    {
        return true;
    }

    public static function tableExportName(): string
    {
        return 'users';
    }

    public static function tableExportFilename(): Closure
    {
        return fn (): string => 'users-'.now()->format('Y-m-d-His');
    }
}
