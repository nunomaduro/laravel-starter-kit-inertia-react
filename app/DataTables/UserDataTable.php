<?php

declare(strict_types=1);

namespace App\DataTables;

use App\Enums\UserStatusEnum;
use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Machour\DataTable\AbstractDataTable;
use Machour\DataTable\Columns\ColumnBuilder;
use Machour\DataTable\Concerns\HasAuditLog;
use Machour\DataTable\Concerns\HasExport;
use Machour\DataTable\Concerns\HasImport;
use Machour\DataTable\Concerns\HasInlineEdit;
use Machour\DataTable\Concerns\HasSelectAll;
use Machour\DataTable\Concerns\HasToggle;
use Machour\DataTable\Filters\OperatorFilter;
use Machour\DataTable\QuickView;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Throwable;

#[TypeScript]
final class UserDataTable extends AbstractDataTable
{
    use HasAuditLog;
    use HasExport;
    use HasImport;
    use HasInlineEdit;
    use HasSelectAll;
    use HasToggle;

    protected static ?int $defaultPerPage = 25;

    protected static ?int $maxPerPage = 100;

    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $avatar,
        public string $status,
        public bool $onboarding_completed,
        public int $organizations_count,
        public ?string $first_organization_name,
        public ?string $created_at,
        public ?string $updated_at,
    ) {}

    public static function fromModel(User $model): self
    {
        $status = match (true) {
            $model->trashed() => UserStatusEnum::Deleted,
            $model->onboarding_completed => UserStatusEnum::Active,
            default => UserStatusEnum::Pending,
        };

        return new self(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            avatar: $model->avatar,
            status: $status->value,
            onboarding_completed: $model->onboarding_completed,
            organizations_count: $model->organizations_count ?? $model->organizations()->count(),
            first_organization_name: $model->relationLoaded('organizations')
                ? ($model->organizations->first()?->name ?? null)
                : null,
            created_at: $model->created_at?->format('Y-m-d H:i'),
            updated_at: $model->updated_at?->format('Y-m-d H:i'),
        );
    }

    public static function tableColumns(): array
    {
        return [
            ColumnBuilder::make('_index', '#')
                ->rowIndex()
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('id', 'ID')
                ->number()
                ->sortable()
                ->prefix('#')
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('name', 'Name')
                ->text()
                ->sortable()
                ->filterable()
                ->editable()
                ->lineClamp(2)
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('email', 'Email')
                ->email()
                ->sortable()
                ->filterable()
                ->editable()
                ->tooltip('Contact email')
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('avatar', 'Avatar')
                ->image()
                ->visible(false)
                ->group('Identity')
                ->build(),
            ColumnBuilder::make('status', 'Status')
                ->badge([
                    ['label' => 'Active', 'value' => 'active', 'variant' => 'default'],
                    ['label' => 'Pending', 'value' => 'pending', 'variant' => 'secondary'],
                    ['label' => 'Deleted', 'value' => 'deleted', 'variant' => 'destructive'],
                ])
                ->filterable()
                ->description('Active, pending, or deleted (soft-delete)')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('onboarding_completed', 'Onboarding done')
                ->boolean()
                ->toggleable()
                ->sortable()
                ->filterable()
                ->group('Status')
                ->build(),
            ColumnBuilder::make('organizations_count', 'Orgs')
                ->number()
                ->suffix(' org(s)')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('first_organization_name', 'First org')
                ->text()
                ->description('First organization (relational demo)')
                ->group('Status')
                ->build(),
            ColumnBuilder::make('created_at', 'Created at')
                ->date()
                ->sortable()
                ->filterable()
                ->description('First seen')
                ->group('Dates')
                ->build(),
            ColumnBuilder::make('updated_at', 'Updated at')
                ->date()
                ->sortable()
                ->filterable()
                ->responsivePriority(2)
                ->group('Dates')
                ->build(),
        ];
    }

    /** No separate summary row; footer shows page count and created-at range. */
    public static function tableSummary(QueryBuilder $query): array
    {
        return [];
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
        return ['name'];
    }

    /**
     * Resolve async filter options for a column (e.g. name search).
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function resolveAsyncFilterOptions(string $columnId, ?string $search = null): array
    {
        if ($columnId !== 'name') {
            return [];
        }

        $query = self::tableBaseQuery()
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

    public static function tableAllowedFilters(): array
    {
        return [
            AllowedFilter::custom('created_at', new OperatorFilter('date')),
            AllowedFilter::custom('updated_at', new OperatorFilter('date')),
            AllowedFilter::custom('onboarding_completed', new OperatorFilter('boolean')),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
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
            'name' => null,
            'email' => null,
            'status' => null,
            'onboarding_completed' => null,
            'organizations_count' => $items->sum(fn (self $u): int => $u->organizations_count),
            'first_organization_name' => null,
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
                'row' => ['class' => 'bg-emerald-50/50 dark:bg-emerald-950/20'],
            ],
            [
                'column' => 'onboarding_completed',
                'operator' => 'eq',
                'value' => false,
                'row' => ['class' => 'bg-amber-50/30 dark:bg-amber-950/10'],
            ],
        ];
    }

    /**
     * Single source of truth for table feature flags and config.
     *
     * @return array{pollingInterval: int, deferLoading: bool, softDeletesEnabled: bool, detailDisplay: string, detailRowEnabled: bool}
     */
    public static function tableOptions(): array
    {
        return [
            'pollingInterval' => 30,
            'deferLoading' => true,
            'softDeletesEnabled' => true,
            'detailDisplay' => 'drawer',
            'detailRowEnabled' => true,
        ];
    }

    public static function tableDetailRowEnabled(): bool
    {
        return self::tableOptions()['detailRowEnabled'];
    }

    public static function tableDetailDisplay(): string
    {
        return self::tableOptions()['detailDisplay'];
    }

    public static function tablePollingInterval(): int
    {
        return self::tableOptions()['pollingInterval'];
    }

    public static function tableDeferLoading(): bool
    {
        return self::tableOptions()['deferLoading'];
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

    /** @return array{user: array{id: int, name: string, email: string, created_at: string|null}} */
    public static function showProps(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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

        if (! $user?->can('bypass-permissions')) {
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
    public static function tableGroupByColumn(): ?string
    {
        return 'onboarding_completed';
    }

    /** Authorize table actions. Full usage example. */
    public static function tableAuthorize(string $action, Request $request): bool
    {
        return $request->user() !== null;
    }

    /** Persist filters/sort to localStorage. Full usage example. */
    public static function tablePersistState(): bool
    {
        return true;
    }

    // ─── HasExport ───────────────────────────────────────────

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
        $model = $modelClass::findOrFail($id);
        $oldValue = $model->{$columnId};
        $model->update([$columnId => $value]);
        self::logInlineEdit($model, $columnId, $oldValue, $model->{$columnId});

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

        return url("/{$prefix}/toggle/".self::tableToggleName());
    }

    public static function handleToggle(Model $model, string $columnId, bool $value): void
    {
        $oldValue = $model->{$columnId};
        $model->update([$columnId => $value]);
        $model->refresh();
        self::logToggle($model, $columnId, $oldValue, $model->{$columnId});
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
            if (in_array($extension, ['csv'], true)) {
                $handle = fopen($filePath, 'r');
                if ($handle === false) {
                    $errors[] = 'Could not open file.';

                    return ['created' => 0, 'updated' => 0, 'errors' => $errors];
                }
                $header = fgetcsv($handle);
                if ($header === false) {
                    fclose($handle);

                    return ['created' => 0, 'updated' => 0, 'errors' => $errors];
                }
                while (($row = fgetcsv($handle)) !== false) {
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
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    // ─── HasAuditLog ────────────────────────────────────────────

    public static function tableAuditLogName(): string
    {
        return 'users';
    }
}
