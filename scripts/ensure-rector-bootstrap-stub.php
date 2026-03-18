<?php

declare(strict_types=1);

/**
 * Ensures vendor/rector/rector/bootstrap/app.php exists so Pest arch tests do not fail
 * when Rector's bootstrap resolves Laravel's bootstrap path relative to rector's dir.
 * Run after composer install/update (e.g. post-install-cmd).
 */
$rectorBase = __DIR__.'/../vendor/rector/rector';
$stubDir = $rectorBase.'/bootstrap';
$stubFile = $stubDir.'/app.php';

if (! is_dir($rectorBase)) {
    return;
}

if (! is_dir($stubDir)) {
    mkdir($stubDir, 0755, true);
}

$stub = <<<'PHP'
<?php

declare(strict_types=1);

// This file is inside vendor/rector/rector/bootstrap/ but is required when path resolution
// wrongly uses rector's dir. Load the project's real bootstrap when run from project root.
$projectBootstrap = getcwd() . '/bootstrap/app.php';
if (file_exists($projectBootstrap)) {
    return require $projectBootstrap;
}

// Fallback minimal stub if getcwd() is not project root
$stub = new class {
    public function make(string $abstract, array $parameters = []): mixed
    {
        return $this;
    }

    public function bootstrap(): void
    {
    }
};

return $stub;
PHP;

if (file_exists($stubFile) && file_get_contents($stubFile) === $stub) {
    return;
}

file_put_contents($stubFile, $stub);
