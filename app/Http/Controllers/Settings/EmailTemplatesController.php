<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateEmailTemplateRequest;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class EmailTemplatesController extends Controller
{
    public function index(): Response
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $registeredEvents = config('database-mail.events', []);

        // For each registered event, check if there's an org-customized template
        $templates = collect($registeredEvents)->map(function (string $eventClass) use ($organization): array {
            $eventName = class_basename($eventClass);
            $description = method_exists($eventClass, 'getName') ? $eventClass::getName() : '';

            // Use the actual MailTemplate model from the package
            $mailTemplateClass = config('database-mail.mail_template_model', \MartinPetricko\LaravelDatabaseMail\Models\MailTemplate::class);

            $orgTemplate = $mailTemplateClass::query()
                ->where('event', $eventClass)
                ->where('organization_id', $organization->id)
                ->first();

            $defaultTemplate = $mailTemplateClass::query()
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

        $mailTemplateClass = config('database-mail.mail_template_model', \MartinPetricko\LaravelDatabaseMail\Models\MailTemplate::class);

        $orgTemplate = $mailTemplateClass::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        $defaultTemplate = $mailTemplateClass::query()
            ->where('event', $eventClass)
            ->whereNull('organization_id')
            ->where('is_default', true)
            ->first();

        $template = $orgTemplate ?? $defaultTemplate;
        abort_unless($template, 404);

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

        $mailTemplateClass = config('database-mail.mail_template_model', \MartinPetricko\LaravelDatabaseMail\Models\MailTemplate::class);

        // Find existing org template or create copy-on-write from default
        $orgTemplate = $mailTemplateClass::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        if ($orgTemplate) {
            $orgTemplate->update([
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
            ]);
        } else {
            $defaultTemplate = $mailTemplateClass::query()
                ->where('event', $eventClass)
                ->whereNull('organization_id')
                ->where('is_default', true)
                ->first();

            abort_unless($defaultTemplate, 404);

            $mailTemplateClass::query()->create([
                'organization_id' => $organization->id,
                'event' => $eventClass,
                'name' => $defaultTemplate->name,
                'subject' => $request->validated('subject'),
                'body' => $request->validated('body'),
                'recipients' => $defaultTemplate->recipients,
                'attachments' => $defaultTemplate->attachments ?? [],
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

    public function preview(string $event): JsonResponse
    {
        $organization = TenantContext::get();
        abort_unless($organization, 404);

        $eventClass = $this->resolveEventClass($event);
        abort_unless($eventClass, 404);

        $mailTemplateClass = config('database-mail.mail_template_model', \MartinPetricko\LaravelDatabaseMail\Models\MailTemplate::class);

        $orgTemplate = $mailTemplateClass::query()
            ->where('event', $eventClass)
            ->where('organization_id', $organization->id)
            ->first();

        $defaultTemplate = $mailTemplateClass::query()
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

        $mailTemplateClass = config('database-mail.mail_template_model', \MartinPetricko\LaravelDatabaseMail\Models\MailTemplate::class);

        $mailTemplateClass::query()
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

        foreach ($registeredEvents as $eventClass) {
            if (class_basename($eventClass) === $event) {
                return $eventClass;
            }
        }

        return null;
    }

    /**
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

        $variables['app'] = [
            'app.name' => 'Application name',
            'app.url' => 'Application URL',
        ];

        return $variables;
    }
}
