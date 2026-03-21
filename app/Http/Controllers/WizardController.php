<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\WizardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $result = $this->wizardService->analyzeDescription($request->input('description'));

        return response()->json($result);
    }

    /**
     * Preview what will be generated for selected modules.
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string'],
        ]);

        $preview = $this->wizardService->preview($request->input('modules'));

        return response()->json($preview);
    }

    /**
     * Generate the project with selected configuration.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['string'],
        ]);

        $name = $request->input('name');
        $description = $request->input('description');
        $modules = $request->input('modules');

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
