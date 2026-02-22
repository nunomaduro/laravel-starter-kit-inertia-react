<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\Column;
use Machour\DataTable\QuickView;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserDataTable extends AbstractDataTable
{
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
            new Column(id: 'id', label: 'ID', type: 'number', sortable: true),
            new Column(id: 'name', label: 'Name', type: 'text', sortable: true),
            new Column(id: 'email', label: 'Email', type: 'text', sortable: true),
            new Column(id: 'created_at', label: 'Created at', type: 'date', sortable: true, filterable: true),
        ];
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
        ];
    }

    public static function tableBaseQuery(): Builder
    {
        return User::query();
    }

    public static function tableDefaultSort(): string
    {
        return '-id';
    }
}
