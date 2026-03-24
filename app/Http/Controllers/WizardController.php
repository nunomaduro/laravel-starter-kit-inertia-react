<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreWizardStep1Request;
use App\Http\Requests\StoreWizardStep2Request;
use App\Http\Requests\StoreWizardStep3Request;
use App\Services\WizardService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Web-based project wizard — browser version of `php artisan factory:create`.
 *
 * Multi-step flow: describe → analyze → preview → generate.
 */
final class WizardController extends Controller
{
    public function __construct(
        private readonly WizardService $wizardService,
    ) {}

    /**
     * Show the wizard page (single Inertia page with step state).
     */
    public function index(): Response
    {
        $domains = $this->wizardService->loadDomains();

        return Inertia::render('wizard/index', [
            'availableDomains' => collect($domains)->map(fn (array $d): array => [
                'slug' => $d['slug'],
                'name' => $d['name'],
                'description' => $d['description'],
                'features' => $d['suggested_features'] ?? [],
            ])->values()->all(),
        ]);
    }

    /**
     * Analyze a project description and recommend modules.
     */
    public function analyze(StoreWizardStep1Request $request): JsonResponse
    {
        $result = $this->wizardService->analyzeDescription($request->validated('description'));

        return response()->json($result);
    }

    /**
     * Preview what will be generated for selected modules.
     */
    public function preview(StoreWizardStep2Request $request): JsonResponse
    {
        $preview = $this->wizardService->preview($request->validated('modules'));

        return response()->json($preview);
    }

    /**
     * Generate the project with selected configuration.
     */
    public function generate(StoreWizardStep3Request $request): JsonResponse
    {
        $validated = $request->validated();
        $name = $validated['name'];
        $description = $validated['description'];
        $modules = $validated['modules'];

        // Configure AI context
        $this->wizardService->configureAIContext($description, $modules);

        // Update app name in .env
        $envPath = base_path('.env');

        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
            $content = preg_replace('/^APP_NAME=.*/m', 'APP_NAME="'.addslashes($name).'"', $content);
            file_put_contents($envPath, $content);
        }

        $preview = $this->wizardService->preview($modules);

        return response()->json([
            'success' => true,
            'message' => "{$name} configured successfully!",
            'preview' => $preview,
            'next_steps' => [
                'Run migrations: php artisan migrate',
                'Seed demo data: php artisan db:seed',
                'Build frontend: npm run build',
                'Start server: php artisan serve',
            ],
        ]);
    }
}
