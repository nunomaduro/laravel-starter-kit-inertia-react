# Email Template Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add org-scoped email template customization on top of the existing martinpetricko/laravel-database-mail package, allowing org admins to customize transactional email templates via a Tiptap rich text editor with variable insertion and live preview.

**Architecture:** Extend existing `mail_templates` table with `organization_id` for org-scoping -> fallback chain (org template -> default template) -> Inertia React settings page with Tiptap editor, variable toolbar, and split-view preview. No new service class or config file — uses existing database-mail event registration.

**Tech Stack:** Laravel 13, martinpetricko/laravel-database-mail ^2.0.3, @tiptap/react (new), Inertia.js v2, React 19, Tailwind CSS v4

**Spec:** `docs/superpowers/specs/2026-03-30-webhooks-email-templates-design.md` (Section 3)

---

## File Structure

### New Files

| File | Responsibility |
|------|---------------|
| `database/migrations/XXXX_add_organization_id_to_mail_templates.php` | Add org scoping columns |
| `app/Http/Controllers/Settings/EmailTemplatesController.php` | List, edit, preview, reset |
| `app/Http/Requests/Settings/UpdateEmailTemplateRequest.php` | Template update validation |
| `resources/js/pages/settings/email-templates/index.tsx` | List all events with template status |
| `resources/js/pages/settings/email-templates/edit.tsx` | Tiptap editor + variable toolbar + preview |
| `resources/js/components/tiptap-editor.tsx` | Reusable Tiptap rich text editor component |
| `tests/Feature/Settings/EmailTemplatesControllerTest.php` | Controller feature tests |
| `docs/user-guide/email-templates.md` | Admin user guide |

### Modified Files

| File | Change |
|------|--------|
| `database/seeders/data/organization-permissions.json` | Add `org_email_templates` permission group |
| `routes/settings.php` | Add email template settings routes |
| `resources/js/layouts/settings/layout.tsx` | Add email templates nav item |
| `package.json` | Add @tiptap/react and extensions |

---

### Task 1: Install Tiptap packages

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Install Tiptap and extensions**

```bash
npm install @tiptap/react @tiptap/starter-kit @tiptap/extension-link @tiptap/extension-placeholder @tiptap/pm
```

- [ ] **Step 2: Verify installation**

```bash
npm run build
```

Expected: Build succeeds with new packages.

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore: install @tiptap/react rich text editor packages"
```

---

### Task 2: Create migration and add permissions

**Files:**
- Create: `database/migrations/XXXX_add_organization_id_to_mail_templates.php`
- Modify: `database/seeders/data/organization-permissions.json`

- [ ] **Step 1: Create the migration**

```bash
php artisan make:migration add_organization_id_to_mail_templates --no-interaction
```

Edit the generated migration:

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
        Schema::table('mail_templates', function (Blueprint $table): void {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->boolean('is_default')->default(false)->after('is_active');

            $table->index('organization_id');
            $table->index(['event', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::table('mail_templates', function (Blueprint $table): void {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropIndex(['event', 'organization_id']);
            $table->dropColumn(['organization_id', 'is_default']);
        });
    }
};
```

- [ ] **Step 2: Add email template permissions**

In `database/seeders/data/organization-permissions.json`, add after the `org_webhooks` group (or `org_dashboards` if webhooks plan hasn't been executed yet):

```json
"org_email_templates": {
    "permissions": [
        {"name": "org.email-templates.view", "roles": ["owner", "admin"], "org_grantable": true},
        {"name": "org.email-templates.manage", "roles": ["owner", "admin"], "org_grantable": true}
    ]
}
```

- [ ] **Step 3: Run the migration**

```bash
php artisan migrate
```

Expected: Migration runs successfully.

- [ ] **Step 4: Mark existing templates as defaults**

```bash
php artisan tinker --execute "DB::table('mail_templates')->whereNull('organization_id')->update(['is_default' => true]);"
```

- [ ] **Step 5: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/*add_organization_id_to_mail_templates.php database/seeders/data/organization-permissions.json
git commit -m "feat(email-templates): add org scoping migration and permissions"
```

---

### Task 3: Create Tiptap editor component

**Files:**
- Create: `resources/js/components/tiptap-editor.tsx`

- [ ] **Step 1: Create the reusable Tiptap component**

Create `resources/js/components/tiptap-editor.tsx`:

```tsx
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import {
    Bold,
    Italic,
    List,
    ListOrdered,
    Heading2,
    Heading3,
    Link as LinkIcon,
    Undo,
    Redo,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useEffect } from 'react';

interface TiptapEditorProps {
    content: string;
    onChange: (html: string) => void;
    placeholder?: string;
    className?: string;
    variables?: Record<string, Record<string, string>>;
}

export default function TiptapEditor({
    content,
    onChange,
    placeholder = 'Start typing...',
    className,
    variables,
}: TiptapEditorProps) {
    const editor = useEditor({
        extensions: [
            StarterKit,
            Link.configure({ openOnClick: false }),
            Placeholder.configure({ placeholder }),
        ],
        content,
        onUpdate: ({ editor }) => {
            onChange(editor.getHTML());
        },
        editorProps: {
            attributes: {
                class: 'prose prose-sm dark:prose-invert max-w-none focus:outline-none min-h-[200px] px-3 py-2',
            },
        },
    });

    useEffect(() => {
        if (editor && content !== editor.getHTML()) {
            editor.commands.setContent(content);
        }
    }, [content]);

    if (!editor) return null;

    const insertVariable = (variable: string) => {
        editor.chain().focus().insertContent(`{{ ${variable} }}`).run();
    };

    return (
        <div className={cn('rounded-md border', className)}>
            {/* Toolbar */}
            <div className="flex flex-wrap items-center gap-0.5 border-b px-2 py-1">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleBold().run()}
                    data-active={editor.isActive('bold') || undefined}
                >
                    <Bold className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleItalic().run()}
                    data-active={editor.isActive('italic') || undefined}
                >
                    <Italic className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleHeading({ level: 2 }).run()}
                    data-active={editor.isActive('heading', { level: 2 }) || undefined}
                >
                    <Heading2 className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleHeading({ level: 3 }).run()}
                    data-active={editor.isActive('heading', { level: 3 }) || undefined}
                >
                    <Heading3 className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleBulletList().run()}
                    data-active={editor.isActive('bulletList') || undefined}
                >
                    <List className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().toggleOrderedList().run()}
                    data-active={editor.isActive('orderedList') || undefined}
                >
                    <ListOrdered className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => {
                        const url = window.prompt('Enter URL');
                        if (url) editor.chain().focus().setLink({ href: url }).run();
                    }}
                    data-active={editor.isActive('link') || undefined}
                >
                    <LinkIcon className="size-3.5" />
                </Button>
                <div className="bg-border mx-1 h-4 w-px" />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().undo().run()}
                    disabled={!editor.can().undo()}
                >
                    <Undo className="size-3.5" />
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="size-7"
                    onClick={() => editor.chain().focus().redo().run()}
                    disabled={!editor.can().redo()}
                >
                    <Redo className="size-3.5" />
                </Button>
            </div>

            {/* Variable insertion toolbar */}
            {variables && Object.keys(variables).length > 0 && (
                <div className="flex flex-wrap items-center gap-1 border-b px-2 py-1.5">
                    <span className="text-muted-foreground mr-1 text-xs">Variables:</span>
                    {Object.entries(variables).map(([group, vars]) => (
                        <div key={group} className="flex items-center gap-1">
                            {Object.entries(vars).map(([variable, label]) => (
                                <Button
                                    key={variable}
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="h-6 px-2 font-mono text-xs"
                                    onClick={() => insertVariable(variable)}
                                    title={label}
                                >
                                    {variable}
                                </Button>
                            ))}
                        </div>
                    ))}
                </div>
            )}

            {/* Editor content */}
            <EditorContent editor={editor} />
        </div>
    );
}
```

- [ ] **Step 2: Build to verify**

```bash
npm run build
```

Expected: Build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/tiptap-editor.tsx
git commit -m "feat(email-templates): add reusable Tiptap rich text editor component"
```

---

### Task 4: Create controller and form request

**Files:**
- Create: `app/Http/Controllers/Settings/EmailTemplatesController.php`
- Create: `app/Http/Requests/Settings/UpdateEmailTemplateRequest.php`

- [ ] **Step 1: Create the form request**

```bash
php artisan make:request Settings/UpdateEmailTemplateRequest --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = TenantContext::get();

        return $organization !== null
            && $this->user()?->canInOrganization('org.email-templates.manage', $organization);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}
```

- [ ] **Step 2: Create the controller**

```bash
php artisan make:controller Settings/EmailTemplatesController --no-interaction
```

Replace the generated file with:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateEmailTemplateRequest;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

final class EmailTemplatesController extends Controller
{
    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $registeredEvents = config('database-mail.events', []);

        $templates = collect($registeredEvents)->map(function (string $eventClass) use ($organization): array {
            $eventName = class_basename($eventClass);
            $description = '';
            $variables = [];

            if (method_exists($eventClass, 'getName')) {
                $description = $eventClass::getName();
            }

            // Check if org has a customized template
            $orgTemplate = MailTemplate::query()
                ->where('event', $eventClass)
                ->where('organization_id', $organization->id)
                ->first();

            $defaultTemplate = MailTemplate::query()
                ->where('event', $eventClass)
                ->whereNull('organization_id')
                ->where('is_default', true)
                ->first();

            $isCustomized = $orgTemplate !== null;
            $template = $orgTemplate ?? $defaultTemplate;

            return [
                'event_class' => $eventClass,
                'event_name' => $eventName,
                'description' => $description,
                'is_customized' => $isCustomized,
                'subject' => $template?->subject,
                'updated_at' => $isCustomized ? $orgTemplate->updated_at?->toIso8601String() : null,
            ];
        })->values()->all();

        return Inertia::render('settings/email-templates/index', [
            'templates' => $templates,
        ]);
    }

    public function edit(string $event): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $eventClass = $this->resolveEventClass($event);
        abort_unless($eventClass, 404);

        // Resolve org template or fall back to default
        $orgTemplate = MailTemplate::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        $defaultTemplate = MailTemplate::query()
            ->where('event', $eventClass)
            ->whereNull('organization_id')
            ->where('is_default', true)
            ->first();

        $template = $orgTemplate ?? $defaultTemplate;
        abort_unless($template, 404);

        // Get available variables from the event's TriggersDatabaseMail contract
        $variables = $this->getEventVariables($eventClass);

        return Inertia::render('settings/email-templates/edit', [
            'event_class' => $eventClass,
            'event_name' => class_basename($eventClass),
            'subject' => $template->subject,
            'body' => $template->body,
            'is_customized' => $orgTemplate !== null,
            'variables' => $variables,
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, string $event): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $eventClass = $this->resolveEventClass($event);
        abort_unless($eventClass, 404);

        // Find or create org-scoped template (copy-on-write)
        $orgTemplate = MailTemplate::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        if ($orgTemplate) {
            $orgTemplate->update([
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
            ]);
        } else {
            // Copy from default and customize
            $defaultTemplate = MailTemplate::query()
                ->where('event', $eventClass)
                ->whereNull('organization_id')
                ->where('is_default', true)
                ->first();

            abort_unless($defaultTemplate, 404);

            MailTemplate::query()->create([
                'organization_id' => $organization->id,
                'event' => $eventClass,
                'name' => $defaultTemplate->name,
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
                'recipients' => $defaultTemplate->recipients,
                'is_active' => true,
                'is_default' => false,
            ]);
        }

        activity()
            ->useLog('email-templates')
            ->withProperties([
                'event' => class_basename($eventClass),
                'action' => 'customized',
                'subject' => $request->validated('subject'),
            ])
            ->log('Email template customized');

        return to_route('settings.email-templates.index')->with('success', 'Email template updated.');
    }

    public function preview(string $event): \Illuminate\Http\JsonResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $eventClass = $this->resolveEventClass($event);
        abort_unless($eventClass, 404);

        $orgTemplate = MailTemplate::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        $defaultTemplate = MailTemplate::query()
            ->where('event', $eventClass)
            ->whereNull('organization_id')
            ->where('is_default', true)
            ->first();

        $template = $orgTemplate ?? $defaultTemplate;
        abort_unless($template, 404);

        // Replace variables with sample data for preview
        $variables = $this->getEventVariables($eventClass);
        $sampleData = collect($variables)->flatMap(function (array $vars): array {
            $samples = [];
            foreach ($vars as $key => $label) {
                $samples["{{ {$key} }}"] = "[{$label}]";
            }

            return $samples;
        })->all();

        $previewSubject = str_replace(array_keys($sampleData), array_values($sampleData), $template->subject);
        $previewBody = str_replace(array_keys($sampleData), array_values($sampleData), $template->body);

        return response()->json([
            'subject' => $previewSubject,
            'body' => $previewBody,
        ]);
    }

    public function reset(string $event): RedirectResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $eventClass = $this->resolveEventClass($event);
        abort_unless($eventClass, 404);

        MailTemplate::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->delete();

        activity()
            ->useLog('email-templates')
            ->withProperties([
                'event' => class_basename($eventClass),
                'action' => 'reset',
            ])
            ->log('Email template reset to default');

        return to_route('settings.email-templates.index')->with('success', 'Template reset to default.');
    }

    private function resolveEventClass(string $event): ?string
    {
        $registeredEvents = config('database-mail.events', []);

        // Find matching event class by class basename
        foreach ($registeredEvents as $eventClass) {
            if (class_basename($eventClass) === $event) {
                return $eventClass;
            }
        }

        return null;
    }

    /**
     * Extract available variables from a TriggersDatabaseMail event.
     *
     * @return array<string, array<string, string>>
     */
    private function getEventVariables(string $eventClass): array
    {
        $variables = [];

        if (method_exists($eventClass, 'getRecipients')) {
            $recipients = $eventClass::getRecipients();
            foreach ($recipients as $key => $recipient) {
                $variables[$key] = [
                    "{$key}.name" => ucfirst($key).' name',
                    "{$key}.email" => ucfirst($key).' email',
                ];
            }
        }

        // Add common variables
        $variables['app'] = [
            'app.name' => 'Application name',
            'app.url' => 'Application URL',
        ];

        return $variables;
    }
}
```

- [ ] **Step 3: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 4: Commit**

```bash
git add app/Http/Controllers/Settings/EmailTemplatesController.php app/Http/Requests/Settings/UpdateEmailTemplateRequest.php
git commit -m "feat(email-templates): add controller with fallback chain and preview"
```

---

### Task 5: Add routes and sidebar navigation

**Files:**
- Modify: `routes/settings.php`
- Modify: `resources/js/layouts/settings/layout.tsx`

- [ ] **Step 1: Add routes to routes/settings.php**

At the top of `routes/settings.php`, add the import:

```php
use App\Http\Controllers\Settings\EmailTemplatesController;
```

Inside the `Route::middleware(['auth', 'verified', 'tenant', 'permission:org.settings.manage'])` group, add:

```php
Route::get('settings/email-templates', [EmailTemplatesController::class, 'index'])->name('settings.email-templates.index');
Route::get('settings/email-templates/{event}/edit', [EmailTemplatesController::class, 'edit'])->name('settings.email-templates.edit');
Route::put('settings/email-templates/{event}', [EmailTemplatesController::class, 'update'])->name('settings.email-templates.update');
Route::post('settings/email-templates/{event}/preview', [EmailTemplatesController::class, 'preview'])->name('settings.email-templates.preview');
Route::delete('settings/email-templates/{event}', [EmailTemplatesController::class, 'reset'])->name('settings.email-templates.reset');
```

- [ ] **Step 2: Generate Wayfinder routes**

```bash
php artisan wayfinder:generate
```

- [ ] **Step 3: Add sidebar nav item**

In `resources/js/layouts/settings/layout.tsx`, add the import:

```typescript
import { index as indexEmailTemplates } from '@/routes/settings/email-templates';
```

Add the `Mail` lucide-react icon to the imports:

```typescript
import { Mail } from 'lucide-react';
```

Add the nav item after the "Webhooks" entry (or after "Audit log" if webhooks plan hasn't been executed):

```typescript
{
    title: 'Email Templates',
    href: indexEmailTemplates(),
    icon: Mail,
    dataPan: 'settings-nav-email-templates',
    requiresOrgAdmin: true,
},
```

- [ ] **Step 4: Run Pint and check TypeScript**

```bash
vendor/bin/pint --dirty --format agent
npx tsc --noEmit
```

- [ ] **Step 5: Commit**

```bash
git add routes/settings.php resources/js/layouts/settings/layout.tsx
git commit -m "feat(email-templates): add routes and sidebar navigation"
```

---

### Task 6: Create Inertia pages — Index

**Files:**
- Create: `resources/js/pages/settings/email-templates/index.tsx`

- [ ] **Step 1: Create the index page**

Create `resources/js/pages/settings/email-templates/index.tsx`:

```tsx
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import SettingsLayout from '@/layouts/settings/layout';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';

interface TemplateItem {
    event_class: string;
    event_name: string;
    description: string;
    is_customized: boolean;
    subject: string | null;
    updated_at: string | null;
}

interface Props extends SharedData {
    templates: TemplateItem[];
}

export default function EmailTemplatesIndex() {
    const { templates } = usePage<Props>().props;

    return (
        <SettingsLayout>
            <Heading
                title="Email Templates"
                description="Customize the transactional emails sent by your organization"
            />

            {templates.length === 0 ? (
                <div className="text-muted-foreground py-12 text-center text-sm">
                    No email templates registered.
                </div>
            ) : (
                <div className="space-y-2">
                    {templates.map((template) => (
                        <Link
                            key={template.event_class}
                            href={route('settings.email-templates.edit', { event: template.event_name })}
                            className="bg-muted/50 hover:bg-muted block rounded-lg border p-4 transition-colors"
                            data-pan="email-templates-edit"
                        >
                            <div className="flex items-start justify-between gap-4">
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <p className="text-sm font-medium">{template.event_name}</p>
                                        <Badge
                                            variant={template.is_customized ? 'default' : 'outline'}
                                            className={
                                                template.is_customized
                                                    ? 'bg-emerald-500/10 text-emerald-500'
                                                    : ''
                                            }
                                        >
                                            {template.is_customized ? 'Customized' : 'Default'}
                                        </Badge>
                                    </div>
                                    {template.description && (
                                        <p className="text-muted-foreground mt-1 text-sm">{template.description}</p>
                                    )}
                                    {template.subject && (
                                        <p className="text-muted-foreground mt-1 truncate text-xs">
                                            Subject: {template.subject}
                                        </p>
                                    )}
                                    {template.is_customized && template.updated_at && (
                                        <p className="text-muted-foreground mt-1 text-xs">
                                            Modified {new Date(template.updated_at).toLocaleDateString()}
                                        </p>
                                    )}
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </SettingsLayout>
    );
}
```

- [ ] **Step 2: Build frontend**

```bash
npm run build
```

Expected: Build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/settings/email-templates/index.tsx
git commit -m "feat(email-templates): add index page with template status badges"
```

---

### Task 7: Create Inertia pages — Edit with Tiptap

**Files:**
- Create: `resources/js/pages/settings/email-templates/edit.tsx`

- [ ] **Step 1: Create the edit page**

Create `resources/js/pages/settings/email-templates/edit.tsx`:

```tsx
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import TiptapEditor from '@/components/tiptap-editor';
import SettingsLayout from '@/layouts/settings/layout';
import { type SharedData } from '@/types';
import { Link, router, useForm, usePage } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';

interface Props extends SharedData {
    event_class: string;
    event_name: string;
    subject: string;
    body: string;
    is_customized: boolean;
    variables: Record<string, Record<string, string>>;
}

export default function EmailTemplatesEdit() {
    const { event_name, subject, body, is_customized, variables } = usePage<Props>().props;
    const [previewHtml, setPreviewHtml] = useState<string | null>(null);
    const [previewSubject, setPreviewSubject] = useState<string | null>(null);
    const [loadingPreview, setLoadingPreview] = useState(false);
    const debounceRef = useRef<ReturnType<typeof setTimeout>>();

    const form = useForm({
        subject,
        body,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(route('settings.email-templates.update', { event: event_name }));
    };

    const handleReset = () => {
        if (!confirm('Reset this template to the default? Your customizations will be lost.')) return;
        router.delete(route('settings.email-templates.reset', { event: event_name }));
    };

    const fetchPreview = useCallback(() => {
        setLoadingPreview(true);
        fetch(route('settings.email-templates.preview', { event: event_name }), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
            },
        })
            .then((res) => res.json())
            .then((data) => {
                setPreviewSubject(data.subject);
                setPreviewHtml(data.body);
            })
            .finally(() => setLoadingPreview(false));
    }, [event_name]);

    const handleBodyChange = (html: string) => {
        form.setData('body', html);
        // Debounce preview update
        if (debounceRef.current) clearTimeout(debounceRef.current);
        debounceRef.current = setTimeout(fetchPreview, 1000);
    };

    // Insert variable into subject
    const insertSubjectVariable = (variable: string) => {
        const input = document.getElementById('subject') as HTMLInputElement;
        if (!input) return;
        const start = input.selectionStart ?? form.data.subject.length;
        const end = input.selectionEnd ?? start;
        const value = form.data.subject;
        const newValue = value.slice(0, start) + `{{ ${variable} }}` + value.slice(end);
        form.setData('subject', newValue);
    };

    return (
        <SettingsLayout>
            <Heading
                title={`Edit: ${event_name}`}
                description="Customize the email template for this event"
            />

            <form onSubmit={submit} className="space-y-6">
                {/* Subject */}
                <div className="space-y-2">
                    <Label htmlFor="subject">Subject</Label>
                    <div className="flex gap-2">
                        <Input
                            id="subject"
                            value={form.data.subject}
                            onChange={(e) => form.setData('subject', e.target.value)}
                            className="flex-1"
                        />
                    </div>
                    <div className="flex flex-wrap gap-1">
                        {Object.entries(variables).flatMap(([, vars]) =>
                            Object.entries(vars).map(([variable, label]) => (
                                <Button
                                    key={`subject-${variable}`}
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    className="h-5 px-1.5 font-mono text-xs"
                                    onClick={() => insertSubjectVariable(variable)}
                                    title={label}
                                >
                                    {variable}
                                </Button>
                            )),
                        )}
                    </div>
                    <InputError message={form.errors.subject} />
                </div>

                {/* Split view: Editor + Preview */}
                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Editor */}
                    <div className="space-y-2">
                        <Label>Body</Label>
                        <TiptapEditor
                            content={form.data.body}
                            onChange={handleBodyChange}
                            variables={variables}
                            placeholder="Write your email template..."
                        />
                        <InputError message={form.errors.body} />
                    </div>

                    {/* Preview */}
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <Label>Preview</Label>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={fetchPreview}
                                disabled={loadingPreview}
                                data-pan="email-templates-preview"
                            >
                                {loadingPreview ? 'Loading...' : 'Preview with sample data'}
                            </Button>
                        </div>
                        <div className="bg-muted/50 min-h-[250px] rounded-md border p-4">
                            {previewSubject && (
                                <p className="mb-3 border-b pb-2 text-sm font-medium">
                                    Subject: {previewSubject}
                                </p>
                            )}
                            {previewHtml ? (
                                <div
                                    className="prose prose-sm dark:prose-invert"
                                    dangerouslySetInnerHTML={{ __html: previewHtml }}
                                />
                            ) : (
                                <p className="text-muted-foreground text-sm">
                                    Click "Preview with sample data" to see how this template looks.
                                </p>
                            )}
                        </div>
                    </div>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-3">
                    <Button type="submit" disabled={form.processing} data-pan="email-templates-save">
                        Save Template
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={route('settings.email-templates.index')}>Cancel</Link>
                    </Button>
                    {is_customized && (
                        <Button
                            type="button"
                            variant="destructive"
                            size="sm"
                            onClick={handleReset}
                            className="ml-auto"
                            data-pan="email-templates-reset"
                        >
                            Reset to Default
                        </Button>
                    )}
                </div>
            </form>
        </SettingsLayout>
    );
}
```

- [ ] **Step 2: Build frontend**

```bash
npm run build
```

Expected: Build succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/settings/email-templates/edit.tsx
git commit -m "feat(email-templates): add edit page with Tiptap editor and split preview"
```

---

### Task 8: Write feature tests

**Files:**
- Create: `tests/Feature/Settings/EmailTemplatesControllerTest.php`

- [ ] **Step 1: Create the test file**

Create `tests/Feature/Settings/EmailTemplatesControllerTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Services\TenantContext;
use MartinPetricko\LaravelDatabaseMail\Models\MailTemplate;

beforeEach(function (): void {
    $this->organization = Organization::factory()->create();
    $this->user = User::factory()->create();
    $this->organization->addMember($this->user, 'admin');
    TenantContext::set($this->organization);
    $this->actingAs($this->user);
});

test('index page renders with registered events', function (): void {
    $this->get(route('settings.email-templates.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/email-templates/index')
            ->has('templates')
        );
});

test('edit page renders with template and variables', function (): void {
    $registeredEvents = config('database-mail.events', []);
    if (empty($registeredEvents)) {
        $this->markTestSkipped('No database-mail events registered');
    }

    $eventClass = $registeredEvents[0];
    $eventName = class_basename($eventClass);

    // Ensure a default template exists
    MailTemplate::query()->firstOrCreate(
        ['event' => $eventClass, 'organization_id' => null],
        [
            'name' => $eventName,
            'subject' => 'Test Subject',
            'body' => '<p>Test body</p>',
            'recipients' => [],
            'is_active' => true,
            'is_default' => true,
        ],
    );

    $this->get(route('settings.email-templates.edit', ['event' => $eventName]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/email-templates/edit')
            ->has('subject')
            ->has('body')
            ->has('variables')
        );
});

test('update creates org-scoped copy on first edit', function (): void {
    $registeredEvents = config('database-mail.events', []);
    if (empty($registeredEvents)) {
        $this->markTestSkipped('No database-mail events registered');
    }

    $eventClass = $registeredEvents[0];
    $eventName = class_basename($eventClass);

    // Create default template
    MailTemplate::query()->firstOrCreate(
        ['event' => $eventClass, 'organization_id' => null],
        [
            'name' => $eventName,
            'subject' => 'Default Subject',
            'body' => '<p>Default body</p>',
            'recipients' => [],
            'is_active' => true,
            'is_default' => true,
        ],
    );

    $this->put(route('settings.email-templates.update', ['event' => $eventName]), [
        'subject' => 'Custom Subject',
        'body' => '<p>Custom body</p>',
    ])->assertRedirect(route('settings.email-templates.index'));

    // Org template created
    $this->assertDatabaseHas('mail_templates', [
        'event' => $eventClass,
        'organization_id' => $this->organization->id,
        'subject' => 'Custom Subject',
        'is_default' => false,
    ]);

    // Default template unchanged
    $this->assertDatabaseHas('mail_templates', [
        'event' => $eventClass,
        'organization_id' => null,
        'subject' => 'Default Subject',
        'is_default' => true,
    ]);
});

test('update modifies existing org template', function (): void {
    $registeredEvents = config('database-mail.events', []);
    if (empty($registeredEvents)) {
        $this->markTestSkipped('No database-mail events registered');
    }

    $eventClass = $registeredEvents[0];
    $eventName = class_basename($eventClass);

    // Create org template
    MailTemplate::query()->create([
        'event' => $eventClass,
        'organization_id' => $this->organization->id,
        'name' => $eventName,
        'subject' => 'Old Custom Subject',
        'body' => '<p>Old custom body</p>',
        'recipients' => [],
        'is_active' => true,
        'is_default' => false,
    ]);

    $this->put(route('settings.email-templates.update', ['event' => $eventName]), [
        'subject' => 'Updated Subject',
        'body' => '<p>Updated body</p>',
    ])->assertRedirect(route('settings.email-templates.index'));

    $this->assertDatabaseHas('mail_templates', [
        'event' => $eventClass,
        'organization_id' => $this->organization->id,
        'subject' => 'Updated Subject',
    ]);
});

test('reset deletes org template and reverts to default', function (): void {
    $registeredEvents = config('database-mail.events', []);
    if (empty($registeredEvents)) {
        $this->markTestSkipped('No database-mail events registered');
    }

    $eventClass = $registeredEvents[0];
    $eventName = class_basename($eventClass);

    // Create org template
    $orgTemplate = MailTemplate::query()->create([
        'event' => $eventClass,
        'organization_id' => $this->organization->id,
        'name' => $eventName,
        'subject' => 'Custom Subject',
        'body' => '<p>Custom body</p>',
        'recipients' => [],
        'is_active' => true,
        'is_default' => false,
    ]);

    $this->delete(route('settings.email-templates.reset', ['event' => $eventName]))
        ->assertRedirect(route('settings.email-templates.index'));

    $this->assertDatabaseMissing('mail_templates', ['id' => $orgTemplate->id]);
});

test('preview returns rendered template with sample data', function (): void {
    $registeredEvents = config('database-mail.events', []);
    if (empty($registeredEvents)) {
        $this->markTestSkipped('No database-mail events registered');
    }

    $eventClass = $registeredEvents[0];
    $eventName = class_basename($eventClass);

    MailTemplate::query()->firstOrCreate(
        ['event' => $eventClass, 'organization_id' => null],
        [
            'name' => $eventName,
            'subject' => 'Hello {{ user.name }}',
            'body' => '<p>Welcome {{ user.name }}</p>',
            'recipients' => [],
            'is_active' => true,
            'is_default' => true,
        ],
    );

    $this->postJson(route('settings.email-templates.preview', ['event' => $eventName]))
        ->assertOk()
        ->assertJsonStructure(['subject', 'body']);
});

test('unauthenticated user cannot access email templates', function (): void {
    auth()->logout();

    $this->get(route('settings.email-templates.index'))
        ->assertRedirect(route('login'));
});
```

- [ ] **Step 2: Run the tests**

```bash
php artisan test --compact --filter=EmailTemplatesControllerTest
```

Expected: All tests pass.

- [ ] **Step 3: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 4: Commit**

```bash
git add tests/Feature/Settings/EmailTemplatesControllerTest.php
git commit -m "test(email-templates): add feature tests for EmailTemplatesController"
```

---

### Task 9: Sync permissions, register Pan analytics, run full suite

- [ ] **Step 1: Sync permissions**

```bash
php artisan permission:sync
```

Expected: New `org.email-templates.view` and `org.email-templates.manage` permissions created.

- [ ] **Step 2: Register Pan analytics names**

In `app/Providers/AppServiceProvider.php`, in the `configurePan()` method, add:

```php
'email-templates-edit',
'email-templates-preview',
'email-templates-save',
'email-templates-reset',
'settings-nav-email-templates',
```

- [ ] **Step 3: Run full test suite**

```bash
php artisan test --compact
```

Expected: All tests pass.

- [ ] **Step 4: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "chore(email-templates): sync permissions and register Pan analytics"
```

---

### Task 10: Write documentation

**Files:**
- Create: `docs/user-guide/email-templates.md`

- [ ] **Step 1: Write admin user guide**

Create `docs/user-guide/email-templates.md` covering:
- What email templates are — customize the transactional emails your organization sends
- Default email events table:
  | Event | Description | Available Variables |
  |-------|-------------|-------------------|
  | UserCreated | Welcome email sent to new users | `user.name`, `user.email`, `app.name`, `app.url` |
  | OrganizationInvitationSent | Invitation email | `user.name`, `user.email`, `app.name`, `app.url` |
  | OrganizationInvitationAccepted | Acceptance notification | `user.name`, `user.email`, `app.name`, `app.url` |
  | NewTermsVersionPublished | Terms update notification | `app.name`, `app.url` |
  | TrialEndingReminder | Trial expiry warning | `app.name`, `app.url` |
  | DunningFailedPaymentReminder | Failed payment notification | `app.name`, `app.url` |
  | InvoicePaid | Payment receipt | `app.name`, `app.url` |
- How to customize a template (click event -> edit subject/body -> save)
- How variables work: type `{{ user.name }}` or click the variable button to insert
- How to preview with sample data before saving
- How to reset to default (destructive — your customizations are lost)
- Note: changes only affect your organization — other organizations keep their own templates

- [ ] **Step 2: Commit**

```bash
git add docs/user-guide/email-templates.md
git commit -m "docs(email-templates): add admin user guide"
```
