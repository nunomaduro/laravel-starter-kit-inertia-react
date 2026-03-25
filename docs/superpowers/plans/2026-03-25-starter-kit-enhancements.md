# Starter Kit Enhancements Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build 5 generic SaaS infrastructure features (shared DataTable views, PDF pipeline, email trigger scheduling, RAG pipeline, AI agent service + global chat) in the starter kit so FusionCRM v4 and all future modules can extend them.

**Architecture:** Two parallel tracks — Track 1 (Quick Wins: tasks 1–3, ~3–4 days) and Track 2 (AI Stack: tasks 4–8, ~7–10 days). Track 1 features are independent. Track 2 features build on each other: RAG → ModuleToolRegistry → OrgScopedAgent → Context Injection → Chat UI.

**Tech Stack:** Laravel 13, Inertia.js v2 + React 19, Tailwind CSS v4, Pest 4, Filament v5, Laravel AI SDK, pgvector, Spatie Media Library, thomasjohnkane/snooze, martinpetricko/laravel-database-mail

**Spec:** `docs/superpowers/specs/2026-03-25-starter-kit-enhancements-design.md`

---

## File Structure

### Track 1 — Quick Wins

```
# Task 1: Shared DataTable Views
database/migrations/YYYY_MM_DD_HHMMSS_add_sharing_columns_to_data_table_saved_views_table.php  (create)
app/Models/DataTableSavedView.php                                                               (create)
app/Http/Controllers/Api/DataTableSavedViewController.php                                       (create)
app/Http/Requests/Api/StoreDataTableSavedViewRequest.php                                        (create)
app/Http/Requests/Api/UpdateDataTableSavedViewRequest.php                                       (create)
routes/api.php                                                                                  (modify)
resources/js/components/data-table/data-table-quick-views.tsx                                   (modify)
tests/Feature/DataTableSavedViewTest.php                                                        (create)

# Task 2: PDF Generation Pipeline
app/Actions/GeneratePdf.php                                                                     (create)
app/Jobs/GeneratePdfJob.php                                                                     (create)
tests/Feature/GeneratePdfTest.php                                                               (create)

# Task 3: Scheduled Email Triggers
database/migrations/YYYY_MM_DD_HHMMSS_create_mail_trigger_schedules_table.php                   (create)
app/Models/MailTriggerSchedule.php                                                              (create)
app/Services/ScheduledMailDispatcher.php                                                        (create)
app/Filament/System/Pages/ManageMailTriggers.php                                                (create)
app/Providers/EventServiceProvider.php                                                          (modify — if needed)
tests/Feature/ScheduledMailDispatcherTest.php                                                   (create)
```

### Track 2 — AI Stack

```
# Task 4: RAG Pipeline
config/ai.php                                                                                   (modify)
database/migrations/YYYY_MM_DD_HHMMSS_create_model_embeddings_table.php                         (create)
app/Models/ModelEmbedding.php                                                                   (create)
app/Models/Concerns/HasEmbeddings.php                                                           (create)
app/Jobs/GenerateEmbeddingJob.php                                                               (create)
app/Services/SemanticSearchService.php                                                          (create)
app/Console/Commands/RefreshEmbeddingsCommand.php                                               (create)
tests/Feature/HasEmbeddingsTest.php                                                             (create)
tests/Feature/SemanticSearchServiceTest.php                                                     (create)

# Task 5: ModuleToolRegistry
app/Support/ModuleToolRegistry.php                                                              (create)
app/Ai/Contracts/ModuleAiTool.php                                                               (create)
app/Ai/Tools/SemanticSearchTool.php                                                             (create)
app/Modules/Support/ModuleProvider.php                                                          (modify)
app/Modules/Contracts/ProvidesAITools.php                                                       (create)
tests/Feature/ModuleToolRegistryTest.php                                                        (create)

# Task 6: OrgScopedAgent
app/Ai/OrgScopedAgent.php                                                                      (create)
app/Ai/Contracts/ContextAwareTool.php                                                           (create)
tests/Feature/OrgScopedAgentTest.php                                                            (create)

# Task 7: Context Injection
resources/js/hooks/use-agent-context.ts                                                         (create)
app/Http/Controllers/Api/ChatController.php                                                     (modify)
app/Http/Requests/Api/StoreChatMessageRequest.php                                               (modify)
tests/Feature/ChatContextInjectionTest.php                                                      (create)

# Task 8: Chat UI Rework
resources/js/components/global-chat/global-chat-widget.tsx                                      (create)
resources/js/components/global-chat/chat-slide-over.tsx                                         (create)
resources/js/components/global-chat/conversation-list.tsx                                       (create)
resources/js/components/global-chat/chat-panel.tsx                                              (create)
resources/js/components/global-chat/chat-input.tsx                                              (create)
resources/js/components/global-chat/voice-input.tsx                                             (create)
resources/js/components/global-chat/voice-output.tsx                                            (create)
resources/js/components/global-chat/file-upload.tsx                                             (create)
resources/js/components/chat/renderers/renderer-registry.tsx                                    (create)
resources/js/components/chat/renderers/table-renderer.tsx                                       (create)
resources/js/components/chat/renderers/card-renderer.tsx                                        (create)
resources/js/components/chat/renderers/chart-renderer.tsx                                       (create)
resources/js/components/chat/renderers/action-renderer.tsx                                      (create)
resources/js/components/chat/renderers/text-renderer.tsx                                        (create)
resources/js/layouts/app-layout.tsx                                                             (modify)
resources/js/pages/chat/index.tsx                                                               (modify)
```

---

## Track 1 — Quick Wins

### Task 1: Shared DataTable Views

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_sharing_columns_to_data_table_saved_views_table.php`
- Create: `app/Models/DataTableSavedView.php`
- Create: `app/Http/Controllers/Api/DataTableSavedViewController.php`
- Create: `app/Http/Requests/Api/StoreDataTableSavedViewRequest.php`
- Create: `app/Http/Requests/Api/UpdateDataTableSavedViewRequest.php`
- Modify: `routes/api.php`
- Modify: `resources/js/components/data-table/data-table-quick-views.tsx`
- Create: `tests/Feature/DataTableSavedViewTest.php`

**Context:**
- The `data_table_saved_views` table exists (see `database/migrations/create_data_table_saved_views_table.php`) with columns: `id`, `user_id`, `table_name`, `name`, `filters`, `sort`, `columns`, `column_order`, `is_default`.
- The React component `resources/js/components/data-table/data-table-quick-views.tsx` currently stores custom views in **localStorage** (key `dt-quickviews-{tableName}`). We are moving to server-side storage with sharing.
- No `DataTableSavedView` model exists yet — only the migration.
- Follow the Action pattern for business logic. Use `TenantContext` for org scoping.
- @skill: `pest-testing` for test patterns; `inertia-react-development` for React changes; `tailwindcss-development` for styling.

- [ ] **Step 1: Write tests for the saved view API**

Create `tests/Feature/DataTableSavedViewTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\DataTableSavedView;
use App\Models\Organization;
use App\Models\User;

beforeEach(function (): void {
    $this->org = Organization::factory()->create();
    $this->user = createTestUser();
    $this->actingAs($this->user);
    config()->set('tenancy.current_organization_id', $this->org->id);
});

it('returns grouped saved views for a table', function (): void {
    // Private view
    DataTableSavedView::factory()->create([
        'user_id' => $this->user->id,
        'table_name' => 'contacts',
        'name' => 'My View',
        'is_shared' => false,
    ]);

    // Shared team view
    DataTableSavedView::factory()->create([
        'organization_id' => $this->org->id,
        'table_name' => 'contacts',
        'name' => 'Team View',
        'is_shared' => true,
        'is_system' => false,
        'created_by' => $this->user->id,
    ]);

    // System view
    DataTableSavedView::factory()->create([
        'organization_id' => $this->org->id,
        'table_name' => 'contacts',
        'name' => 'System View',
        'is_shared' => true,
        'is_system' => true,
        'created_by' => $this->user->id,
    ]);

    $response = $this->getJson('/api/data-table-saved-views?table_name=contacts');

    $response->assertOk()
        ->assertJsonStructure([
            'my_views',
            'team_views',
            'system_views',
        ])
        ->assertJsonCount(1, 'my_views')
        ->assertJsonCount(1, 'team_views')
        ->assertJsonCount(1, 'system_views');
});

it('creates a private saved view', function (): void {
    $response = $this->postJson('/api/data-table-saved-views', [
        'table_name' => 'contacts',
        'name' => 'My Custom View',
        'filters' => ['status' => 'active'],
        'sort' => 'name',
        'columns' => ['name', 'email'],
        'is_shared' => false,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('data_table_saved_views', [
        'name' => 'My Custom View',
        'user_id' => $this->user->id,
        'is_shared' => false,
        'organization_id' => null,
    ]);
});

it('creates a shared team view with organization_id', function (): void {
    $response = $this->postJson('/api/data-table-saved-views', [
        'table_name' => 'contacts',
        'name' => 'Shared View',
        'filters' => [],
        'is_shared' => true,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('data_table_saved_views', [
        'name' => 'Shared View',
        'is_shared' => true,
        'organization_id' => $this->org->id,
        'created_by' => $this->user->id,
    ]);
});

it('only allows admins to create system views', function (): void {
    $response = $this->postJson('/api/data-table-saved-views', [
        'table_name' => 'contacts',
        'name' => 'System Default',
        'is_shared' => true,
        'is_system' => true,
    ]);

    $response->assertForbidden();
});

it('deletes own private view', function (): void {
    $view = DataTableSavedView::factory()->create([
        'user_id' => $this->user->id,
        'table_name' => 'contacts',
        'name' => 'To Delete',
    ]);

    $this->deleteJson("/api/data-table-saved-views/{$view->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('data_table_saved_views', ['id' => $view->id]);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=DataTableSavedViewTest`
Expected: FAIL — model, controller, routes don't exist yet.

- [ ] **Step 3: Create the migration**

Run: `php artisan make:migration add_sharing_columns_to_data_table_saved_views_table --no-interaction`

Then edit the migration:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_table_saved_views', function (Blueprint $table): void {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_shared')->default(false)->after('is_default');
            $table->boolean('is_system')->default(false)->after('is_shared');
            $table->foreignId('created_by')->nullable()->after('is_system')->constrained('users')->nullOnDelete();

            $table->index(['organization_id', 'is_shared']);
        });
    }

    public function down(): void
    {
        Schema::table('data_table_saved_views', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['organization_id', 'is_shared']);
            $table->dropColumn(['organization_id', 'is_shared', 'is_system', 'created_by']);
        });
    }
};
```

- [ ] **Step 4: Create the model**

Run: `php artisan make:class "Models/DataTableSavedView" --no-interaction` — then replace contents:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DataTableSavedView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'table_name',
        'name',
        'filters',
        'sort',
        'columns',
        'column_order',
        'is_default',
        'is_shared',
        'is_system',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'columns' => 'array',
            'column_order' => 'array',
            'is_default' => 'boolean',
            'is_shared' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @param Builder<self> $query */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)->where('is_shared', false);
    }

    /** @param Builder<self> $query */
    public function scopeSharedInOrg(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId)
            ->where('is_shared', true)
            ->where('is_system', false);
    }

    /** @param Builder<self> $query */
    public function scopeSystemInOrg(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId)
            ->where('is_system', true);
    }

    /**
     * Return views grouped by scope for the current user + org.
     *
     * @return array{my_views: \Illuminate\Database\Eloquent\Collection<int, self>, team_views: \Illuminate\Database\Eloquent\Collection<int, self>, system_views: \Illuminate\Database\Eloquent\Collection<int, self>}
     */
    public static function grouped(string $tableName, int $userId, int $organizationId): array
    {
        return [
            'my_views' => self::query()->forUser($userId)->where('table_name', $tableName)->get(),
            'team_views' => self::query()->sharedInOrg($organizationId)->where('table_name', $tableName)->get(),
            'system_views' => self::query()->systemInOrg($organizationId)->where('table_name', $tableName)->get(),
        ];
    }
}
```

- [ ] **Step 5: Create the factory**

Create `database/factories/DataTableSavedViewFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DataTableSavedView;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DataTableSavedView> */
final class DataTableSavedViewFactory extends Factory
{
    protected $model = DataTableSavedView::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'table_name' => fake()->randomElement(['contacts', 'deals', 'users']),
            'name' => fake()->words(3, true),
            'filters' => [],
            'sort' => null,
            'columns' => null,
            'column_order' => null,
            'is_default' => false,
            'is_shared' => false,
            'is_system' => false,
        ];
    }

    public function shared(int $organizationId, int $createdBy): static
    {
        return $this->state([
            'organization_id' => $organizationId,
            'is_shared' => true,
            'created_by' => $createdBy,
        ]);
    }

    public function system(int $organizationId, int $createdBy): static
    {
        return $this->state([
            'organization_id' => $organizationId,
            'is_shared' => true,
            'is_system' => true,
            'created_by' => $createdBy,
        ]);
    }
}
```

- [ ] **Step 6: Create form requests and controller**

Create `app/Http/Requests/Api/StoreDataTableSavedViewRequest.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDataTableSavedViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->boolean('is_system')) {
            return $this->user()?->can('manage system views') ?? false;
        }

        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'table_name' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'filters' => ['nullable', 'array'],
            'sort' => ['nullable', 'string', 'max:255'],
            'columns' => ['nullable', 'array'],
            'column_order' => ['nullable', 'array'],
            'is_default' => ['boolean'],
            'is_shared' => ['boolean'],
            'is_system' => ['boolean'],
        ];
    }
}
```

Create `app/Http/Controllers/Api/DataTableSavedViewController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\DataTableSavedView;
use App\Http\Requests\Api\StoreDataTableSavedViewRequest;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class DataTableSavedViewController
{
    public function index(Request $request): JsonResponse
    {
        $tableName = $request->string('table_name')->toString();
        $userId = $request->user()->id;
        $orgId = TenantContext::id();

        return response()->json(
            DataTableSavedView::grouped($tableName, $userId, $orgId ?? 0)
        );
    }

    public function store(StoreDataTableSavedViewRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['created_by'] = $request->user()->id;

        if ($request->boolean('is_shared') || $request->boolean('is_system')) {
            $data['organization_id'] = TenantContext::id();
        }

        $view = DataTableSavedView::create($data);

        return response()->json($view, 201);
    }

    public function destroy(Request $request, DataTableSavedView $dataTableSavedView): Response
    {
        $user = $request->user();

        // Only creator or admin can delete
        abort_unless(
            $dataTableSavedView->user_id === $user->id
            || $dataTableSavedView->created_by === $user->id
            || $user->can('manage system views'),
            403
        );

        $dataTableSavedView->delete();

        return response()->noContent();
    }
}
```

- [ ] **Step 7: Register API routes**

Add to `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('data-table-saved-views', \App\Http\Controllers\Api\DataTableSavedViewController::class)
        ->only(['index', 'store', 'destroy']);
});
```

Check existing route structure first — add to the existing `auth:sanctum` group if one exists.

- [ ] **Step 8: Run the migration**

Run: `php artisan migrate`

- [ ] **Step 9: Run tests to verify they pass**

Run: `php artisan test --compact --filter=DataTableSavedViewTest`
Expected: All tests pass.

- [ ] **Step 10: Update the React DataTable quick views component**

Modify `resources/js/components/data-table/data-table-quick-views.tsx` to:
1. Replace localStorage with API calls to `/api/data-table-saved-views`
2. Group the dropdown into 3 sections: My Views / Team Views / System Views with `DropdownMenuLabel` headers and `DropdownMenuSeparator` between groups
3. Add a "Share with team" toggle (Switch component) in the save dialog
4. Add a "Set as system view" checkbox visible only when user has admin permissions (pass admin status as prop from parent)
5. Show shared/system badges next to view names in the dropdown

@skill: `inertia-react-development`, `tailwindcss-development` for React + styling patterns.

- [ ] **Step 11: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add database/migrations/*add_sharing_columns* app/Models/DataTableSavedView.php database/factories/DataTableSavedViewFactory.php app/Http/Controllers/Api/DataTableSavedViewController.php app/Http/Requests/Api/StoreDataTableSavedViewRequest.php routes/api.php resources/js/components/data-table/data-table-quick-views.tsx tests/Feature/DataTableSavedViewTest.php
git commit -m "feat: add shared and system DataTable saved views with 3-tier scoping"
```

---

### Task 2: PDF Generation Pipeline

**Files:**
- Create: `app/Actions/GeneratePdf.php`
- Create: `app/Jobs/GeneratePdfJob.php`
- Create: `tests/Feature/GeneratePdfTest.php`

**Context:**
- `spatie/laravel-pdf` (v2) is installed. Config at `config/laravel-pdf.php`.
- Spatie Media Library is installed. Models use `InteractsWithMedia` trait and `addMedia()->toMediaCollection()`.
- Follow the Action pattern: `final readonly class`, single `handle()` method.
- Follow the Job pattern from `app/Jobs/GenerateEmbedding.php`: `final class`, `ShouldQueue`, `Queueable` trait.
- @skill: `pest-testing`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Create `tests/Feature/GeneratePdfTest.php`:

```php
<?php

declare(strict_types=1);

use App\Actions\GeneratePdf;
use App\Jobs\GeneratePdfJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

it('generates a pdf from a blade view', function (): void {
    Storage::fake('local');
    View::addLocation(resource_path('views'));

    // Create a minimal test blade view
    file_put_contents(
        resource_path('views/test-pdf.blade.php'),
        '<h1>{{ $title }}</h1>'
    );

    $path = app(GeneratePdf::class)->handle(
        view: 'test-pdf',
        data: ['title' => 'Test PDF'],
        filename: 'test-output.pdf',
    );

    expect($path)->toEndWith('test-output.pdf');
    expect(file_exists($path))->toBeTrue();
});

it('dispatches the pdf job to the queue', function (): void {
    Queue::fake();

    GeneratePdfJob::dispatch(
        view: 'test-pdf',
        data: ['title' => 'Async PDF'],
        filename: 'async-output.pdf',
        userId: 1,
    );

    Queue::assertPushed(GeneratePdfJob::class);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=GeneratePdfTest`
Expected: FAIL — action and job don't exist.

- [ ] **Step 3: Create the GeneratePdf action**

Run: `php artisan make:action "GeneratePdf" --no-interaction`

Then replace contents:

```php
<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\MediaLibrary\HasMedia;

final readonly class GeneratePdf
{
    /**
     * Generate a PDF from a Blade view and optionally attach it to a model via Media Library.
     */
    public function handle(
        string $view,
        array $data,
        string $filename,
        ?Model $attachTo = null,
        string $collection = 'documents',
    ): string {
        $path = storage_path('app/pdf/'.$filename);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        Pdf::view($view, $data)->save($path);

        if ($attachTo instanceof HasMedia) {
            $attachTo->addMedia($path)
                ->preservingOriginal()
                ->toMediaCollection($collection);
        }

        return $path;
    }
}
```

- [ ] **Step 4: Create the GeneratePdfJob**

Run: `php artisan make:job GeneratePdfJob --no-interaction`

Then replace contents:

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\GeneratePdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use Throwable;

final class GeneratePdfJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(
        private readonly string $view,
        private readonly array $data,
        private readonly string $filename,
        private readonly int $userId,
        private readonly ?Model $attachTo = null,
        private readonly string $collection = 'documents',
    ) {}

    public function handle(GeneratePdf $generatePdf): void
    {
        $path = $generatePdf->handle(
            view: $this->view,
            data: $this->data,
            filename: $this->filename,
            attachTo: $this->attachTo,
            collection: $this->collection,
        );

        $user = User::find($this->userId);

        if ($user !== null) {
            $user->notify(new \App\Notifications\PdfReadyNotification($this->filename, $path));
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GeneratePdfJob failed', [
            'view' => $this->view,
            'filename' => $this->filename,
            'error' => $exception->getMessage(),
        ]);

        $user = User::find($this->userId);

        if ($user !== null) {
            $user->notify(new \App\Notifications\PdfFailedNotification($this->filename, $exception->getMessage()));
        }
    }
}
```

Note: You will need to create `App\Notifications\PdfReadyNotification` and `App\Notifications\PdfFailedNotification` as simple DatabaseNotification classes. Use `php artisan make:notification PdfReadyNotification --no-interaction` and `php artisan make:notification PdfFailedNotification --no-interaction`.

- [ ] **Step 5: Run tests to verify they pass**

Run: `php artisan test --compact --filter=GeneratePdfTest`
Expected: All tests pass.

- [ ] **Step 6: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add app/Actions/GeneratePdf.php app/Jobs/GeneratePdfJob.php app/Notifications/PdfReadyNotification.php app/Notifications/PdfFailedNotification.php tests/Feature/GeneratePdfTest.php
git commit -m "feat: add GeneratePdf action and async GeneratePdfJob with Media Library attachment"
```

---

### Task 3: Scheduled Email Triggers

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_mail_trigger_schedules_table.php`
- Create: `app/Models/MailTriggerSchedule.php`
- Create: `app/Services/ScheduledMailDispatcher.php`
- Create: `app/Filament/System/Pages/ManageMailTriggers.php`
- Create: `tests/Feature/ScheduledMailDispatcherTest.php`

**Context:**
- `martinpetricko/laravel-database-mail` handles event → template. Config at `config/database-mail.php` lists registered events (see file for exact event classes).
- `thomasjohnkane/snooze` provides `ScheduledNotification` for delayed sends.
- Existing events implement `TriggersDatabaseMail` interface and `CanTriggerDatabaseMail` trait.
- Filament settings pages live at `app/Filament/System/Pages/Manage*.php` — follow that pattern.
- @skill: `pest-testing`, `database-mail`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests for the dispatcher**

Create `tests/Feature/ScheduledMailDispatcherTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\MailTriggerSchedule;
use App\Models\Organization;
use App\Services\ScheduledMailDispatcher;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

beforeEach(function (): void {
    $this->org = Organization::factory()->create();
    config()->set('tenancy.current_organization_id', $this->org->id);
});

it('returns null when no schedule exists for event', function (): void {
    $dispatcher = app(ScheduledMailDispatcher::class);

    $result = $dispatcher->getScheduleForEvent(
        eventClass: 'App\\Events\\User\\UserCreated',
        organizationId: $this->org->id,
    );

    expect($result)->toBeNull();
});

it('returns the schedule when one exists and is active', function (): void {
    $schedule = MailTriggerSchedule::factory()->create([
        'organization_id' => $this->org->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'delay_minutes' => 60,
        'is_active' => true,
    ]);

    $dispatcher = app(ScheduledMailDispatcher::class);

    $result = $dispatcher->getScheduleForEvent(
        eventClass: 'App\\Events\\User\\UserCreated',
        organizationId: $this->org->id,
    );

    expect($result)->not->toBeNull();
    expect($result->delay_minutes)->toBe(60);
});

it('returns null when schedule is inactive', function (): void {
    MailTriggerSchedule::factory()->create([
        'organization_id' => $this->org->id,
        'event_class' => 'App\\Events\\User\\UserCreated',
        'is_active' => false,
    ]);

    $dispatcher = app(ScheduledMailDispatcher::class);

    $result = $dispatcher->getScheduleForEvent(
        eventClass: 'App\\Events\\User\\UserCreated',
        organizationId: $this->org->id,
    );

    expect($result)->toBeNull();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=ScheduledMailDispatcherTest`
Expected: FAIL.

- [ ] **Step 3: Create the migration**

Run: `php artisan make:migration create_mail_trigger_schedules_table --no-interaction`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_trigger_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('event_class');
            $table->foreignId('template_id')->nullable()->constrained('mail_templates')->nullOnDelete();
            $table->unsignedInteger('delay_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('feature_flag')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['organization_id', 'event_class']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_trigger_schedules');
    }
};
```

- [ ] **Step 4: Create the model and factory**

Create `app/Models/MailTriggerSchedule.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MailTriggerSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'event_class',
        'template_id',
        'delay_minutes',
        'is_active',
        'feature_flag',
        'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'delay_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
```

Create factory `database/factories/MailTriggerScheduleFactory.php`:

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MailTriggerSchedule;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MailTriggerSchedule> */
final class MailTriggerScheduleFactory extends Factory
{
    protected $model = MailTriggerSchedule::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'event_class' => 'App\\Events\\User\\UserCreated',
            'template_id' => null,
            'delay_minutes' => null,
            'is_active' => true,
            'feature_flag' => null,
        ];
    }
}
```

- [ ] **Step 5: Create the ScheduledMailDispatcher service**

Create `app/Services/ScheduledMailDispatcher.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MailTriggerSchedule;
use Laravel\Pennant\Feature;

final class ScheduledMailDispatcher
{
    /**
     * Look up the active schedule for an event in a given org.
     * Returns null if no active schedule exists or feature flag is not active.
     */
    public function getScheduleForEvent(string $eventClass, int $organizationId): ?MailTriggerSchedule
    {
        $schedule = MailTriggerSchedule::query()
            ->where('organization_id', $organizationId)
            ->where('event_class', $eventClass)
            ->where('is_active', true)
            ->first();

        if ($schedule === null) {
            return null;
        }

        // Check feature flag if one is specified
        if ($schedule->feature_flag !== null && ! Feature::active($schedule->feature_flag)) {
            return null;
        }

        return $schedule;
    }

    /**
     * Whether a send should be delayed based on the schedule.
     */
    public function shouldDelay(MailTriggerSchedule $schedule): bool
    {
        return $schedule->delay_minutes !== null && $schedule->delay_minutes > 0;
    }

    /**
     * Whether a send should be suppressed (schedule exists but is inactive).
     */
    public function shouldSuppress(string $eventClass, int $organizationId): bool
    {
        return MailTriggerSchedule::query()
            ->where('organization_id', $organizationId)
            ->where('event_class', $eventClass)
            ->where('is_active', false)
            ->exists();
    }
}
```

- [ ] **Step 6: Run migration and tests**

Run: `php artisan migrate`
Run: `php artisan test --compact --filter=ScheduledMailDispatcherTest`
Expected: All tests pass.

- [ ] **Step 7: Create the Filament admin page**

Create `app/Filament/System/Pages/ManageMailTriggers.php`. This is a Livewire page (not a SettingsPage since data is per-org, not global settings). Follow the pattern from `ManageFeatureFlags.php` for navigation setup but use a custom Livewire component with a Filament table.

```php
<?php

declare(strict_types=1);

namespace App\Filament\System\Pages;

use App\Models\MailTriggerSchedule;
use App\Support\TenantContext;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use MartinPetricko\LaravelDatabaseMail\Contracts\TriggersDatabaseMail;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;
use UnitEnum;

final class ManageMailTriggers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Settings · Mail';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Mail Triggers';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.system.pages.manage-mail-triggers';

    public static function canAccess(): bool
    {
        return filament()->getCurrentPanel()?->getId() === 'system';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MailTriggerSchedule::query()
                    ->where('organization_id', TenantContext::id())
            )
            ->columns([
                TextColumn::make('event_class')
                    ->label('Event')
                    ->formatStateUsing(function (string $state): string {
                        if (class_exists($state) && is_subclass_of($state, TriggersDatabaseMail::class)) {
                            return app($state)->getName();
                        }
                        return class_basename($state);
                    }),
                TextColumn::make('template_id')
                    ->label('Template')
                    ->formatStateUsing(fn (?int $state): string =>
                        $state ? (MailTemplate::find($state)?->name ?? '—') : '—'
                    ),
                TextColumn::make('delay_minutes')
                    ->label('Delay')
                    ->formatStateUsing(fn (?int $state): string =>
                        $state ? "{$state} min" : 'Immediate'
                    ),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('feature_flag')
                    ->label('Feature Flag')
                    ->placeholder('—'),
            ])
            ->headerActions([
                Action::make('addTrigger')
                    ->label('Add Trigger')
                    ->schema([
                        Select::make('event_class')
                            ->label('Event')
                            ->options(fn (): array => collect(config('database-mail.events', []))
                                ->mapWithKeys(fn (string $class): array => [
                                    $class => class_exists($class) && is_subclass_of($class, TriggersDatabaseMail::class)
                                        ? app($class)->getName()
                                        : class_basename($class),
                                ])->all()
                            )
                            ->required(),
                        Select::make('template_id')
                            ->label('Template')
                            ->options(MailTemplate::pluck('name', 'id')->all())
                            ->nullable(),
                        TextInput::make('delay_minutes')
                            ->label('Delay (minutes)')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave empty for immediate send'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Select::make('feature_flag')
                            ->label('Feature Flag')
                            ->options(fn (): array => collect(config('feature-flags.features', []))
                                ->keys()
                                ->mapWithKeys(fn (string $key): array => [$key => $key])
                                ->all()
                            )
                            ->nullable(),
                    ])
                    ->action(function (array $data): void {
                        MailTriggerSchedule::create([
                            ...$data,
                            'organization_id' => TenantContext::id(),
                            'created_by' => auth()->id(),
                        ]);
                    }),
            ]);
    }
}
```

You will also need to create the Blade view `resources/views/filament/system/pages/manage-mail-triggers.blade.php`:

```blade
<x-filament-panels::page>
    {{ $this->table }}
</x-filament-panels::page>
```

@skill: Search Filament v5 docs for `HasTable` page pattern to confirm the exact v5 API.

- [ ] **Step 8: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add database/migrations/*create_mail_trigger_schedules* app/Models/MailTriggerSchedule.php database/factories/MailTriggerScheduleFactory.php app/Services/ScheduledMailDispatcher.php app/Filament/System/Pages/ManageMailTriggers.php tests/Feature/ScheduledMailDispatcherTest.php
git commit -m "feat: add scheduled email triggers with Snooze delay and Filament admin page"
```

---

## Track 2 — AI Stack

### Task 4: RAG Pipeline (pgvector)

**Files:**
- Modify: `config/ai.php`
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_model_embeddings_table.php`
- Create: `app/Models/ModelEmbedding.php`
- Create: `app/Models/Concerns/HasEmbeddings.php`
- Create: `app/Jobs/GenerateEmbeddingJob.php` (new version — replaces existing `app/Jobs/GenerateEmbedding.php` pattern)
- Create: `app/Services/SemanticSearchService.php`
- Create: `app/Console/Commands/RefreshEmbeddingsCommand.php`
- Create: `tests/Feature/HasEmbeddingsTest.php`
- Create: `tests/Feature/SemanticSearchServiceTest.php`

**Context:**
- pgvector is installed. Existing `embedding_demos` table and `GenerateEmbedding` job at `app/Jobs/GenerateEmbedding.php` show the pattern.
- `Laravel\Ai\Embeddings::for([$text])->generate()` is the API for generating embeddings.
- `config/ai.php` already has `default_for_embeddings` and `caching.embeddings` keys.
- `Pgvector\Laravel\Vector` cast is available.
- `HasNeighbors` trait from `pgvector/pgvector` is used in `EmbeddingDemo` model.
- Rate limiting: use `spatie/laravel-rate-limited-job-middleware`.
- @skill: `pest-testing`, `ai-sdk-development`, `taylor-otwell-style`.

- [ ] **Step 1: Add embeddings config to `config/ai.php`**

Add after the `caching` section:

```php
'embeddings' => [
    'provider' => env('EMBEDDING_PROVIDER', 'openai'),
    'model' => env('EMBEDDING_MODEL', 'text-embedding-3-small'),
    'dimensions' => (int) env('EMBEDDING_DIMENSIONS', 1536),
],
```

- [ ] **Step 2: Write tests for HasEmbeddings and SemanticSearchService**

Create `tests/Feature/HasEmbeddingsTest.php` and `tests/Feature/SemanticSearchServiceTest.php` with tests covering:
- Model with HasEmbeddings dispatches GenerateEmbeddingJob on created/updated
- `needsReembedding()` returns true when content changes, false when unchanged
- SemanticSearchService returns results scoped by organization
- SemanticSearchService respects `scope()` filter for model types
- SemanticSearchService respects `threshold()` for minimum similarity

Mock `Laravel\Ai\Embeddings` in tests since we don't want real API calls.

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact --filter="HasEmbeddingsTest|SemanticSearchServiceTest"`
Expected: FAIL.

- [ ] **Step 4: Create the migration**

Run: `php artisan make:migration create_model_embeddings_table --no-interaction`

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_embeddings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('embeddable_type', 50);
            $table->unsignedBigInteger('embeddable_id');
            $table->unsignedInteger('chunk_index')->default(0);
            // vector(1536) — default dimension for text-embedding-3-small
            // If changing provider, create a new migration to alter the dimension
            $table->vector('embedding', 1536);
            $table->string('content_hash', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['embeddable_type', 'embeddable_id', 'chunk_index'], 'model_embeddings_morph_chunk_unique');
            $table->index('organization_id');
        });

        // IVFFlat index for cosine similarity search
        // Note: IVFFlat requires data to be present for training — run after initial data load
        // For MVP, use exact search (no index) which works on small datasets
    }

    public function down(): void
    {
        Schema::dropIfExists('model_embeddings');
    }
};
```

- [ ] **Step 5: Create ModelEmbedding model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Pgvector\Laravel\Vector;

final class ModelEmbedding extends Model
{
    protected $fillable = [
        'organization_id',
        'embeddable_type',
        'embeddable_id',
        'chunk_index',
        'embedding',
        'content_hash',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
            'metadata' => 'array',
            'chunk_index' => 'integer',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function embeddable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
```

- [ ] **Step 6: Create HasEmbeddings trait**

Create `app/Models/Concerns/HasEmbeddings.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\ModelEmbedding;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Add to any model that should be embeddable for semantic search.
 * The model must implement toEmbeddableText(): string.
 */
trait HasEmbeddings
{
    abstract public function toEmbeddableText(): string;

    public static function bootHasEmbeddings(): void
    {
        static::created(function (self $model): void {
            GenerateEmbeddingJob::dispatch($model);
        });

        static::updated(function (self $model): void {
            if ($model->needsReembedding()) {
                GenerateEmbeddingJob::dispatch($model);
            }
        });
    }

    /** @return MorphOne<ModelEmbedding, $this> */
    public function embedding(): MorphOne
    {
        return $this->morphOne(ModelEmbedding::class, 'embeddable');
    }

    public function needsReembedding(): bool
    {
        $currentHash = hash('sha256', $this->toEmbeddableText());
        $storedHash = $this->embedding?->content_hash;

        return $storedHash === null || $storedHash !== $currentHash;
    }

    public function contentHash(): string
    {
        return hash('sha256', $this->toEmbeddableText());
    }
}
```

- [ ] **Step 7: Create GenerateEmbeddingJob (new polymorphic version)**

Create `app/Jobs/GenerateEmbeddingJob.php`:

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Concerns\HasEmbeddings;
use App\Models\ModelEmbedding;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Spatie\RateLimitedMiddleware\RateLimited;
use Throwable;

final class GenerateEmbeddingJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public function __construct(
        private readonly Model $model,
    ) {}

    public function uniqueId(): string
    {
        return $this->model->getMorphClass().':'.$this->model->getKey();
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [
            (new RateLimited)->allow(60)->everySeconds(60)->releaseAfterSeconds(30),
        ];
    }

    public function handle(): void
    {
        /** @var Model&HasEmbeddings $model */
        $model = $this->model;

        $text = mb_trim(strip_tags($model->toEmbeddableText()));

        if ($text === '') {
            return;
        }

        $contentHash = $model->contentHash();

        // Skip if content hasn't changed
        $existing = $model->embedding;
        if ($existing !== null && $existing->content_hash === $contentHash) {
            return;
        }

        $response = Embeddings::for([$text])->generate();
        $vector = $response->first();

        ModelEmbedding::updateOrCreate(
            [
                'embeddable_type' => $model->getMorphClass(),
                'embeddable_id' => $model->getKey(),
                'chunk_index' => 0,
            ],
            [
                'organization_id' => $model->organization_id ?? 0,
                'embedding' => $vector,
                'content_hash' => $contentHash,
            ],
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('GenerateEmbeddingJob failed', [
            'model' => $this->model::class,
            'key' => $this->model->getKey(),
            'error' => $exception->getMessage(),
        ]);
    }
}
```

Note: This replaces the existing `app/Jobs/GenerateEmbedding.php` pattern. The old job works on a single text column; this new one works on the `HasEmbeddings` trait's `toEmbeddableText()`. Keep the old job for backward compatibility with `EmbeddingDemo` — or migrate `EmbeddingDemo` to use `HasEmbeddings`.

- [ ] **Step 8: Create SemanticSearchService**

Create `app/Services/SemanticSearchService.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ModelEmbedding;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;
use Pgvector\Laravel\Vector;

final class SemanticSearchService
{
    /** @var array<int, class-string<Model>> */
    private array $scopes = [];

    private ?int $organizationId = null;

    private int $limit = 10;

    private float $threshold = 0.7;

    private string $queryText;

    public static function query(string $text): self
    {
        $instance = new self;
        $instance->queryText = $text;

        return $instance;
    }

    /** @param class-string<Model> ...$modelClasses */
    public function scope(string ...$modelClasses): self
    {
        $this->scopes = $modelClasses;

        return $this;
    }

    public function forOrganization(int $organizationId): self
    {
        $this->organizationId = $organizationId;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function threshold(float $threshold): self
    {
        $this->threshold = $threshold;

        return $this;
    }

    /**
     * Execute the semantic search and return hydrated models with similarity scores.
     *
     * @return Collection<int, Model>
     */
    public function get(): Collection
    {
        if ($this->organizationId === null) {
            throw new \InvalidArgumentException('Organization ID is required. Call forOrganization().');
        }

        // Generate embedding for the query
        $response = Embeddings::for([$this->queryText])->generate();
        $queryVector = $response->first();

        // Build the similarity query using a subquery to avoid binding order issues
        $vectorString = '['.implode(',', $queryVector->toArray()).']';

        $subQuery = ModelEmbedding::query()
            ->selectRaw("model_embeddings.*, 1 - (embedding <=> '{$vectorString}'::vector) as similarity_score")
            ->where('organization_id', $this->organizationId);

        // Filter by model types if scoped
        if ($this->scopes !== []) {
            $morphTypes = array_map(
                fn (string $class): string => (new $class)->getMorphClass(),
                $this->scopes,
            );
            $subQuery->whereIn('embeddable_type', $morphTypes);
        }

        $embeddings = $subQuery
            ->havingRaw('similarity_score >= ?', [$this->threshold])
            ->orderByDesc('similarity_score')
            ->limit($this->limit)
            ->get();

        // Hydrate the actual Eloquent models
        return $embeddings->map(function (ModelEmbedding $embedding): ?Model {
            $model = $embedding->embeddable;

            if ($model !== null) {
                $model->setAttribute('similarity_score', $embedding->similarity_score);
            }

            return $model;
        })->filter();
    }
}
```

- [ ] **Step 9: Create the refresh command**

Run: `php artisan make:command RefreshEmbeddingsCommand --no-interaction`

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\Concerns\HasEmbeddings;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

final class RefreshEmbeddingsCommand extends Command
{
    /** @var string */
    protected $signature = 'embeddings:refresh
        {model : Fully qualified model class name}
        {--chunk=500 : Chunk size for processing}';

    /** @var string */
    protected $description = 'Re-generate embeddings for all instances of a model';

    public function handle(): int
    {
        /** @var class-string<Model&HasEmbeddings> $modelClass */
        $modelClass = $this->argument('model');

        if (! class_exists($modelClass)) {
            $this->error("Class {$modelClass} does not exist.");

            return self::FAILURE;
        }

        if (! in_array(HasEmbeddings::class, class_uses_recursive($modelClass), true)) {
            $this->error("{$modelClass} does not use the HasEmbeddings trait.");

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        $total = $modelClass::count();

        $this->info("Dispatching embedding jobs for {$total} {$modelClass} records...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $modelClass::query()->chunk($chunkSize, function ($models) use ($bar): void {
            foreach ($models as $model) {
                GenerateEmbeddingJob::dispatch($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info('All embedding jobs dispatched to the queue.');

        return self::SUCCESS;
    }
}
```

- [ ] **Step 10: Run migration and tests**

Run: `php artisan migrate`
Run: `php artisan test --compact --filter="HasEmbeddingsTest|SemanticSearchServiceTest"`
Expected: All tests pass.

- [ ] **Step 11: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add config/ai.php database/migrations/*create_model_embeddings* app/Models/ModelEmbedding.php app/Models/Concerns/HasEmbeddings.php app/Jobs/GenerateEmbeddingJob.php app/Services/SemanticSearchService.php app/Console/Commands/RefreshEmbeddingsCommand.php tests/Feature/HasEmbeddingsTest.php tests/Feature/SemanticSearchServiceTest.php
git commit -m "feat: add RAG pipeline with HasEmbeddings trait, SemanticSearchService, and embeddings:refresh command"
```

---

### Task 5: ModuleToolRegistry

**Files:**
- Create: `app/Support/ModuleToolRegistry.php`
- Create: `app/Ai/Contracts/ModuleAiTool.php`
- Create: `app/Modules/Contracts/ProvidesAITools.php`
- Create: `app/Ai/Tools/SemanticSearchTool.php`
- Modify: `app/Modules/Support/ModuleProvider.php`
- Create: `tests/Feature/ModuleToolRegistryTest.php`

**Context:**
- Module providers extend `App\Modules\Support\ModuleProvider` (see `modules/crm/src/Providers/CrmModuleServiceProvider.php`).
- Modules already implement `ProvidesAIContext` with `tools(): array` method — but it returns empty arrays. We are building the registry that collects and filters these tools.
- Feature flags checked via `Feature::active()` from Pennant. Plan-gating via `FeatureHelper`.
- @skill: `pest-testing`, `pennant-development`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Create `tests/Feature/ModuleToolRegistryTest.php` testing:
- Registry collects tools from providers implementing `ProvidesAITools`
- `getToolsForOrganization()` filters by feature flag
- Base tools (not module-gated) are always included
- Empty registry returns only base tools

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=ModuleToolRegistryTest`

- [ ] **Step 3: Create the ModuleAiTool contract**

Create `app/Ai/Contracts/ModuleAiTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

interface ModuleAiTool
{
    /**
     * The Pennant feature flag required to use this tool, or null if always available.
     */
    public static function requiredFeature(): ?string;
}
```

- [ ] **Step 4: Create ProvidesAITools contract**

Create `app/Modules/Contracts/ProvidesAITools.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Contracts;

interface ProvidesAITools
{
    /**
     * Return AI tool class names this module provides.
     *
     * @return array<int, class-string>
     */
    public function registerAiTools(): array;
}
```

- [ ] **Step 5: Create ModuleToolRegistry**

Create `app/Support/ModuleToolRegistry.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Ai\Contracts\ModuleAiTool;
use App\Models\Organization;
use App\Modules\Contracts\ProvidesAITools;
use App\Modules\Support\ModuleProvider;
use Illuminate\Support\Facades\App;
use Laravel\Pennant\Feature;

final class ModuleToolRegistry
{
    /** @var array<int, class-string> */
    private array $baseTools = [];

    /** @var array<int, class-string> */
    private array $moduleTools = [];

    private bool $collected = false;

    /**
     * Register a base tool that is always available (not module-gated).
     *
     * @param class-string $toolClass
     */
    public function registerBaseTool(string $toolClass): void
    {
        $this->baseTools[] = $toolClass;
    }

    /**
     * Collect tools from all enabled module providers.
     */
    public function collect(): void
    {
        if ($this->collected) {
            return;
        }

        $providers = App::getLoadedProviders();

        foreach ($providers as $providerClass => $loaded) {
            if (! $loaded) {
                continue;
            }

            $provider = App::getProvider($providerClass);

            if ($provider instanceof ModuleProvider && $provider instanceof ProvidesAITools) {
                foreach ($provider->registerAiTools() as $toolClass) {
                    $this->moduleTools[] = $toolClass;
                }
            }
        }

        $this->collected = true;
    }

    /**
     * Get all tools available for an organization, filtered by feature flags.
     *
     * @return array<int, object>
     */
    public function getToolsForOrganization(Organization $org): array
    {
        $this->collect();

        $tools = [];

        // Base tools are always available
        foreach ($this->baseTools as $toolClass) {
            $tools[] = App::make($toolClass);
        }

        // Module tools filtered by feature flag
        foreach ($this->moduleTools as $toolClass) {
            if (! is_subclass_of($toolClass, ModuleAiTool::class)) {
                $tools[] = App::make($toolClass);

                continue;
            }

            $requiredFeature = $toolClass::requiredFeature();

            if ($requiredFeature === null || Feature::for($org)->active($requiredFeature)) {
                $tools[] = App::make($toolClass);
            }
        }

        return $tools;
    }
}
```

- [ ] **Step 6: Create SemanticSearchTool (base tool)**

Create `app/Ai/Tools/SemanticSearchTool.php` — an AI tool that wraps `SemanticSearchService`. This is a base tool (always available). Implement using `laravel/ai` tool pattern matching existing tools in `app/Ai/Tools/`.

- [ ] **Step 7: Update ModuleProvider to register AI tools**

Modify `app/Modules/Support/ModuleProvider.php`:
- In the `boot()` method, after `registerAIContext()`, add a call to register tools with the `ModuleToolRegistry` singleton if the provider implements `ProvidesAITools`.
- Register `ModuleToolRegistry` as a singleton in a service provider (e.g., `AppServiceProvider` or a new `AiServiceProvider`).

- [ ] **Step 8: Run tests**

Run: `php artisan test --compact --filter=ModuleToolRegistryTest`
Expected: All tests pass.

- [ ] **Step 9: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add app/Support/ModuleToolRegistry.php app/Ai/Contracts/ModuleAiTool.php app/Modules/Contracts/ProvidesAITools.php app/Ai/Tools/SemanticSearchTool.php app/Modules/Support/ModuleProvider.php tests/Feature/ModuleToolRegistryTest.php
git commit -m "feat: add ModuleToolRegistry for collecting and filtering AI tools from modules"
```

---

### Task 6: OrgScopedAgent

**Files:**
- Create: `app/Ai/OrgScopedAgent.php`
- Create: `app/Ai/Contracts/ContextAwareTool.php`
- Create: `tests/Feature/OrgScopedAgentTest.php`

**Context:**
- Existing `AssistantAgent` at `app/Ai/Agents/AssistantAgent.php` implements `Agent`, `Conversational`, `HasMiddleware`, `HasTools`.
- Uses `Promptable` trait, `RemembersConversations` trait.
- `TenantContext::id()` gives the current org ID.
- Credits system exists on organizations. BYOK check: org-overridable AI settings group.
- @skill: `pest-testing`, `ai-sdk-development`, `taylor-otwell-style`.

- [ ] **Step 1: Write tests**

Create `tests/Feature/OrgScopedAgentTest.php` testing:
- Agent resolves tools from `ModuleToolRegistry` for the current org
- Agent injects org context into tool calls
- Conversations are scoped to user + org
- Context payload is passed to tools implementing `ContextAwareTool`

Mock the AI SDK to avoid real API calls.

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Create ContextAwareTool interface**

Create `app/Ai/Contracts/ContextAwareTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai\Contracts;

interface ContextAwareTool
{
    /**
     * Set the page context for this tool invocation.
     *
     * @param array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} $context
     */
    public function setContext(array $context): void;
}
```

- [ ] **Step 4: Create OrgScopedAgent**

Create `app/Ai/OrgScopedAgent.php`:

```php
<?php

declare(strict_types=1);

namespace App\Ai;

use App\Ai\Contracts\ContextAwareTool;
use App\Ai\Middleware\WithMemoryUnlessUnavailable;
use App\Models\Organization;
use App\Models\User;
use App\Support\ModuleToolRegistry;
use App\Support\TenantContext;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

final class OrgScopedAgent implements Agent, Conversational, HasMiddleware, HasTools
{
    use Promptable;
    use RemembersConversations;

    /** @var array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} */
    private array $pageContext = [];

    public function __construct(
        private readonly Organization $organization,
        private readonly User $user,
        private readonly ModuleToolRegistry $toolRegistry,
    ) {}

    public static function make(): self
    {
        $org = Organization::findOrFail(TenantContext::id());
        $user = auth()->user();

        return new self(
            organization: $org,
            user: $user,
            toolRegistry: app(ModuleToolRegistry::class),
        );
    }

    /** @param array{page?: string, entity_type?: string, entity_id?: int, entity_name?: string} $context */
    public function withContext(array $context): self
    {
        $this->pageContext = $context;

        return $this;
    }

    public function instructions(): string
    {
        $orgName = $this->organization->name;
        $contextLine = '';

        if ($this->pageContext !== []) {
            $page = $this->pageContext['page'] ?? 'unknown';
            $entity = $this->pageContext['entity_type'] ?? null;
            $entityName = $this->pageContext['entity_name'] ?? null;

            $contextLine = "\n\nThe user is currently on page: {$page}.";

            if ($entity !== null) {
                $contextLine .= " They are viewing a {$entity}";
                if ($entityName !== null) {
                    $contextLine .= " named \"{$entityName}\"";
                }
                $contextLine .= '.';
            }
        }

        return "You are an AI assistant for {$orgName}. "
            .'Help the user with their tasks using the available tools. '
            .'All data is scoped to the user\'s organization — you only have access to their data. '
            .'Use the Store Memory tool to save important facts. '
            .'Use the Recall Memory tool when you need context from previous conversations.'
            .$contextLine;
    }

    public function tools(): iterable
    {
        $tools = $this->toolRegistry->getToolsForOrganization($this->organization);

        // Inject page context into context-aware tools
        foreach ($tools as $tool) {
            if ($tool instanceof ContextAwareTool && $this->pageContext !== []) {
                $tool->setContext($this->pageContext);
            }
        }

        return $tools;
    }

    public function middleware(): array
    {
        return [
            new WithMemoryUnlessUnavailable(
                ['user_id' => $this->user->id, 'organization_id' => $this->organization->id],
                limit: (int) config('memory.middleware_recall_limit', 5),
            ),
        ];
    }
}
```

Note: Credit deduction middleware should be added here. Check if the org has BYOK AI keys in the org-overridable AI settings group. If not, deduct credits per turn. This may require a custom middleware class `DeductAiCredits` that wraps the agent turn.

- [ ] **Step 5: Run tests**

Run: `php artisan test --compact --filter=OrgScopedAgentTest`
Expected: All tests pass.

- [ ] **Step 6: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add app/Ai/OrgScopedAgent.php app/Ai/Contracts/ContextAwareTool.php tests/Feature/OrgScopedAgentTest.php
git commit -m "feat: add OrgScopedAgent with tenant context, tool filtering, and page context injection"
```

---

### Task 7: Context Injection (Frontend → Backend)

**Files:**
- Create: `resources/js/hooks/use-agent-context.ts`
- Modify: `app/Http/Controllers/Api/ChatController.php`
- Modify: `app/Http/Requests/Api/StoreChatMessageRequest.php`
- Create: `tests/Feature/ChatContextInjectionTest.php`

**Context:**
- `ChatController` at `app/Http/Controllers/Api/ChatController.php` currently uses `AssistantAgent`. It needs to use `OrgScopedAgent` instead and accept a `context` payload.
- `StoreChatMessageRequest` at `app/Http/Requests/Api/StoreChatMessageRequest.php` needs `context` validation.
- Module manifests don't have `contextual_models` yet — add to `ModuleManifest`.
- @skill: `inertia-react-development`, `wayfinder-development`, `pest-testing`.

- [ ] **Step 1: Write backend test**

Create `tests/Feature/ChatContextInjectionTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Organization;

beforeEach(function (): void {
    $this->org = Organization::factory()->create();
    $this->user = createTestUser();
    $this->actingAs($this->user);
    config()->set('tenancy.current_organization_id', $this->org->id);
});

it('accepts context payload in chat request', function (): void {
    // This test validates the request accepts context — mock the AI to avoid real calls
    \Illuminate\Support\Facades\Http::fake();

    $response = $this->postJson('/api/chat', [
        'messages' => [
            ['role' => 'user', 'content' => 'Hello'],
        ],
        'context' => [
            'page' => '/crm/contacts/123',
            'entity_type' => 'contact',
            'entity_id' => 123,
            'entity_name' => 'John Smith',
        ],
    ]);

    // Should not get 422 (validation error) for the context field
    expect($response->status())->not->toBe(422);
});
```

- [ ] **Step 2: Update StoreChatMessageRequest**

Add `context` validation rules:

```php
'context' => ['nullable', 'array'],
'context.page' => ['nullable', 'string'],
'context.entity_type' => ['nullable', 'string'],
'context.entity_id' => ['nullable', 'integer'],
'context.entity_name' => ['nullable', 'string'],
```

- [ ] **Step 3: Update ChatController to use OrgScopedAgent**

Modify `app/Http/Controllers/Api/ChatController.php`:
- Replace `AssistantAgent::make(...)` with `OrgScopedAgent::make()`
- Pass `$request->input('context', [])` via `->withContext()`
- Add `organization_id` to conversation creation
- Keep existing streaming infrastructure intact

- [ ] **Step 4: Create `useAgentContext` hook**

Create `resources/js/hooks/use-agent-context.ts`:

```typescript
import { usePage } from '@inertiajs/react';
import { useMemo } from 'react';

interface AgentContext {
    page: string;
    entity_type?: string;
    entity_id?: number;
    entity_name?: string;
}

/**
 * Extracts the current page context for the AI agent.
 * Detects entities from Inertia page props based on module-registered contextual models.
 */
export function useAgentContext(): AgentContext {
    const { url, props } = usePage();

    return useMemo(() => {
        const context: AgentContext = { page: url };

        // Check for common entity patterns in page props
        // Modules declare contextual_models in module.json
        const contextualKeys = ['contact', 'deal', 'project', 'lot', 'employee', 'activity'];

        for (const key of contextualKeys) {
            const entity = (props as Record<string, unknown>)[key];
            if (entity && typeof entity === 'object' && 'id' in entity) {
                context.entity_type = key;
                context.entity_id = (entity as { id: number }).id;
                // Try common name fields
                const named = entity as Record<string, unknown>;
                context.entity_name = (named.name ?? named.title ?? named.full_name ?? `${named.first_name ?? ''} ${named.last_name ?? ''}`.trim()) as string || undefined;
                break; // Use first match
            }
        }

        return context;
    }, [url, props]);
}
```

- [ ] **Step 5: Run tests**

Run: `php artisan test --compact --filter=ChatContextInjectionTest`
Expected: Tests pass (context accepted, not 422).

- [ ] **Step 6: Run Pint and commit**

Run: `vendor/bin/pint --dirty --format agent`

```bash
git add resources/js/hooks/use-agent-context.ts app/Http/Controllers/Api/ChatController.php app/Http/Requests/Api/StoreChatMessageRequest.php tests/Feature/ChatContextInjectionTest.php
git commit -m "feat: add context injection from frontend to OrgScopedAgent via useAgentContext hook"
```

---

### Task 8: Chat UI Rework

**Files:**
- Create: `resources/js/components/global-chat/global-chat-widget.tsx`
- Create: `resources/js/components/global-chat/chat-slide-over.tsx`
- Create: `resources/js/components/global-chat/conversation-list.tsx`
- Create: `resources/js/components/global-chat/chat-panel.tsx`
- Create: `resources/js/components/global-chat/chat-input.tsx`
- Create: `resources/js/components/global-chat/voice-input.tsx`
- Create: `resources/js/components/global-chat/voice-output.tsx`
- Create: `resources/js/components/global-chat/file-upload.tsx`
- Create: `resources/js/components/chat/renderers/renderer-registry.tsx`
- Create: `resources/js/components/chat/renderers/table-renderer.tsx`
- Create: `resources/js/components/chat/renderers/card-renderer.tsx`
- Create: `resources/js/components/chat/renderers/chart-renderer.tsx`
- Create: `resources/js/components/chat/renderers/action-renderer.tsx`
- Create: `resources/js/components/chat/renderers/text-renderer.tsx`
- Modify: `resources/js/layouts/app-layout.tsx` (or equivalent layout file)
- Modify: `resources/js/pages/chat/index.tsx`

**Context:**
- Existing chat UI at `resources/js/pages/chat/` with components: `chat-input.tsx`, `conversation-sidebar.tsx`, `message-bubble.tsx`, `message-list.tsx`, `empty-state.tsx`.
- Existing ChatController uses NDJSON streaming (TanStack AG-UI protocol). Keep this protocol.
- Design: 2-panel layout (160px conversation list + 400px active chat) in a 560px slide-over. See mockup in `.superpowers/brainstorm/` for visual reference.
- Follow DESIGN.md: dark-first, JetBrains Mono headings, IBM Plex Sans body, muted teal accent `oklch(0.65 0.14 165)`, no card shadows, minimal motion.
- @skill: `inertia-react-development`, `tailwindcss-development`, `frontend-design`.

This task is the largest and should be broken into sub-steps. Each sub-step produces a working, committable piece.

- [ ] **Step 1: Create the renderer registry and base renderers**

Create `resources/js/components/chat/renderers/renderer-registry.tsx`:

```typescript
import { type ComponentType } from 'react';

export interface BlockData {
    type: string;
    data: Record<string, unknown>;
}

type RendererComponent = ComponentType<{ data: Record<string, unknown> }>;

const registry = new Map<string, RendererComponent>();

export function registerRenderer(type: string, component: RendererComponent): void {
    registry.set(type, component);
}

export function getRenderer(type: string): RendererComponent | undefined {
    return registry.get(type);
}

export function renderBlock(block: BlockData): JSX.Element | null {
    const Renderer = getRenderer(block.type);
    if (!Renderer) return null;
    return <Renderer data={block.data} />;
}
```

Then create `text-renderer.tsx`, `table-renderer.tsx`, `card-renderer.tsx`, `chart-renderer.tsx`, and `action-renderer.tsx` — each as a simple React component rendering the appropriate block type. Register all in `renderer-registry.tsx`.

Commit: `feat: add structured response renderer registry with base renderers`

- [ ] **Step 2: Create the global chat widget skeleton**

Create `resources/js/components/global-chat/global-chat-widget.tsx`:
- Floating button component (bottom-right, teal accent, chat icon)
- Manages open/closed state
- Listens for `Cmd+K` / `Ctrl+K` keyboard shortcut (app-scoped, ignores when input focused)
- Shows unread badge
- When open, renders `ChatSlideOver`

Add the widget to the app layout (modify `resources/js/layouts/app-layout.tsx` or equivalent) so it renders on every page.

Commit: `feat: add global chat floating button with keyboard shortcut`

- [ ] **Step 3: Create the slide-over and 2-panel layout**

Create `resources/js/components/global-chat/chat-slide-over.tsx`:
- 560px slide-over panel from right side
- 2-panel layout: `ConversationList` (160px) + `ChatPanel` (flex)
- Slide animation (200ms per DESIGN.md)
- Close button and expand-to-full-page button

Create `resources/js/components/global-chat/conversation-list.tsx`:
- New Chat button
- Search input
- Conversation items: title, preview, timestamp, unread dot
- Active conversation highlighted with teal left border
- Fetches conversations from `/api/conversations` (existing API)

Create `resources/js/components/global-chat/chat-panel.tsx`:
- Context bar (uses `useAgentContext()`)
- Message list (reuse/refactor from existing `message-list.tsx`)
- Renders structured blocks using `renderBlock()` from renderer registry
- Voice playback button on agent messages

Commit: `feat: add 2-panel chat slide-over with conversation list and chat panel`

- [ ] **Step 4: Create the input area with file upload and voice**

Create `resources/js/components/global-chat/chat-input.tsx`:
- Text input field
- Send button
- File upload button (triggers file picker)
- Voice input button

Create `resources/js/components/global-chat/file-upload.tsx`:
- File picker (images + documents)
- Preview attached files
- Upload to server (store in Media Library on the conversation)
- Pass file references with the chat message

Create `resources/js/components/global-chat/voice-input.tsx`:
- Web Speech API (`SpeechRecognition`)
- Red active state with waveform visualization
- Transcribed text fills the input field
- Graceful fallback if browser doesn't support Speech API

Create `resources/js/components/global-chat/voice-output.tsx`:
- Play/pause TTS on agent messages
- Call `/api/chat/tts` endpoint (create if needed) that uses `laravel/ai` audio generation
- Audio element with playback controls

Commit: `feat: add chat input with file upload, voice input, and voice output`

- [ ] **Step 5: Integrate streaming with structured blocks**

Update `ChatPanel` to:
- Use the existing NDJSON streaming protocol from `ChatController`
- Progressive token rendering with typing cursor animation
- After stream completes, parse `blocks` from the final message if present
- Render structured blocks (table, card, chart, action) below the text

Commit: `feat: integrate streaming responses with structured block rendering`

- [ ] **Step 6: Update the full-page /chat route**

Modify `resources/js/pages/chat/index.tsx`:
- Use the same 2-panel layout as the slide-over, but full width
- Wider conversation list (220px) and wider chat panel
- Reuse all the same components (`ConversationList`, `ChatPanel`, `ChatInput`)

Commit: `feat: update full-page chat to use 2-panel layout matching slide-over`

- [ ] **Step 7: Final integration and testing**

- Verify the widget appears on all authenticated pages
- Test keyboard shortcut opens/closes
- Test conversation creation, messaging, streaming
- Test file upload works
- Test voice input (browser-dependent)
- Run: `npm run build` to verify no build errors

```bash
git add resources/js/components/global-chat/ resources/js/components/chat/renderers/ resources/js/layouts/ resources/js/pages/chat/
git commit -m "feat: complete global chat UI rework with 2-panel layout, streaming, voice, and file uploads"
```

- [ ] **Step 8: Run full test suite**

Run: `php artisan test --compact`
Run: `npm run build`

Fix any failures. Final commit if needed.

---

## Post-Implementation

After all 8 tasks are complete:

1. Run full test suite: `php artisan test --compact`
2. Run Pint on all modified PHP: `vendor/bin/pint --dirty --format agent`
3. Run ESLint: `npx eslint resources/js/`
4. Run build: `npm run build`
5. Verify no type errors: `vendor/bin/phpstan analyse` (if configured)
