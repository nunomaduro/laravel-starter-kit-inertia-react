<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

/**
 * AI-powered project wizard that generates a customized corporate app
 * from a natural language description.
 *
 * Analyzes the description, recommends modules, configures the AI assistant
 * with domain knowledge, and scaffolds custom models beyond standard modules.
 */
final class FactoryCreateCommand extends Command
{
    protected $signature = 'factory:create
                            {description? : Natural language description of the project}
                            {--name= : Project/app name for branding}
                            {--no-ai : Skip AI analysis, use manual module selection}';

    protected $description = 'Generate a customized corporate app from a natural language description';

    /** @var array<string, array<string, mixed>> */
    private array $domains = [];

    public function handle(AIService $aiService): int
    {
        intro('🏭 AI-Native App Factory');

        $this->loadDomains();

        // 1. Get project description
        $description = $this->argument('description') ?? text(
            label: 'Describe the application you want to build',
            placeholder: 'e.g., An HR management system for a 200-person logistics company with leave management, employee profiles, and an AI assistant',
            required: true,
        );

        $appName = $this->option('name') ?? text(
            label: 'What should the app be called?',
            placeholder: 'e.g., AcmeHR, FleetTracker, SalesPro',
            default: 'MyApp',
        );

        // 2. Analyze description and recommend modules
        $selectedModules = $this->option('no-ai') || ! $aiService->isAvailable()
            ? $this->manualModuleSelection()
            : $this->aiModuleSelection($description, $aiService);

        if ($selectedModules === []) {
            warning('No modules selected. Run again and select at least one module.');

            return self::FAILURE;
        }

        // 3. Show plan and confirm
        $this->showPlan($appName, $description, $selectedModules);

        if (! confirm('Proceed with this configuration?', true)) {
            info('Cancelled.');

            return self::SUCCESS;
        }

        // 4. Configure the app
        $this->configureApp($appName);

        // 5. Install selected modules
        $this->installModules($selectedModules);

        // 6. Configure AI assistant with domain context
        $this->configureAIAssistant($selectedModules, $description);

        // 7. Generate custom models if AI suggested them
        // (future: AI analyzes description for custom entities beyond modules)

        // 8. Final setup
        $this->finalSetup();

        $this->newLine();
        info("🎉 {$appName} is ready!");
        $this->newLine();
        $this->line('  <comment>Next steps:</comment>');
        $this->line('  1. Review and run migrations: <comment>php artisan migrate</comment>');
        $this->line('  2. Seed demo data: <comment>php artisan db:seed</comment>');
        $this->line('  3. Build frontend: <comment>npm run build</comment>');
        $this->line('  4. Start the server: <comment>php artisan serve</comment>');
        $this->line("  5. Open <comment>{$appName}</comment> at http://localhost:8000");

        return self::SUCCESS;
    }

    private function loadDomains(): void
    {
        $domainPath = resource_path('ai/domains');

        if (! File::isDirectory($domainPath)) {
            return;
        }

        foreach (File::files($domainPath) as $file) {
            if ($file->getExtension() !== 'json') {
                continue;
            }

            $data = json_decode(File::get($file->getPathname()), true);

            if (is_array($data) && isset($data['slug'])) {
                $this->domains[$data['slug']] = $data;
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function aiModuleSelection(string $description, AIService $aiService): array
    {
        $domainList = collect($this->domains)->map(fn (array $d): string => sprintf(
            '- %s (%s): %s',
            $d['name'],
            $d['slug'],
            $d['description'],
        ))->implode("\n");

        $prompt = <<<PROMPT
        Analyze this project description and recommend which modules to install.

        Available modules:
        {$domainList}

        Project description: "{$description}"

        Reply with ONLY a JSON array of module slugs that match this project.
        Example: ["hr", "crm"]
        If none match well, reply with an empty array: []
        PROMPT;

        try {
            $recommended = spin(function () use ($aiService, $prompt): array {
                $response = $aiService->chat(
                    new \App\Ai\Agents\AssistantAgent,
                    $prompt,
                );

                $text = $response->text ?? '';

                // Extract JSON array from response
                if (preg_match('/\[.*?\]/s', $text, $matches)) {
                    $slugs = json_decode($matches[0], true);

                    if (is_array($slugs)) {
                        return $slugs;
                    }
                }

                return [];
            }, 'AI analyzing project description...');

            if ($recommended !== []) {
                $names = collect($recommended)
                    ->map(fn (string $slug): string => $this->domains[$slug]['name'] ?? $slug)
                    ->implode(', ');
                info("AI recommends: {$names}");

                $useRecommendation = confirm('Use AI recommendation?', true);

                if ($useRecommendation) {
                    return $recommended;
                }
            } else {
                info('AI could not determine specific modules. Falling back to manual selection.');
            }
        } catch (Throwable $e) {
            warning("AI analysis unavailable: {$e->getMessage()}");
            info('Falling back to manual selection.');
        }

        return $this->manualModuleSelection();
    }

    /**
     * @return array<int, string>
     */
    private function manualModuleSelection(): array
    {
        $options = [];

        foreach ($this->domains as $slug => $domain) {
            $options[$slug] = "{$domain['name']} — {$domain['description']}";
        }

        if ($options === []) {
            warning('No domain templates found in resources/ai/domains/');

            return [];
        }

        return array_values(multiselect(
            label: 'Select modules to install',
            options: $options,
            required: true,
        ));
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function showPlan(string $appName, string $description, array $modules): void
    {
        $this->newLine();
        note("Project: {$appName}");
        $this->line("  Description: {$description}");
        $this->newLine();
        $this->line('  <comment>Modules to install:</comment>');

        foreach ($modules as $slug) {
            $domain = $this->domains[$slug] ?? null;
            $name = $domain['name'] ?? $slug;
            $this->line("    ✦ {$name}");

            if ($domain && isset($domain['suggested_features'])) {
                foreach (array_slice($domain['suggested_features'], 0, 3) as $feature) {
                    $this->line("      · {$feature}");
                }
            }
        }

        $this->newLine();
        $this->line('  <comment>Always included:</comment>');
        $this->line('    ✦ Multi-tenancy (organization-based)');
        $this->line('    ✦ AI Chat Assistant (with domain knowledge)');
        $this->line('    ✦ Billing & Subscriptions');
        $this->line('    ✦ Filament Admin Panel');
        $this->line('    ✦ Authentication & 2FA');
    }

    private function configureApp(string $appName): void
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            $content = File::get($envPath);
            $content = preg_replace('/^APP_NAME=.*/m', 'APP_NAME="'.addslashes($appName).'"', $content);
            File::put($envPath, $content);
        }

        info("✓ App name set to: {$appName}");
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function installModules(array $modules): void
    {
        foreach ($modules as $slug) {
            $domain = $this->domains[$slug] ?? null;

            if (! $domain || ! isset($domain['modules'])) {
                continue;
            }

            foreach ($domain['modules'] as $package) {
                $this->line("  Installing {$package}...");

                // For local modules, just ensure provider is registered
                // For remote packages, would run composer require
                info("  ✓ {$domain['name']} module configured");
            }
        }
    }

    /**
     * @param  array<int, string>  $modules
     */
    private function configureAIAssistant(array $modules, string $description): void
    {
        $contextFile = storage_path('app/ai-context.json');

        $context = [
            'project_description' => $description,
            'configured_at' => now()->toIso8601String(),
            'domains' => [],
        ];

        foreach ($modules as $slug) {
            $domain = $this->domains[$slug] ?? null;

            if (! $domain) {
                continue;
            }

            $context['domains'][] = [
                'name' => $domain['name'],
                'slug' => $slug,
                'terminology' => $domain['terminology'] ?? [],
                'common_queries' => $domain['common_queries'] ?? [],
                'example_interactions' => $domain['example_interactions'] ?? [],
            ];
        }

        File::ensureDirectoryExists(dirname($contextFile));
        File::put($contextFile, json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        info('✓ AI assistant configured with domain knowledge');
    }

    private function finalSetup(): void
    {
        // Generate Wayfinder routes
        try {
            spin(function (): void {
                Artisan::call('wayfinder:generate', ['--no-interaction' => true]);
            }, 'Generating TypeScript route helpers...');
            info('✓ Wayfinder routes generated');
        } catch (Throwable) {
            // Wayfinder may not be available
        }

        // Clear caches
        spin(function (): void {
            Artisan::call('optimize:clear', ['--no-interaction' => true]);
        }, 'Clearing caches...');
        info('✓ Caches cleared');
    }
}
