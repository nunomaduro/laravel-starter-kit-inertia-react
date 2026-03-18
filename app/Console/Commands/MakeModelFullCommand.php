<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\SeederCategory;
use App\Services\AISeederCodeGenerator;
use App\Services\AISeedGenerator;
use App\Services\EnhancedRelationshipAnalyzer;
use App\Services\PrismService;
use App\Services\RelationshipAnalyzer;
use App\Services\SeedSpecGenerator;
use App\Services\TraditionalSeedGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeModelFullCommand extends Command
{
    protected $signature = 'make:model:full
                            {name : The name of the model}
                            {--category=development : Seeder category (essential, development, production)}
                            {--migration : Create a new migration file for the model}
                            {--factory : Create a new factory for the model}
                            {--seed : Create a new seeder for the model}
                            {--controller : Create a new controller for the model}
                            {--resource : Indicates if the generated controller should be a resource controller}
                            {--api : Indicates if the generated controller should be an API resource controller}
                            {--requests : Create Form Request classes for the model}
                            {--policy : Create a new policy for the model}
                            {--all : Generate a migration, factory, seeder, and resource controller}
                            {--no-ai : Skip AI generation even if available}';

    protected $description = 'Create a new Eloquent model with factory, seeder, and JSON data file';

    public function handle(): int
    {
        $name = $this->argument('name');
        $category = $this->getCategory();
        $all = $this->option('all');

        $this->info('Creating full model setup for: '.$name);

        // Create model with migration
        $this->createModel($name, $all);

        // Create factory
        if ($all || $this->option('factory')) {
            $this->createFactory($name);
        }

        // Create seeder
        if ($all || $this->option('seed')) {
            $this->createSeeder($name, $category);
        }

        // Generate seed spec first (needed for JSON generation)
        $this->generateSeedSpec($name);

        // Create JSON data file
        $this->createJsonDataFile($name);

        // Auto-generate JSON if missing/empty (smart generation)
        $this->autoGenerateJsonIfNeeded($name);

        // Update manifest
        $this->updateManifest($name, $category);

        // Create controller if requested
        if ($all || $this->option('controller')) {
            $this->createController($name, $all);
        }

        // Create policy if requested
        if ($all || $this->option('policy')) {
            $this->createPolicy($name);
        }

        // Create requests if requested
        if ($all || $this->option('requests')) {
            $this->createRequests($name);
        }

        $this->info('Model setup complete for: '.$name);

        return self::SUCCESS;
    }

    private function createModel(string $name, bool $all): void
    {
        $options = ['--no-interaction' => true];

        if ($all || $this->option('migration')) {
            $options['--migration'] = true;
        }

        Artisan::call('make:model', array_merge(['name' => $name], $options));
        $this->injectActivityLogToModel($name);
        $this->info('✓ Model created');
    }

    private function injectActivityLogToModel(string $name): void
    {
        $path = app_path('Models/'.$name.'.php');

        if (! File::exists($path)) {
            return;
        }

        $content = File::get($path);

        if (Str::contains($content, 'LogsActivity') || Str::contains($content, 'activitylog')) {
            return;
        }

        $content = $this->injectActivityLogUseStatements($content);
        $content = $this->injectActivityLogTrait($content);
        $content = $this->injectActivityLogMethod($content);

        File::put($path, $content);
    }

    private function injectActivityLogUseStatements(string $content): string
    {
        $useLines = "use Spatie\\Activitylog\\LogOptions;\nuse Spatie\\Activitylog\\Traits\\LogsActivity;\n";

        return preg_replace(
            '/(use Illuminate\\\\Database\\\\Eloquent\\\\Model;)\n/',
            "$1\n\n".$useLines,
            $content,
            1
        );
    }

    private function injectActivityLogTrait(string $content): string
    {
        return preg_replace(
            '/\n\{\n(\s+)/',
            "\n{\n    use LogsActivity;\n\n$1",
            $content,
            1
        );
    }

    private function injectActivityLogMethod(string $content): string
    {
        $sensitive = config('activitylog.sensitive_attributes', [
            'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'embedding', 'api_token',
        ]);
        $exceptList = implode(', ', array_map(
            fn (string $attr): string => "'".$attr."'",
            $sensitive
        ));
        $method = <<<PHP

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll()
            ->logExcept([{$exceptList}]);
    }
}
PHP;

        return preg_replace(
            '/\n}(\s*)$/',
            $method.'$1',
            $content,
            1
        );
    }

    private function createFactory(string $name): void
    {
        Artisan::call('make:factory', [
            'name' => $name.'Factory',
            '--model' => $name,
            '--no-interaction' => true,
        ]);
        $this->info('✓ Factory created');
    }

    private function createSeeder(string $name, SeederCategory $category): void
    {
        $seederName = $name.'Seeder';
        $categoryPath = database_path('seeders/'.$category->value);

        if (! File::isDirectory($categoryPath)) {
            File::makeDirectory($categoryPath, 0755, true);
        }

        $seederPath = sprintf('%s/%s.php', $categoryPath, $seederName);

        if (File::exists($seederPath)) {
            $this->warn('Seeder already exists: '.$seederPath);
        } else {
            $this->updateSeederFile($name, $seederName, $category);
        }

        $this->info(sprintf('✓ Seeder created in %s category', $category->value));
    }

    private function updateSeederFile(string $modelName, string $seederName, SeederCategory $category): void
    {
        $seederPath = database_path(sprintf('seeders/%s/%s.php', $category->value, $seederName));
        $modelClass = 'App\Models\\'.$modelName;
        $namespace = 'Database\Seeders\\'.$category->value;

        $enhancedAnalyzer = resolve(EnhancedRelationshipAnalyzer::class);

        // Try enhanced analyzer first (uses model reflection)
        $relationships = class_exists($modelClass)
            ? $enhancedAnalyzer->analyzeModel($modelClass)
            : [];

        // Fallback to migration-based analysis if model doesn't exist yet
        if (empty($relationships)) {
            $analyzer = new RelationshipAnalyzer();
            $migrationPath = $analyzer->getLatestMigrationForModel($modelName);
            $migrationRelationships = $migrationPath ? $analyzer->analyzeMigration($migrationPath) : [];

            // Convert migration relationships format to enhanced format
            foreach ($migrationRelationships as $key => $rel) {
                $relationships[$key] = [
                    'type' => $rel['type'],
                    'model' => $rel['model'] ?? null,
                    'foreignKey' => null,
                    'localKey' => null,
                    'pivotTable' => null,
                ];
            }
        }

        // Get seed spec for AI generation
        $specGenerator = resolve(SeedSpecGenerator::class);
        $spec = class_exists($modelClass)
            ? $specGenerator->loadSpec($modelClass) ?? $specGenerator->generateSpec($modelClass)
            : ['fields' => [], 'relationships' => [], 'value_hints' => []];

        // Generate seeder code using AI or traditional method
        $aiGenerator = resolve(AISeederCodeGenerator::class);
        $seederMethods = $aiGenerator->generateSeederCode($modelName, $spec, $relationships, $category->value);

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use {$modelClass};
use Database\Seeders\Concerns\LoadsJsonData;
use Illuminate\Database\Seeder;

final class {$seederName} extends Seeder
{
    use LoadsJsonData;

    /**
     * Run the database seeds (idempotent).
     */
    public function run(): void
    {
        \$this->seedRelationships();
        \$this->seedFromJson();
        \$this->seedFromFactory();
    }
{$seederMethods}
}

PHP;

        File::put($seederPath, $content);
    }

    private function createJsonDataFile(string $modelName): void
    {
        $jsonKey = $this->getJsonKey($modelName);
        $jsonPath = database_path('seeders/data/'.Str::snake(Str::plural($modelName)).'.json');

        $content = [
            $jsonKey => [
                [
                    'id' => 1,
                    // Add example fields here based on model
                ],
            ],
        ];

        File::put($jsonPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('✓ JSON data file created');
    }

    private function updateManifest(string $modelName, SeederCategory $category): void
    {
        $manifestPath = database_path('seeders/manifest.json');
        $jsonFileName = Str::snake(Str::plural($modelName)).'.json';

        if (! File::exists($manifestPath)) {
            $manifest = [
                'seeders' => [],
                'categories' => [],
            ];
        } else {
            $manifest = json_decode(File::get($manifestPath), true);
        }

        $seederEntry = [
            'name' => $modelName.'Seeder',
            'category' => $category->value,
            'description' => sprintf('Seeds %s data', $modelName),
            'dependencies' => [],
            'data_files' => [$jsonFileName],
        ];

        $manifest['seeders'][] = $seederEntry;

        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('✓ Manifest updated');
    }

    private function createController(string $name, bool $all): void
    {
        $options = ['--no-interaction' => true];

        if ($all || $this->option('resource')) {
            $options['--resource'] = true;
        }

        if ($all || $this->option('api')) {
            $options['--api'] = true;
        }

        Artisan::call('make:controller', array_merge(['name' => $name.'Controller'], $options));
        $this->info('✓ Controller created');
    }

    private function createPolicy(string $name): void
    {
        Artisan::call('make:policy', [
            'name' => $name.'Policy',
            '--model' => $name,
            '--no-interaction' => true,
        ]);
        $this->info('✓ Policy created');
    }

    private function createRequests(string $name): void
    {
        Artisan::call('make:request', [
            'name' => sprintf('Store%sRequest', $name),
            '--no-interaction' => true,
        ]);

        Artisan::call('make:request', [
            'name' => sprintf('Update%sRequest', $name),
            '--no-interaction' => true,
        ]);

        $this->info('✓ Form requests created');
    }

    private function getCategory(): SeederCategory
    {
        $category = $this->option('category') ?? 'development';

        return match (mb_strtolower($category)) {
            'essential' => SeederCategory::Essential,
            'production' => SeederCategory::Production,
            default => SeederCategory::Development,
        };
    }

    private function getJsonKey(string $modelName): string
    {
        return Str::snake(Str::plural($modelName));
    }

    private function generateSeedSpec(string $name): void
    {
        $modelClass = 'App\Models\\'.$name;

        if (! class_exists($modelClass)) {
            return;
        }

        try {
            $generator = resolve(SeedSpecGenerator::class);
            $spec = $generator->generateSpec($modelClass);
            $generator->saveSpec($modelClass, $spec);
            $this->info('✓ Seed spec created');
        } catch (Exception $exception) {
            $this->warn('Could not generate seed spec: '.$exception->getMessage());
        }
    }

    private function autoGenerateJsonIfNeeded(string $name): void
    {
        if ($this->option('no-ai')) {
            return;
        }

        $jsonKey = $this->getJsonKey($name);
        $jsonPath = database_path(sprintf('seeders/data/%s.json', $jsonKey));

        // Check if JSON file exists and has data
        $hasData = false;
        if (File::exists($jsonPath)) {
            $content = File::get($jsonPath);
            $data = json_decode($content, true) ?? [];
            $hasData = ! empty($data[$jsonKey] ?? []);
        }

        // Skip if JSON already has data
        if ($hasData) {
            return;
        }

        $modelClass = 'App\Models\\'.$name;

        if (! class_exists($modelClass)) {
            return;
        }

        try {
            $specGenerator = resolve(SeedSpecGenerator::class);
            $spec = $specGenerator->loadSpec($modelClass);

            if ($spec === null) {
                return;
            }

            $prismService = resolve(PrismService::class);
            $aiAvailable = $prismService->isAvailable();

            if ($aiAvailable && config('seeding.auto_generate_json', true)) {
                $this->line('  Auto-generating JSON with AI...');
                $this->generateJsonWithAI($name, $spec, $prismService);
            } elseif (config('seeding.auto_generate_json', true)) {
                $this->line('  Auto-generating JSON with Faker...');
                $this->generateJsonWithFaker($name, $spec);
            }
        } catch (Exception) {
            // Silently fail - JSON generation is optional
        }
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function generateJsonWithAI(string $name, array $spec, PrismService $prismService): void
    {
        try {
            $aiGenerator = resolve(AISeedGenerator::class);
            $profile = $aiGenerator->loadProfile('App\Models\\'.$name);

            if ($profile === null) {
                $profile = $aiGenerator->generateProfile('App\Models\\'.$name, $spec);
                $aiGenerator->saveProfile('App\Models\\'.$name, $profile);
            }

            $prompt = $aiGenerator->buildPrompt($spec, $profile, 'basic_demo');
            $model = $prismService->defaultModel();
            $records = $this->generateSeedJsonViaPrism($prompt, $model, $prismService);

            if ($records !== null && $records !== []) {
                $jsonKey = $this->getJsonKey($name);
                $data = [
                    $jsonKey => $records,
                    '_source' => 'ai',
                    '_auto_generated' => true,
                    '_generated_at' => now()->toIso8601String(),
                ];

                File::put(database_path(sprintf('seeders/data/%s.json', $jsonKey)), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->info('  ✓ JSON auto-generated with AI');
            } else {
                $this->generateJsonWithFaker($name, $spec);
            }
        } catch (Exception) {
            $this->generateJsonWithFaker($name, $spec);
        }
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function generateSeedJsonViaPrism(string $prompt, string $model, PrismService $prismService): ?array
    {
        $schema = [
            'type' => 'array',
            'items' => [
                'type' => 'object',
                'additionalProperties' => true,
            ],
        ];

        try {
            $jsonData = $prismService->generateStructured($prompt, $schema, $model);

            throw_unless(is_array($jsonData), Exception::class, 'Structured output did not return array');

            return $this->normalizeJsonRecords($jsonData);
        } catch (Exception) {
            $response = $prismService->generate($prompt, $model);
            $text = $response->text;
            $jsonData = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                if (preg_match('/```(?:json)?\s*(\[.*?\])/s', $text, $matches)) {
                    $jsonData = json_decode($matches[1], true);
                } elseif (preg_match('/\[.*\]/s', $text, $matches)) {
                    $jsonData = json_decode($matches[0], true);
                }
            }

            if ($jsonData === null || json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $this->normalizeJsonRecords($jsonData);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $jsonData
     * @return array<int, array<string, mixed>>
     */
    private function normalizeJsonRecords(array $jsonData): array
    {
        if (isset($jsonData['data']) && is_array($jsonData['data'])) {
            return $jsonData['data'];
        }

        if (isset($jsonData[0]) && is_array($jsonData[0])) {
            return $jsonData;
        }

        if (! isset($jsonData[0])) {
            return [$jsonData];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function generateJsonWithFaker(string $name, array $spec): void
    {
        try {
            $traditionalGenerator = resolve(TraditionalSeedGenerator::class);
            $jsonData = $traditionalGenerator->generate($spec, 3);

            $jsonKey = $this->getJsonKey($name);
            $data = [
                $jsonKey => $jsonData,
                '_source' => 'traditional',
                '_auto_generated' => true,
                '_generated_at' => now()->toIso8601String(),
            ];

            File::put(database_path(sprintf('seeders/data/%s.json', $jsonKey)), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('  ✓ JSON auto-generated with Faker');
        } catch (Exception) {
            // Silently fail
        }
    }
}
